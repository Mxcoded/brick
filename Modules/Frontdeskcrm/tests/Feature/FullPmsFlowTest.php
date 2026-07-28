<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationCharge;
use Modules\Frontdeskcrm\Models\RegistrationPayment;
use Modules\Frontdeskcrm\Services\FolioService;
use Modules\Frontdeskcrm\Services\InvoiceService;
use Modules\Frontdeskcrm\Services\NightAuditService;
use Modules\Website\Models\Booking;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class FullPmsFlowTest extends TestCase
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

        ChartOfAccount::updateOrCreate(
            ['code' => '1200'],
            ['name' => 'Accounts Receivable', 'type' => 'asset', 'normal_balance' => 'debit', 'active' => true]
        );
        ChartOfAccount::updateOrCreate(
            ['code' => '4000'],
            ['name' => 'Room Revenue', 'type' => 'income', 'normal_balance' => 'credit', 'active' => true]
        );
        ChartOfAccount::updateOrCreate(
            ['code' => '1000'],
            ['name' => 'Cash', 'type' => 'asset', 'normal_balance' => 'debit', 'active' => true]
        );
    }

    #[Test]
    public function online_booking_full_flow()
    {
        $roomType = RoomType::create([
            'name' => 'Online Booking Room',
            'slug' => 'online-booking-'.uniqid(),
            'price' => 50000,
            'capacity' => 2,
            'is_active' => true,
        ]);
        $roomUnit = RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => 'OB-'.rand(100, 999),
            'floor' => 1,
            'status' => 'available',
        ]);
        $guest = Guest::create([
            'full_name' => 'Online Booking Guest',
            'contact_number' => '080'.rand(10000000, 99999999),
            'email' => 'online'.uniqid().'@example.com',
            'nationality' => 'Nigerian',
            'gender' => 'male',
        ]);

        $booking = Booking::create([
            'guest_name' => $guest->full_name,
            'guest_email' => $guest->email,
            'guest_phone' => $guest->contact_number,
            'guest_profile_id' => $guest->id,
            'room_type_id' => $roomType->id,
            'check_in_date' => Carbon::today()->subDays(1)->toDateString(),
            'check_out_date' => Carbon::today()->addDays(2)->toDateString(),
            'adults' => 1,
            'total_amount' => 150000,
            'amount_paid' => 150000,
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'booking_reference' => 'BK-ONLINE-'.rand(10000, 99999),
        ]);

        $this->assertNotNull($booking);
        $this->assertEquals('paid', $booking->payment_status);
        $this->assertEquals(150000, $booking->amount_paid);

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

        $this->assertGreaterThan(0, $registration->room_rate);

        $onlinePayment = RegistrationPayment::where('registration_id', $registration->id)
            ->where('reference', $booking->booking_reference)
            ->first();
        $this->assertNotNull($onlinePayment);
        $this->assertEquals(150000, $onlinePayment->amount);

        $roomUnit->refresh();
        $this->assertEquals('occupied', $roomUnit->status);

        $this->nightAudit->process(Carbon::today());

        $charges = RegistrationCharge::where('registration_id', $registration->id)->get();
        $this->assertGreaterThanOrEqual(1, $charges->count());

        $folio = $this->folioService->ensureFolio($registration);
        $folio->refresh();
        $this->assertGreaterThan(0, $folio->balance);

        $folioItemCount = $folio->items()->count();
        $this->assertGreaterThanOrEqual(1, $folioItemCount);

        $payment = $registration->payments()->create([
            'amount' => 50000,
            'payment_method' => 'cash',
            'payment_date' => Carbon::today()->toDateString(),
            'reference' => 'RCPT-'.rand(1000, 9999),
            'received_by' => $this->user->id,
        ]);
        $this->assertNotNull($payment);

        $response = $this->actingAs($this->user)
            ->post(route('frontdesk.registrations.checkout', $registration));
        $response->assertSessionHas('success');

        $registration->refresh();
        $this->assertEquals('checked_out', $registration->stay_status);

        $folio->refresh();
        $this->assertLessThanOrEqual($folio->balance, $folio->balance);

        $roomUnit->refresh();
        $this->assertEquals('available', $roomUnit->status);

        $invoice = $this->invoiceService->generateFromFolio($folio, [], $this->user->id);
        $this->assertNotNull($invoice);
        $this->assertEquals('draft', $invoice->status);
        $this->assertGreaterThan(0, $invoice->total);

        $booking->refresh();
        $this->assertEquals('completed', $booking->status);
    }

    #[Test]
    public function walk_in_full_flow()
    {
        $roomType = RoomType::create([
            'name' => 'Walk-in Room',
            'slug' => 'walkin-test-'.uniqid(),
            'price' => 40000,
            'capacity' => 2,
            'is_active' => true,
        ]);
        $roomUnit = RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => 'WI-'.rand(100, 999),
            'floor' => 1,
            'status' => 'available',
        ]);
        $guest = Guest::create([
            'full_name' => 'Walk-in Guest',
            'contact_number' => '080'.rand(10000000, 99999999),
            'email' => 'walkin'.uniqid().'@example.com',
            'nationality' => 'Nigerian',
            'gender' => 'female',
        ]);

        $registration = Registration::create([
            'guest_id' => $guest->id,
            'full_name' => $guest->full_name,
            'contact_number' => $guest->contact_number,
            'email' => $guest->email,
            'nationality' => $guest->nationality,
            'room_type_id' => $roomType->id,
            'room_unit_id' => $roomUnit->id,
            'room_rate' => 40000,
            'check_in' => Carbon::today()->subDays(1),
            'check_out' => Carbon::today()->addDays(3),
            'stay_status' => 'draft_by_guest',
            'no_of_nights' => 4,
            'total_amount' => 160000,
        ]);
        $this->assertNotNull($registration);
        $this->assertEquals('draft_by_guest', $registration->stay_status);

        $roomUnit->update(['status' => 'occupied']);

        $registration->update(['stay_status' => 'checked_in', 'checked_in_at' => now()]);

        $registration->refresh();
        $this->assertEquals('checked_in', $registration->stay_status);

        $this->nightAudit->process(Carbon::today());

        $charges = RegistrationCharge::where('registration_id', $registration->id)->get();
        $this->assertGreaterThanOrEqual(1, $charges->count());

        $folio = $this->folioService->ensureFolio($registration);
        $folio->refresh();
        $this->assertGreaterThan(0, $folio->balance);

        $payment = $registration->payments()->create([
            'amount' => 60000,
            'payment_method' => 'pos',
            'payment_date' => Carbon::today()->toDateString(),
            'reference' => 'POS-'.rand(1000, 9999),
            'received_by' => $this->user->id,
        ]);
        $this->assertNotNull($payment);

        $response = $this->actingAs($this->user)
            ->post(route('frontdesk.registrations.checkout', $registration));
        $response->assertSessionHas('success');

        $registration->refresh();
        $this->assertEquals('checked_out', $registration->stay_status);

        $roomUnit->refresh();
        $this->assertEquals('available', $roomUnit->status);

        $folio->refresh();

        $invoice = $this->invoiceService->generateFromFolio($folio, [], $this->user->id);
        $this->assertNotNull($invoice);
        $this->assertEquals('draft', $invoice->status);
        $this->assertGreaterThan(0, $invoice->total);
    }

    #[Test]
    public function search_by_booking_reference_works()
    {
        $roomType = RoomType::create([
            'name' => 'Search Room',
            'slug' => 'search-room-'.uniqid(),
            'price' => 50000,
            'capacity' => 2,
            'is_active' => true,
        ]);
        $roomUnit = RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => 'SR-'.rand(100, 999),
            'floor' => 1,
            'status' => 'available',
        ]);
        $guest = Guest::create([
            'full_name' => 'Search Guest',
            'contact_number' => '080'.rand(10000000, 99999999),
            'email' => 'search'.uniqid().'@example.com',
            'nationality' => 'Nigerian',
            'gender' => 'male',
        ]);

        $booking = Booking::create([
            'guest_name' => $guest->full_name,
            'guest_email' => $guest->email,
            'guest_phone' => $guest->contact_number,
            'guest_profile_id' => $guest->id,
            'room_type_id' => $roomType->id,
            'check_in_date' => Carbon::today()->toDateString(),
            'check_out_date' => Carbon::today()->addDays(2)->toDateString(),
            'adults' => 1,
            'total_amount' => 100000,
            'status' => 'confirmed',
            'booking_reference' => 'BK-SEARCH-'.rand(10000, 99999),
        ]);

        Registration::create([
            'guest_id' => $guest->id,
            'booking_id' => $booking->id,
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

        $response = $this->actingAs($this->user)
            ->get(route('frontdesk.registrations.index', ['search' => $booking->booking_reference]));
        $response->assertOk();
        $response->assertSee($guest->full_name);
    }

    #[Test]
    public function incidental_charge_posting_works()
    {
        $roomType = RoomType::create([
            'name' => 'Incidental Room',
            'slug' => 'incidental-test-'.uniqid(),
            'price' => 50000,
            'capacity' => 2,
            'is_active' => true,
        ]);
        $roomUnit = RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => 'IC-'.rand(100, 999),
            'floor' => 1,
            'status' => 'occupied',
        ]);
        $guest = Guest::create([
            'full_name' => 'Incidental Guest',
            'contact_number' => '080'.rand(10000000, 99999999),
            'email' => 'incidental'.uniqid().'@example.com',
            'nationality' => 'Nigerian',
            'gender' => 'male',
        ]);
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

        $response = $this->actingAs($this->user)->post(
            route('frontdesk.registrations.post-charge', $registration),
            [
                'charge_type' => 'laundry',
                'description' => 'Express laundry service',
                'amount' => 15000,
                'charge_date' => Carbon::today()->toDateString(),
            ]
        );

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $response->assertSessionHas('success');

        $folio = $this->folioService->ensureFolio($registration);
        $folio->refresh();
        $this->assertEquals(15000, $folio->balance);

        $charge = RegistrationCharge::where('registration_id', $registration->id)
            ->where('charge_type', 'laundry')
            ->first();
        $this->assertNotNull($charge);
        $this->assertEquals(15000, $charge->amount);
        $this->assertEquals('Express laundry service', $charge->description);
    }

    #[Test]
    public function registration_show_page_has_folio_invoice_charge_buttons()
    {
        $roomType = RoomType::create([
            'name' => 'UI Check Room',
            'slug' => 'ui-check-'.uniqid(),
            'price' => 50000,
            'capacity' => 2,
            'is_active' => true,
        ]);
        $roomUnit = RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => 'UI-'.rand(100, 999),
            'floor' => 1,
            'status' => 'occupied',
        ]);
        $guest = Guest::create([
            'full_name' => 'UI Check Guest',
            'contact_number' => '080'.rand(10000000, 99999999),
            'email' => 'ui'.uniqid().'@example.com',
            'nationality' => 'Nigerian',
            'gender' => 'male',
        ]);
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
    }
}
