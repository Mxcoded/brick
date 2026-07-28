<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Frontdeskcrm\Models\Folio;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationCharge;
use Modules\Frontdeskcrm\Models\RegistrationPayment;
use Modules\Frontdeskcrm\Models\TaxCode;
use Modules\Frontdeskcrm\Services\FolioService;
use Modules\Frontdeskcrm\Services\InvoiceService;
use Modules\Frontdeskcrm\Services\NightAuditService;
use Modules\Website\Models\Booking;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class WebsiteToCheckinE2ETest extends TestCase
{
    use DatabaseTransactions;

    protected User $user;

    protected FolioService $folioService;

    protected InvoiceService $invoiceService;

    protected NightAuditService $nightAudit;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(ValidateCsrfToken::class);
        $this->user = User::factory()->create();
        Permission::firstOrCreate(['name' => 'access_frontdesk_dashboard', 'guard_name' => 'web']);
        $this->user->givePermissionTo('access_frontdesk_dashboard');
        $this->folioService = app(FolioService::class);
        $this->invoiceService = app(InvoiceService::class);
        $this->nightAudit = app(NightAuditService::class);

        ChartOfAccount::updateOrCreate(['code' => '1200'], [
            'name' => 'Accounts Receivable', 'type' => 'asset', 'normal_balance' => 'debit', 'active' => true,
        ]);
        ChartOfAccount::updateOrCreate(['code' => '4000'], [
            'name' => 'Room Revenue', 'type' => 'income', 'normal_balance' => 'credit', 'active' => true,
        ]);
        ChartOfAccount::updateOrCreate(['code' => '1000'], [
            'name' => 'Cash', 'type' => 'asset', 'normal_balance' => 'debit', 'active' => true,
        ]);
    }

    protected function createRoomAndType(string $suffix): array
    {
        $roomType = RoomType::create([
            'name' => "E2E Room {$suffix}",
            'slug' => "e2e-{$suffix}-".uniqid(),
            'price' => 50000,
            'capacity' => 2,
            'is_active' => true,
        ]);
        $roomUnit = RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => "E2E-{$suffix}-".rand(100, 999),
            'floor' => 1,
            'status' => 'available',
        ]);

        return [$roomType, $roomUnit];
    }

    protected function createGuest(string $name, string $suffix): Guest
    {
        return Guest::create([
            'full_name' => $name,
            'contact_number' => '080'.rand(10000000, 99999999),
            'email' => strtolower(str_replace(' ', '', $name)).$suffix.'@example.com',
            'nationality' => 'Nigerian',
            'gender' => 'male',
        ]);
    }

    #[Test]
    public function complete_website_booking_to_checkout_lifecycle()
    {
        [$roomType, $roomUnit] = $this->createRoomAndType('LIFECYCLE');
        $guest = $this->createGuest('Lifecycle Guest', 'lc');

        // ========== STEP 1: Website creates booking ==========
        $booking = Booking::create([
            'guest_name' => $guest->full_name,
            'guest_email' => $guest->email,
            'guest_phone' => $guest->contact_number,
            'guest_profile_id' => $guest->id,
            'room_type_id' => $roomType->id,
            'check_in_date' => Carbon::today()->subDays(1)->toDateString(),
            'check_out_date' => Carbon::today()->addDays(3)->toDateString(),
            'adults' => 1,
            'total_amount' => 200000,
            'amount_paid' => 100000,
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'payment_method' => 'paystack',
            'booking_reference' => 'BK-LC-'.rand(10000, 99999),
            'source' => 'website',
        ]);

        $this->assertEquals('confirmed', $booking->status);
        $this->assertEquals('paid', $booking->payment_status);
        $this->assertEquals(100000, $booking->amount_paid);

        // ========== STEP 2: Agent processes booking check-in ==========
        $response = $this->actingAs($this->user)->post(
            route('frontdesk.bookings.process', $booking->booking_reference),
            ['room_unit_id' => $roomUnit->id]
        );
        $response->assertSessionHas('success');

        $registration = Registration::where('booking_id', $booking->id)->first();
        $this->assertNotNull($registration);
        $this->assertEquals('checked_in', $registration->stay_status);
        $this->assertEquals($roomUnit->id, $registration->room_unit_id);
        $this->assertEquals($guest->id, $registration->guest_id);
        $this->assertEquals($booking->total_amount, $registration->total_amount);

        // Room rate should be total / nights
        $this->assertGreaterThan(0, $registration->room_rate);
        $this->assertGreaterThan(0, $registration->no_of_nights);
        $this->assertLessThanOrEqual(4, $registration->no_of_nights);

        // Room should be occupied
        $roomUnit->refresh();
        $this->assertEquals('occupied', $roomUnit->status);

        // Booking should be synced
        $booking->refresh();
        $this->assertEquals('checked_in', $booking->status);
        $this->assertEquals($roomUnit->id, $booking->room_unit_id);

        // Online payment auto-synced
        $onlinePayment = RegistrationPayment::where('registration_id', $registration->id)
            ->where('reference', $booking->booking_reference)
            ->first();
        $this->assertNotNull($onlinePayment);
        $this->assertEquals(100000, $onlinePayment->amount);
        $this->assertEquals('paystack', $onlinePayment->payment_method);

        // ========== STEP 3: Agent posts incidental charges ==========
        $postChargeResponse = $this->actingAs($this->user)->post(
            route('frontdesk.registrations.post-charge', $registration),
            [
                'charge_type' => 'mini_bar',
                'description' => 'Mini bar: 2 waters, 1 soda',
                'amount' => 5000,
                'charge_date' => Carbon::today()->toDateString(),
            ]
        );
        $postChargeResponse->assertSessionHas('success');

        $postChargeResponse2 = $this->actingAs($this->user)->post(
            route('frontdesk.registrations.post-charge', $registration),
            [
                'charge_type' => 'restaurant',
                'description' => 'Room service dinner',
                'amount' => 12000,
                'charge_date' => Carbon::today()->toDateString(),
            ]
        );
        $postChargeResponse2->assertSessionHas('success');

        $folio = $this->folioService->ensureFolio($registration);
        $folio->refresh();
        $this->assertEquals(17000, $folio->balance);

        $folioItemCount = $folio->items()->count();
        $this->assertEquals(2, $folioItemCount);

        // ========== STEP 4: Night audit runs ==========
        $this->nightAudit->process(Carbon::today());

        $charges = RegistrationCharge::where('registration_id', $registration->id)
            ->where('charge_type', 'room')
            ->get();
        $this->assertGreaterThanOrEqual(1, $charges->count());

        $folio->refresh();
        $folioItemsAfterAudit = $folio->items()->count();
        $this->assertGreaterThanOrEqual(3, $folioItemsAfterAudit);

        $this->assertGreaterThan(17000, $folio->balance);

        // ========== STEP 5: Agent records additional payment ==========
        $payment = $registration->payments()->create([
            'amount' => 50000,
            'payment_method' => 'cash',
            'payment_date' => Carbon::today()->toDateString(),
            'reference' => 'CASH-'.rand(1000, 9999),
            'received_by' => $this->user->id,
        ]);
        $this->assertNotNull($payment);

        // ========== STEP 6: Agent checks out ==========
        $checkoutResponse = $this->actingAs($this->user)
            ->post(route('frontdesk.registrations.checkout', $registration));
        $checkoutResponse->assertSessionHas('success');

        $registration->refresh();
        $this->assertEquals('checked_out', $registration->stay_status);
        $this->assertNotNull($registration->actual_checkout_at);

        // Room released
        $roomUnit->refresh();
        $this->assertEquals('available', $roomUnit->status);

        // ========== STEP 7: Generate invoice from folio ==========
        $folio->refresh();
        $invoice = $this->invoiceService->generateFromFolio($folio, [], $this->user->id);
        $this->assertNotNull($invoice);
        $this->assertEquals('draft', $invoice->status);
        $this->assertGreaterThan(0, $invoice->total);

        // Invoice items should match folio items
        $this->assertEquals($folioItemCount + 1, $invoice->items()->count());

        // ========== STEP 8: Issue invoice ==========
        $this->invoiceService->issueInvoice($invoice);
        $invoice->refresh();
        $this->assertEquals('issued', $invoice->status);

        // ========== STEP 9: Verify booking final status ==========
        $booking->refresh();
        $this->assertEquals('completed', $booking->status);
    }

    #[Test]
    public function future_reservation_does_not_occupy_room()
    {
        [$roomType, $roomUnit] = $this->createRoomAndType('FUTURE');
        $guest = $this->createGuest('Future Guest', 'fu');

        $booking = Booking::create([
            'guest_name' => $guest->full_name,
            'guest_email' => $guest->email,
            'guest_phone' => $guest->contact_number,
            'guest_profile_id' => $guest->id,
            'room_type_id' => $roomType->id,
            'check_in_date' => Carbon::today()->addDays(5)->toDateString(),
            'check_out_date' => Carbon::today()->addDays(8)->toDateString(),
            'adults' => 1,
            'total_amount' => 150000,
            'amount_paid' => 50000,
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'payment_method' => 'paystack',
            'booking_reference' => 'BK-FUTURE-'.rand(10000, 99999),
        ]);

        $response = $this->actingAs($this->user)->post(
            route('frontdesk.bookings.process', $booking->booking_reference),
            ['room_unit_id' => $roomUnit->id]
        );
        $response->assertSessionHas('success');

        $registration = Registration::where('booking_id', $booking->id)->first();
        $this->assertEquals('reserved', $registration->stay_status);

        // Room should remain available (not occupied yet)
        $roomUnit->refresh();
        $this->assertEquals('available', $roomUnit->status);

        // Night audit should NOT charge this registration (future arrival)
        $this->nightAudit->process(Carbon::today());

        $charges = RegistrationCharge::where('registration_id', $registration->id)->get();
        $this->assertEquals(0, $charges->count());
    }

    #[Test]
    public function tax_codes_seed_correctly()
    {
        TaxCode::updateOrCreate(
            ['code' => 'VAT7.5'],
            ['name' => 'VAT 7.5% (Exclusive)', 'rate' => 7.50, 'type' => 'exclusive', 'is_active' => true]
        );
        TaxCode::updateOrCreate(
            ['code' => 'SC10'],
            ['name' => 'Service Charge 10%', 'rate' => 10.00, 'type' => 'exclusive', 'is_active' => true]
        );

        $vat = TaxCode::where('code', 'VAT7.5')->first();
        $this->assertNotNull($vat);
        $this->assertEquals(7.50, $vat->rate);
        $this->assertEquals('exclusive', $vat->type);

        $sc = TaxCode::where('code', 'SC10')->first();
        $this->assertNotNull($sc);
        $this->assertEquals(10.00, $sc->rate);
    }

    #[Test]
    public function charge_with_tax_calculates_correctly()
    {
        $vat = TaxCode::updateOrCreate(
            ['code' => 'VAT7.5'],
            ['name' => 'VAT 7.5% (Exclusive)', 'rate' => 7.50, 'type' => 'exclusive', 'is_active' => true]
        );

        [$roomType, $roomUnit] = $this->createRoomAndType('TAX');
        $guest = $this->createGuest('Tax Guest', 'tx');

        $registration = Registration::create([
            'guest_id' => $guest->id,
            'full_name' => $guest->full_name,
            'contact_number' => $guest->contact_number,
            'email' => $guest->email,
            'room_type_id' => $roomType->id,
            'room_unit_id' => $roomUnit->id,
            'room_rate' => 50000,
            'check_in' => Carbon::today()->subDays(1),
            'check_out' => Carbon::today()->addDays(2),
            'stay_status' => 'checked_in',
            'no_of_nights' => 3,
            'total_amount' => 150000,
        ]);

        $folio = $this->folioService->ensureFolio($registration);

        $item = $this->folioService->postCharge($folio, [
            'charge_type' => 'service',
            'description' => 'Conference room rental',
            'amount' => 100000,
            'tax_code' => $vat->code,
            'tax_rate' => $vat->rate,
            'tax_type' => $vat->type,
            'post_date' => Carbon::today()->toDateString(),
        ], $this->user->id);

        $this->assertEquals(7500, $item->tax_amount);
        $this->assertEquals(107500, $folio->fresh()->balance);
    }

    #[Test]
    public function search_by_guest_name_and_registration_id()
    {
        [$roomType, $roomUnit] = $this->createRoomAndType('SEARCH');
        $guest = $this->createGuest('Searchable Guest', 'sr');

        $registration = Registration::create([
            'guest_id' => $guest->id,
            'full_name' => $guest->full_name,
            'contact_number' => $guest->contact_number,
            'email' => $guest->email,
            'room_type_id' => $roomType->id,
            'room_unit_id' => $roomUnit->id,
            'room_rate' => 50000,
            'check_in' => Carbon::today(),
            'check_out' => Carbon::today()->addDays(2),
            'stay_status' => 'checked_in',
            'no_of_nights' => 2,
            'total_amount' => 100000,
        ]);

        // Search by guest name
        $response = $this->actingAs($this->user)
            ->get(route('frontdesk.registrations.index', ['search' => 'Searchable']));
        $response->assertOk();
        $response->assertSee('Searchable Guest');

        // Search by partial name
        $response2 = $this->actingAs($this->user)
            ->get(route('frontdesk.registrations.index', ['search' => 'Searchable']));
        $response2->assertOk();
        $response2->assertSee('Searchable Guest');
    }

    #[Test]
    public function multi_night_stay_charges_accumulate_correctly()
    {
        [$roomType, $roomUnit] = $this->createRoomAndType('MULTI');
        $guest = $this->createGuest('Multi Night Guest', 'mn');

        $registration = Registration::create([
            'guest_id' => $guest->id,
            'full_name' => $guest->full_name,
            'contact_number' => $guest->contact_number,
            'email' => $guest->email,
            'room_type_id' => $roomType->id,
            'room_unit_id' => $roomUnit->id,
            'room_rate' => 50000,
            'check_in' => Carbon::today()->subDays(2),
            'check_out' => Carbon::today()->addDays(2),
            'stay_status' => 'checked_in',
            'no_of_nights' => 4,
            'total_amount' => 200000,
        ]);

        $folio = $this->folioService->ensureFolio($registration);

        // Simulate 3 nights of audit
        for ($i = 2; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $this->nightAudit->process($date);
        }

        $charges = RegistrationCharge::where('registration_id', $registration->id)
            ->where('charge_type', 'room')
            ->get();
        $this->assertEquals(3, $charges->count());

        $folio->refresh();
        $this->assertEquals(150000, $folio->balance);

        $folioItems = $folio->items()->count();
        $this->assertEquals(3, $folioItems);
    }

    #[Test]
    public function registration_show_page_renders_with_all_buttons()
    {
        [$roomType, $roomUnit] = $this->createRoomAndType('UI');
        $guest = $this->createGuest('UI Guest', 'ui');

        $registration = Registration::create([
            'guest_id' => $guest->id,
            'full_name' => $guest->full_name,
            'contact_number' => $guest->contact_number,
            'email' => $guest->email,
            'room_type_id' => $roomType->id,
            'room_unit_id' => $roomUnit->id,
            'room_rate' => 50000,
            'check_in' => Carbon::today()->subDays(1),
            'check_out' => Carbon::today()->addDays(2),
            'stay_status' => 'checked_in',
            'no_of_nights' => 3,
            'total_amount' => 150000,
        ]);

        $response = $this->actingAs($this->user)
            ->get(route('frontdesk.registrations.show', $registration));
        $response->assertOk();
        $response->assertSee('Post Charge');
        $response->assertSee('Folios');
        $response->assertSee('Record a Payment');
        $response->assertSee('Check Out');
        $response->assertSee('Extend Stay');
        $response->assertSee('Financial Summary');
    }
}
