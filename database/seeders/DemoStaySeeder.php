<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Finance\Database\Seeders\ChartOfAccountsSeeder;
use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Services\PostingService;
use Modules\Frontdeskcrm\Models\Folio;
use Modules\Frontdeskcrm\Models\FolioItem;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationCharge;
use Modules\Frontdeskcrm\Models\RegistrationPayment;
use Modules\Frontdeskcrm\Services\FolioService;
use Modules\Website\Models\Booking;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Modules\Website\Services\BookingLedgerService;
use RuntimeException;

/**
 * Seeds the whole guest journey across three modules so a fresh environment can
 * be demonstrated and clicked through without hand-building records:
 *
 *   Website  Booking (paid online)
 *      -> PMS  Registration checked in, room assigned, unit marked occupied
 *      -> PMS  Folio charges posted (FolioService, mirrored to registration_charges)
 *      -> Finance  GL entries for the settlement and the incidental receipt
 *      -> PMS  Checked out, unit released, guest visit history updated,
 *              booking synced to `completed`
 *
 * Two scenarios are produced:
 *
 *   1. FIRST TIME GUEST - a completed stay that ran four to two days ago, so the
 *      whole trail is visible: booking, registration, charges, folio, payments
 *      and the resulting journal entries.
 *   2. RETURNING GUEST - one completed stay from six weeks ago plus a stay that
 *      is in house right now with an open folio and money still outstanding, so
 *      the front desk has a live record to act on (check out, take a payment).
 *
 * Every row is keyed on a fixed identifier, so re-running updates the same
 * records instead of duplicating them. The whole run is one transaction: a
 * failure (a locked fiscal period, a missing account) rolls the lot back rather
 * than leaving half a guest journey behind.
 *
 * Not wired into DatabaseSeeder. Run it explicitly:
 *     php artisan db:seed --class=DemoStaySeeder
 */
class DemoStaySeeder extends Seeder
{
    private const NIGHTLY_RATE = 45000.00;

    private const FIRST_BOOKING_REFERENCE = 'DEMO-BK-FIRST-0001';

    private const FIRST_RESERVATION_CODE = 'DEMO-REG-FIRST-0001';

    private const RETURNING_BOOKING_REFERENCE = 'DEMO-BK-RET-NOW0001';

    private const RETURNING_RESERVATION_CODE = 'DEMO-REG-RET-NOW001';

    private const RETURNING_PAST_REFERENCE = 'DEMO-BK-RET-PST0001';

    private const RETURNING_PAST_CODE = 'DEMO-REG-RET-PST001';

    private FolioService $folioService;

    private PostingService $postings;

    private BookingLedgerService $bookingLedger;

    private ?User $agent = null;

    public function run(): void
    {
        $this->folioService = app(FolioService::class);
        $this->postings = app(PostingService::class);
        $this->bookingLedger = app(BookingLedgerService::class);
        $this->agent = User::orderBy('id')->first();

        // Ledger postings resolve their account codes with firstOrFail(), so the
        // chart has to exist before anything below can post.
        $this->call(ChartOfAccountsSeeder::class);

        DB::transaction(function (): void {
            $roomType = $this->demoRoomType();
            $rooms = $this->demoUnits($roomType);

            $this->seedFirstTimeStay($roomType, $rooms[0]);
            $this->seedReturningGuest($roomType, $rooms[1], $rooms[2]);
        });

        $this->command?->newLine();
        $this->command?->info('Demo stay seeded.');
        $this->command?->line('  Booking references: '.self::FIRST_BOOKING_REFERENCE.', '.self::RETURNING_PAST_REFERENCE.', '.self::RETURNING_BOOKING_REFERENCE);
        $this->command?->line('  Reservations:      '.self::FIRST_RESERVATION_CODE.' (checked out), '.self::RETURNING_PAST_CODE.' (checked out), '.self::RETURNING_RESERVATION_CODE.' (in house)');
    }

    /**
     * Scenario 1: a first-time guest whose stay is already checked out.
     */
    private function seedFirstTimeStay(RoomType $roomType, RoomUnit $unit): void
    {
        $checkIn = Carbon::today()->subDays(4);
        $checkOut = Carbon::today()->subDays(2);
        $nights = $checkIn->diffInDays($checkOut);
        $roomTotal = self::NIGHTLY_RATE * $nights;

        $guest = Guest::firstOrCreate(
            ['email' => 'demo.first.booking@example.test'],
            [
                'full_name' => 'Ada Okafor',
                'contact_number' => '+2348000000001',
                'nationality' => 'Nigeria',
                'gender' => 'Female',
                'email' => 'demo.first.booking@example.test',
                'visit_count' => 0,
            ]
        );

        $booking = $this->syncBooking(
            self::FIRST_BOOKING_REFERENCE,
            $guest,
            $roomType,
            $checkIn,
            $checkOut,
            $roomTotal,
            'paid',
            'confirmed'
        );

        // The website took the money at booking time, so revenue is recognised
        // here rather than again at check-out.
        $this->bookingLedger->recordSettlement($booking, 'paystack');
        $this->requireLedgerEntry("SALE-booking-{$booking->id}", "booking {$booking->booking_reference} settlement");

        $registration = $this->syncRegistration(
            self::FIRST_RESERVATION_CODE,
            $booking,
            $guest,
            $roomType,
            $unit,
            $checkIn,
            $checkOut,
            'checked_in',
            [
                'checked_in_at' => $checkIn->copy()->setTime(14, 5),
                'total_amount' => $roomTotal,
            ]
        );

        $unit->update(['status' => 'occupied', 'housekeeping_status' => 'dirty']);

        $folio = $this->folioFor($registration);

        $this->postChargeOnce($folio, 'Room charge - '.$nights.' night(s)', 'room', $roomTotal, $checkIn);
        $this->postChargeOnce($folio, 'Express laundry', 'laundry', 15000.00, $checkIn->copy()->addDay());
        $this->postChargeOnce($folio, 'Minibar consumption', 'service', 8500.00, $checkIn->copy()->addDay());
        $this->postChargeOnce($folio, 'Breakfast - 2 guests', 'breakfast', 13000.00, $checkIn->copy()->addDay());

        $folio->refresh();
        $folioTotal = (float) $folio->balance;

        // Only the extras were settled at the desk; the room was prepaid online.
        $incidentalTotal = $folioTotal - $roomTotal;

        $this->payOnce(
            $registration,
            $folioTotal,
            'cash',
            $checkOut->toDateString(),
            'DEMO-RCP-FIRST-01'
        );

        $this->folioService->closeFolio($folio);

        $this->postings->recordSale(
            'frontdesk',
            $incidentalTotal,
            'cash',
            'registration',
            $registration->id,
            $checkOut->toDateString(),
            "Incidentals for {$registration->reservation_code}",
            null,
            null,
            // Laundry, minibar and breakfast are hotel incidentals, not room
            // revenue, so they are booked to Other Income rather than the
            // frontdesk module default of 4000 Room Revenue.
            config('finance.accounts.other_income', '4900'),
        );

        $this->requireLedgerEntry(
            "SALE-registration-{$registration->id}",
            "incidental settlement for {$registration->reservation_code}"
        );

        $this->checkOut($registration, $unit, $checkOut->copy()->setTime(11, 30));

        // Matches syncBookingStatus() in RegistrationController.
        $booking->update(['status' => 'completed']);
    }

    /**
     * Scenario 2: a guest on their second documented stay, currently in house.
     */
    private function seedReturningGuest(RoomType $roomType, RoomUnit $pastUnit, RoomUnit $currentUnit): void
    {
        $guest = Guest::firstOrCreate(
            ['email' => 'demo.returning.guest@example.test'],
            [
                'full_name' => 'Chinedu Balogun',
                'contact_number' => '+2348000000002',
                'nationality' => 'Nigeria',
                'gender' => 'Male',
                'email' => 'demo.returning.guest@example.test',
                'company_name' => 'Brickspoint Asokoro',
                'occupation' => 'Consultant',
                'visit_count' => 1,
                'last_visit_at' => Carbon::today()->subDays(40),
            ]
        );

        // The earlier stay, so "returning" is backed by history rather than
        // just a counter on the guest record.
        $pastCheckIn = Carbon::today()->subDays(42);
        $pastCheckOut = Carbon::today()->subDays(40);
        $pastNights = $pastCheckIn->diffInDays($pastCheckOut);
        $pastTotal = self::NIGHTLY_RATE * $pastNights;

        $pastBooking = $this->syncBooking(
            self::RETURNING_PAST_REFERENCE,
            $guest,
            $roomType,
            $pastCheckIn,
            $pastCheckOut,
            $pastTotal,
            'paid',
            'completed'
        );

        $this->bookingLedger->recordSettlement($pastBooking, 'bank_transfer');
        $this->requireLedgerEntry("SALE-booking-{$pastBooking->id}", "booking {$pastBooking->booking_reference} settlement");

        $pastRegistration = $this->syncRegistration(
            self::RETURNING_PAST_CODE,
            $pastBooking,
            $guest,
            $roomType,
            $pastUnit,
            $pastCheckIn,
            $pastCheckOut,
            'checked_out',
            [
                'checked_in_at' => $pastCheckIn->copy()->setTime(15, 40),
                'actual_checkout_at' => $pastCheckOut->copy()->setTime(10, 0),
                'checked_out_by_agent_id' => $this->agent?->id,
                'total_amount' => $pastTotal,
            ]
        );

        $pastFolio = $this->folioFor($pastRegistration);
        $this->postChargeOnce($pastFolio, 'Room charge - '.$pastNights.' night(s)', 'room', $pastTotal, $pastCheckIn);
        $this->payOnce($pastRegistration, $pastTotal, 'bank_transfer', $pastCheckOut->toDateString(), 'DEMO-RCP-RET-PST01');
        $this->folioService->closeFolio($pastFolio);

        $pastUnit->update(['status' => 'available', 'housekeeping_status' => 'clean']);

        // The current stay: checked in today, still open on the folio.
        $checkIn = Carbon::today();
        $checkOut = Carbon::today()->addDays(3);
        $nights = $checkIn->diffInDays($checkOut);
        $roomTotal = self::NIGHTLY_RATE * $nights;

        $booking = $this->syncBooking(
            self::RETURNING_BOOKING_REFERENCE,
            $guest,
            $roomType,
            $checkIn,
            $checkOut,
            $roomTotal,
            'pending',
            'confirmed'
        );

        $registration = $this->syncRegistration(
            self::RETURNING_RESERVATION_CODE,
            $booking,
            $guest,
            $roomType,
            $currentUnit,
            $checkIn,
            $checkOut,
            'checked_in',
            [
                'checked_in_at' => $checkIn->copy()->setTime(12, 20),
                'total_amount' => $roomTotal,
                'payment_method' => 'cash',
            ]
        );

        $currentUnit->update(['status' => 'occupied', 'housekeeping_status' => 'dirty']);

        $folio = $this->folioFor($registration);
        $this->postChargeOnce($folio, 'Room charge - '.$nights.' night(s)', 'room', $roomTotal, $checkIn);
        $this->postChargeOnce($folio, 'Laundry', 'laundry', 12000.00, $checkIn);
        $this->postChargeOnce($folio, 'Minibar consumption', 'service', 6000.00, $checkIn);

        // No payment and no close: the folio is left open so there is a live
        // balance for the front desk to settle.
    }

    private function demoRoomType(): RoomType
    {
        return RoomType::firstOrCreate(
            ['slug' => 'demo-stay-suite'],
            [
                'name' => 'Demo Stay Suite',
                'price' => self::NIGHTLY_RATE,
                'capacity' => 2,
                'base_occupancy' => 2,
                'bed_type' => 'King',
                'description' => 'Fixture room type used by DemoStaySeeder to demonstrate the booking to check-out journey.',
                'is_active' => true,
            ]
        );
    }

    /**
     * Dedicated units, so the demo never competes with real inventory or
     * reassigns a unit that an existing stay is sitting on.
     *
     * @return array<int, RoomUnit>
     */
    private function demoUnits(RoomType $roomType): array
    {
        $numbers = ['DEMO-101', 'DEMO-102', 'DEMO-103'];

        $units = [];

        foreach ($numbers as $number) {
            $units[] = RoomUnit::firstOrCreate(
                ['room_number' => $number],
                [
                    'room_type_id' => $roomType->id,
                    'floor' => 1,
                    'status' => 'available',
                    'housekeeping_status' => 'clean',
                ]
            );
        }

        return $units;
    }

    private function syncBooking(
        string $reference,
        Guest $guest,
        RoomType $roomType,
        Carbon $checkIn,
        Carbon $checkOut,
        float $total,
        string $paymentStatus,
        string $status
    ): Booking {
        return Booking::firstOrCreate(
            ['booking_reference' => $reference],
            [
                'guest_name' => $guest->full_name,
                'guest_email' => $guest->email,
                'guest_phone' => $guest->contact_number,
                'guest_profile_id' => $guest->id,
                'room_type_id' => $roomType->id,
                'check_in_date' => $checkIn->toDateString(),
                'check_out_date' => $checkOut->toDateString(),
                'adults' => 1,
                'children' => 0,
                'total_amount' => $total,
                'amount_paid' => $paymentStatus === 'paid' ? $total : 0,
                'payment_status' => $paymentStatus,
                'payment_method' => $paymentStatus === 'paid' ? 'paystack' : null,
                'status' => $status,
                'source' => 'website',
            ]
        );
    }

    private function syncRegistration(
        string $reservationCode,
        Booking $booking,
        Guest $guest,
        RoomType $roomType,
        RoomUnit $unit,
        Carbon $checkIn,
        Carbon $checkOut,
        string $stayStatus,
        array $extra = []
    ): Registration {
        $registration = Registration::firstOrCreate(
            ['reservation_code' => $reservationCode],
            array_merge([
                'guest_id' => $guest->id,
                'booking_id' => $booking->id,
                'original_check_in_date' => $checkIn->toDateString(),
                'original_check_out_date' => $checkOut->toDateString(),
                'title' => $guest->full_name,
                'full_name' => $guest->full_name,
                'email' => $guest->email,
                'contact_number' => $guest->contact_number,
                'nationality' => $guest->nationality,
                'gender' => $guest->gender,
                'room_type_id' => $roomType->id,
                'room_unit_id' => $unit->id,
                'room_rate' => self::NIGHTLY_RATE,
                'check_in' => $checkIn->toDateString(),
                'check_out' => $checkOut->toDateString(),
                'no_of_guests' => 1,
                'no_of_nights' => $checkIn->diffInDays($checkOut),
                'stay_status' => $stayStatus,
                'finalized_by_agent_id' => $this->agent?->id,
            ], $extra)
        );

        return $registration;
    }

    /**
     * ensureFolio() only reuses an *open* folio, so a completed stay would get a
     * second folio on every re-run. Reuse the registration's folio whatever its
     * state, and let the service create it the first time.
     */
    private function folioFor(Registration $registration): Folio
    {
        return $registration->folios()->first()
            ?? $this->folioService->ensureFolio($registration, $this->agent?->id);
    }

    /**
     * Mirrors RegistrationController::postCharge(): the charge lands on the
     * folio and is mirrored into registration_charges for the legacy charge
     * report, keyed on the description so a re-run does not double it.
     */
    private function postChargeOnce(
        Folio $folio,
        string $description,
        string $chargeType,
        float $amount,
        Carbon $postDate
    ): void {
        $alreadyPosted = FolioItem::where('folio_id', $folio->id)
            ->where('description', $description)
            ->exists();

        if ($alreadyPosted) {
            return;
        }

        $this->folioService->postCharge($folio, [
            'charge_type' => $chargeType,
            'description' => $description,
            'amount' => $amount,
            'post_date' => $postDate->toDateString(),
        ], $this->agent?->id);

        RegistrationCharge::firstOrCreate(
            [
                'registration_id' => $folio->registration_id,
                'folio_id' => $folio->id,
                'description' => $description,
            ],
            [
                'charge_type' => $chargeType,
                'amount' => $amount,
                'charge_date' => $postDate->toDateString(),
            ]
        );
    }

    private function payOnce(
        Registration $registration,
        float $amount,
        string $method,
        string $paymentDate,
        string $reference
    ): void {
        RegistrationPayment::firstOrCreate(
            ['registration_id' => $registration->id, 'reference' => $reference],
            [
                'amount' => $amount,
                'payment_method' => $method,
                'notes' => 'Demo stay settlement',
                'received_by' => $this->agent?->id,
                'payment_date' => $paymentDate,
            ]
        );
    }

    /**
     * The check-out half of RegistrationController::checkout(), minus the early
     * departure and overstay date rewriting (the demo stays run to plan) and
     * minus the notification, which has no place in a seeder.
     */
    private function checkOut(Registration $registration, RoomUnit $unit, Carbon $when): void
    {
        // A visit belongs to the transition, so only count it the first time
        // this stay is closed out. Re-running must not inflate the history.
        $alreadyCheckedOut = $registration->stay_status === 'checked_out';

        $registration->update([
            'stay_status' => 'checked_out',
            'actual_checkout_at' => $when,
            'checked_out_by_agent_id' => $this->agent?->id,
        ]);

        $unit->update(['status' => 'available', 'housekeeping_status' => 'dirty']);

        if ($alreadyCheckedOut) {
            return;
        }

        $guest = $registration->guest;

        if ($guest) {
            $guest->visit_count = ((int) $guest->visit_count) + 1;
            $guest->last_visit_at = $when;
            $guest->save();
        }
    }

    /**
     * PostingService returns null both for "already posted" and for a failed
     * post (BookingLedgerService swallows the exception), so the entry is
     * checked by its deterministic key instead of by return value.
     */
    private function requireLedgerEntry(string $entryNumber, string $label): void
    {
        if (! JournalEntry::where('entry_number', $entryNumber)->exists()) {
            throw new RuntimeException(
                "Demo stay: no journal entry [{$entryNumber}] for {$label}. Check the chart of accounts and the fiscal period."
            );
        }
    }
}
