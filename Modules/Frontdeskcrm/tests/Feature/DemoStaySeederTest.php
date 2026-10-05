<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use Database\Seeders\DemoStaySeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Finance\Models\JournalEntry;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Website\Models\Booking;
use Modules\Website\Models\RoomUnit;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class DemoStaySeederTest extends TestCase
{
    use DatabaseTransactions;

    protected function runDemoSeeder(): void
    {
        $this->seed(DemoStaySeeder::class);
    }

    #[Test]
    public function it_seeds_a_first_time_guest_from_booking_through_to_checkout(): void
    {
        $this->runDemoSeeder();

        $guest = Guest::where('email', 'demo.first.booking@example.test')->firstOrFail();

        // The website booking the guest actually made.
        $booking = Booking::where('booking_reference', 'DEMO-BK-FIRST-0001')->firstOrFail();
        $this->assertSame('paid', $booking->payment_status);
        $this->assertSame('completed', $booking->status, 'Check-out must sync the booking back to completed.');
        $this->assertSame($guest->id, $booking->guest_profile_id);

        // The PMS stay it became.
        $registration = Registration::where('reservation_code', 'DEMO-REG-FIRST-0001')->firstOrFail();
        $this->assertSame('checked_out', $registration->stay_status);
        $this->assertSame($booking->id, $registration->booking_id);
        $this->assertSame($guest->id, $registration->guest_id);
        $this->assertSame(2, (int) $registration->no_of_nights);
        $this->assertNotNull($registration->checked_in_at);
        $this->assertNotNull($registration->actual_checkout_at);

        // Charges were posted to the room and mirrored to the charge report.
        $folio = $registration->folios()->firstOrFail();
        $this->assertSame('closed', $folio->status);
        $this->assertCount(4, $folio->items);
        $this->assertCount(4, $registration->charges);
        $this->assertSame(126500.00, (float) $folio->balance, '2 nights at 45,000 plus laundry, minibar and breakfast.');

        // Checkout settled the folio in full.
        $this->assertSame(126500.00, (float) $registration->payments()->sum('amount'));

        // The unit went back on sale and the guest now has a visit on record.
        $this->assertSame('available', $registration->roomUnit->fresh()->status);
        $this->assertSame(1, (int) $guest->fresh()->visit_count);
        $this->assertNotNull($guest->fresh()->last_visit_at);
    }

    #[Test]
    public function it_posts_the_ledger_for_the_booking_settlement_and_the_incidental_receipt(): void
    {
        $this->runDemoSeeder();

        $booking = Booking::where('booking_reference', 'DEMO-BK-FIRST-0001')->firstOrFail();
        $registration = Registration::where('reservation_code', 'DEMO-REG-FIRST-0001')->firstOrFail();

        // Prepaid room: revenue recognised when the website took the money.
        $this->assertTrue(JournalEntry::where('entry_number', "SALE-booking-{$booking->id}")->exists());

        // Extras settled at the desk, booked to Other Income not Room Revenue.
        $incidental = JournalEntry::where('entry_number', "SALE-registration-{$registration->id}")->firstOrFail();
        $lines = $incidental->lines()->with('account')->get();

        $this->assertSame(36500.0, (float) $lines->sum('debit'));
        $this->assertSame(36500.0, (float) $lines->sum('credit'));

        $codes = $lines->pluck('account.code');

        $this->assertContains('4900', $codes, 'Incidentals belong in Other Income (4900).');
        $this->assertContains('1000', $codes, 'Cash was taken at the desk.');
        $this->assertNotContains('4000', $codes->all(), 'Incidentals are not room revenue.');

        $this->assertSame(
            36500.0,
            (float) $lines->firstWhere('account.code', '4900')?->credit
        );
    }

    #[Test]
    public function it_seeds_a_returning_guest_with_history_and_a_live_stay(): void
    {
        $this->runDemoSeeder();

        $guest = Guest::where('email', 'demo.returning.guest@example.test')->firstOrFail();
        $this->assertSame(1, (int) $guest->visit_count, 'One completed stay so far; the live one has not been checked out.');

        // The earlier stay, closed out.
        $past = Registration::where('reservation_code', 'DEMO-REG-RET-PST001')->firstOrFail();
        $this->assertSame('checked_out', $past->stay_status);
        $this->assertSame('DEMO-BK-RET-PST0001', $past->booking->booking_reference);
        $this->assertSame('completed', $past->booking->status);
        $this->assertSame('available', $past->roomUnit->fresh()->status);
        $this->assertSame('closed', $past->folios()->firstOrFail()->status);

        // The stay in house right now, deliberately left open.
        $current = Registration::where('reservation_code', 'DEMO-REG-RET-NOW001')->firstOrFail();
        $this->assertSame('checked_in', $current->stay_status);
        $this->assertSame(3, (int) $current->no_of_nights);

        $folio = $current->folios()->firstOrFail();
        $this->assertSame('open', $folio->status, 'The live folio stays open so there is a balance to settle.');
        $this->assertCount(3, $folio->items);
        $this->assertSame(153000.00, (float) $folio->balance);
        $this->assertSame(0, (int) $current->payments()->count());

        $this->assertSame('occupied', $current->roomUnit->fresh()->status);
    }

    #[Test]
    public function it_is_idempotent(): void
    {
        $this->runDemoSeeder();
        $this->runDemoSeeder();

        $this->assertSame(3, Booking::whereIn('booking_reference', [
            'DEMO-BK-FIRST-0001',
            'DEMO-BK-RET-NOW0001',
            'DEMO-BK-RET-PST0001',
        ])->count());

        $this->assertSame(3, Registration::whereIn('reservation_code', [
            'DEMO-REG-FIRST-0001',
            'DEMO-REG-RET-PST001',
            'DEMO-REG-RET-NOW001',
        ])->count());

        // One folio per stay, charges posted once each.
        $first = Registration::where('reservation_code', 'DEMO-REG-FIRST-0001')->firstOrFail();
        $this->assertSame(1, $first->folios()->count());
        $this->assertSame(4, $first->folios()->firstOrFail()->items()->count());
        $this->assertSame(4, (int) $first->charges()->count());
        $this->assertSame(1, (int) $first->payments()->count());

        // The visit count must not drift upward on every run.
        $guest = Guest::where('email', 'demo.first.booking@example.test')->firstOrFail();
        $this->assertSame(1, (int) $guest->visit_count);

        // Inventory is not double-claimed.
        $this->assertSame(1, RoomUnit::where('room_number', 'DEMO-101')->count());
    }
}
