<?php

namespace Modules\Website\Tests\Feature;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Website\Emails\BookingConfirmation;
use Modules\Website\Models\Addon;
use Modules\Website\Models\Booking;
use Modules\Website\Models\PaymentGateway;
use Modules\Website\Models\Refund;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Modules\Website\Services\BookingCartService;
use Tests\TestCase;

/**
 * End-to-end coverage of booking a room from the website and paying through the
 * pluggable Paystack gateway, using the existing test Paystack API key.
 *
 * Network calls to Paystack are faked; signatures are computed with the real
 * test secret so the webhook verification path is exercised faithfully.
 */
class BookingPaymentFlowTest extends TestCase
{
    use DatabaseTransactions;

    protected string $secret;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        // Existing test Paystack key from .env (used to sign webhooks).
        $this->secret = config('services.paystack.secret', 'sk_test_234c0da4eef69813efc349936f79d79c0acda9db');
        config(['services.paystack.secret' => $this->secret]);

        // Isolate from any gateway rows committed by other suites in the shared test DB.
        PaymentGateway::query()->delete();

        // Set reservations email so sendConfirmationEmail dispatches the staff copy.
        config(['mail.reservations_email' => 'rsv@brickspoint.com']);

        // Seed the ChartOfAccount records required by the webhook finance integration.
        if (! ChartOfAccount::where('code', '1110')->exists()) {
            ChartOfAccount::create([
                'code' => '1110', 'name' => 'Paystack Clearing', 'type' => 'asset',
                'normal_balance' => 'debit', 'active' => true,
            ]);
        }
        if (! ChartOfAccount::where('code', '4000')->exists()) {
            ChartOfAccount::create([
                'code' => '4000', 'name' => 'Room Revenue', 'type' => 'income',
                'normal_balance' => 'credit', 'active' => true,
            ]);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    private function makeAvailableRoomType(float $price = 20000): RoomType
    {
        $roomType = RoomType::create([
            'name' => 'Deluxe Suite',
            'slug' => 'deluxe-suite-'.uniqid(),
            'price' => $price,
            'capacity' => 2,
            'is_active' => true,
        ]);

        RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => '1'.rand(100, 999),
            'floor' => 1,
            'status' => 'available',
        ]);

        return $roomType;
    }

    private function bookingPayload(int $roomTypeId, string $method = 'pay_on_arrival'): array
    {
        return [
            'guest_name' => 'Test Guest',
            'guest_email' => 'guest@example.com',
            'guest_phone' => '08012345678',
            'guest_gender' => 'male',
            'guest_address' => '123 Test Avenue',
            'guest_nationality' => 'Nigerian',
            'guest_id_type' => 'NIN',
            'guest_id_number' => '12345678901',
            'guest_dob' => '1990-01-01',
            'adults' => 2,
            'children' => 0,
            'payment_method' => $method,
            'room_type_id' => $roomTypeId,
            'check_in_date' => now()->addDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(2)->format('Y-m-d'),
            'special_requests' => null,
        ];
    }

    private function makePaidBooking(string $reference, float $amount = 20000): Booking
    {
        $roomType = $this->makeAvailableRoomType($amount);

        return Booking::create([
            'booking_reference' => $reference,
            'room_type_id' => $roomType->id,
            'source' => 'website',
            'guest_name' => 'Web Guest',
            'guest_email' => 'webguest@example.com',
            'guest_phone' => '08012345678',
            'check_in_date' => now()->addDays(1),
            'check_out_date' => now()->addDays(2),
            'adults' => 2,
            'total_amount' => $amount,
            'amount_paid' => $amount,
            'payment_status' => 'paid',
            'status' => 'confirmed',
            'payment_method' => 'paystack',
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // SCENARIO GROUP 1 — Booking creation (storeBooking)
    // ─────────────────────────────────────────────────────────────

    public function test_pay_on_arrival_single_room_booking_is_created_and_confirmed(): void
    {
        Mail::fake();
        $roomType = $this->makeAvailableRoomType();

        $response = $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id, 'pay_on_arrival'));

        $response->assertRedirectContains('/booking/confirmation');
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bookings', [
            'guest_email' => 'guest@example.com',
            'payment_status' => 'pending',
            'status' => 'pending',
            'payment_method' => 'pay_on_arrival',
        ]);

        // Guest + reservations copy both dispatched.
        Mail::assertSent(BookingConfirmation::class, fn ($m) => $m->hasTo('guest@example.com'));
        Mail::assertSent(BookingConfirmation::class, fn ($m) => $m->hasTo('rsv@brickspoint.com'));
    }

    public function test_paystack_single_room_booking_redirects_to_gateway(): void
    {
        Mail::fake();
        $roomType = $this->makeAvailableRoomType();

        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => ['authorization_url' => 'https://checkout.paystack.com/abc123'],
            ], 200),
        ]);

        $response = $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id, 'paystack'));

        $response->assertRedirect('https://checkout.paystack.com/abc123');
        $this->assertDatabaseHas('bookings', ['payment_status' => 'pending', 'payment_method' => 'paystack']);
        Http::assertSent(fn ($r) => str_contains($r->url(), 'api.paystack.co/transaction/initialize'));
    }

    public function test_paystack_booking_charges_add_on_inclusive_amount(): void
    {
        Mail::fake();
        $roomType = $this->makeAvailableRoomType(20000);
        $addon = Addon::create([
            'name' => 'Airport Pickup',
            'slug' => 'airport-pickup-'.uniqid(),
            'price' => 8000,
            'is_per_night' => false,
            'is_active' => true,
        ]);

        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => ['authorization_url' => 'https://checkout.paystack.com/abc123'],
            ], 200),
        ]);

        $payload = $this->bookingPayload($roomType->id, 'paystack');
        $payload['addons'] = [$addon->id];

        $this->post(route('website.booking.store'), $payload);

        // 1 night at ₦20,000 + ₦8,000 one-time add-on => ₦28,000 => 2,800,000 kobo
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.paystack.co/transaction/initialize')
                && (int) ($request->data()['amount'] ?? 0) === 2800000;
        });

        $booking = Booking::where('guest_email', 'guest@example.com')->orderByDesc('id')->first();
        $this->assertNotNull($booking);
        $this->assertSame(1, $booking->addons()->count());
        $this->assertSame(28000.0, (float) $booking->total_amount);
    }

    public function test_paystack_grouped_booking_charges_add_on_inclusive_amount(): void
    {
        Mail::fake();
        $roomType = $this->makeAvailableRoomType(20000);
        $addon = Addon::create([
            'name' => 'Spa Session',
            'slug' => 'spa-session-'.uniqid(),
            'price' => 8000,
            'is_per_night' => false,
            'is_active' => true,
        ]);

        $this->withSession([
            BookingCartService::SESSION_KEY => [
                'check_in' => now()->addDays(1)->format('Y-m-d'),
                'check_out' => now()->addDays(3)->format('Y-m-d'),
                'nights' => 2,
                'addons' => [
                    $addon->id => [
                        'addon_id' => $addon->id,
                        'name' => $addon->name,
                        'price' => 8000.0,
                        'is_per_night' => false,
                        'quantity' => 1,
                    ],
                ],
                'items' => [
                    $roomType->id => [
                        'room_type_id' => $roomType->id,
                        'room_type_name' => $roomType->name,
                        'quantity' => 2,
                        'price_per_night' => 20000,
                        'base_total' => 40000,
                        'guest_fee_per_night' => 0,
                        'guest_fee_total' => 0,
                        'total_rate' => 40000,
                        'rate_code_id' => null,
                        'capacity' => 2,
                        'adults' => 1,
                        'children' => 0,
                        'image_url' => null,
                        'nights' => 2,
                        'subtotal' => 80000,
                    ],
                ],
            ],
        ]);

        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => ['authorization_url' => 'https://checkout.paystack.com/group123'],
            ], 200),
        ]);

        $payload = $this->bookingPayload($roomType->id, 'paystack');
        unset($payload['room_type_id'], $payload['check_in_date'], $payload['check_out_date']);

        $this->post(route('website.booking.store'), $payload);

        // 2 rooms × ₦40,000 (2 nights) + ₦8,000 add-on => ₦88,000 => 8,800,000 kobo
        Http::assertSent(function ($request) {
            return str_contains($request->url(), 'api.paystack.co/transaction/initialize')
                && (int) ($request->data()['amount'] ?? 0) === 8800000;
        });
    }

    public function test_paystack_initialization_failure_returns_error_and_keeps_booking_pending(): void
    {
        Mail::fake();
        $roomType = $this->makeAvailableRoomType();

        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => false,
                'message' => 'Gateway timeout',
            ], 200),
        ]);

        $response = $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id, 'paystack'));

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('bookings', ['payment_status' => 'pending']);
    }

    public function test_booking_requires_valid_payment_method(): void
    {
        $roomType = $this->makeAvailableRoomType();

        $payload = $this->bookingPayload($roomType->id, 'pay_on_arrival');
        $payload['payment_method'] = 'stripe'; // not offered on the website booking form

        $response = $this->post(route('website.booking.store'), $payload);

        $response->assertSessionHasErrors('payment_method');
        $this->assertDatabaseMissing('bookings', ['guest_email' => 'guest@example.com']);
    }

    public function test_booking_rejects_missing_required_guest_fields(): void
    {
        $roomType = $this->makeAvailableRoomType();

        $payload = $this->bookingPayload($roomType->id);
        unset($payload['guest_name'], $payload['guest_email']);

        $response = $this->post(route('website.booking.store'), $payload);

        $response->assertSessionHasErrors(['guest_name', 'guest_email']);
    }

    // ─────────────────────────────────────────────────────────────
    // SCENARIO GROUP 2 — Paystack callback (verifyPayment)
    // ─────────────────────────────────────────────────────────────

    public function test_callback_marks_single_booking_paid_and_confirms(): void
    {
        Mail::fake();
        $booking = Booking::create([
            'booking_reference' => 'BK'.date('y').'TEST1',
            'room_type_id' => $this->makeAvailableRoomType()->id,
            'source' => 'website',
            'guest_name' => 'CB Guest',
            'guest_email' => 'cbguest@example.com',
            'guest_phone' => '08012345678',
            'check_in_date' => now()->addDays(1),
            'check_out_date' => now()->addDays(2),
            'adults' => 2,
            'total_amount' => 20000,
            'payment_status' => 'pending',
            'status' => 'pending',
            'payment_method' => 'paystack',
        ]);

        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => ['status' => 'success', 'reference' => $booking->booking_reference],
            ], 200),
        ]);

        $response = $this->get(route('website.payment.callback', ['reference' => $booking->booking_reference]));

        $response->assertRedirectContains('/booking/confirmation');
        $this->assertDatabaseHas('bookings', [
            'booking_reference' => $booking->booking_reference,
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);
        Mail::assertSent(BookingConfirmation::class, fn ($m) => $m->hasTo('cbguest@example.com'));
    }

    public function test_callback_marks_group_booking_paid(): void
    {
        Mail::fake();
        $groupId = 'GRP'.date('y').'GRP01';
        $roomTypeId = $this->makeAvailableRoomType()->id;

        foreach (['A', 'B'] as $suffix) {
            Booking::create([
                'booking_reference' => 'BK'.date('y').'GRP'.$suffix,
                'booking_group_id' => $groupId,
                'room_type_id' => $roomTypeId,
                'source' => 'website',
                'guest_name' => 'Group Guest',
                'guest_email' => 'group@example.com',
                'guest_phone' => '08012345678',
                'check_in_date' => now()->addDays(1),
                'check_out_date' => now()->addDays(2),
                'adults' => 2,
                'total_amount' => 20000,
                'payment_status' => 'pending',
                'status' => 'pending',
                'payment_method' => 'paystack',
            ]);
        }

        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => ['status' => 'success', 'reference' => $groupId],
            ], 200),
        ]);

        $response = $this->get(route('website.payment.callback', ['reference' => $groupId]));

        $response->assertRedirectContains('/booking/confirmation');
        $this->assertEquals(2, Booking::where('booking_group_id', $groupId)->where('payment_status', 'paid')->count());
    }

    public function test_callback_without_reference_redirects_home(): void
    {
        $response = $this->get(route('website.payment.callback'));

        $response->assertRedirect(route('website.home'));
        $response->assertSessionHas('error');
    }

    public function test_callback_with_failed_verification_redirects_to_booking_error(): void
    {
        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => ['status' => 'abandoned', 'reference' => 'BKX'],
            ], 200),
        ]);

        $response = $this->get(route('website.payment.callback', ['reference' => 'BKX']));

        $response->assertRedirect(route('website.booking'));
        $response->assertSessionHas('error');
    }

    public function test_callback_with_unknown_reference_redirects_to_booking_error(): void
    {
        Http::fake([
            'api.paystack.co/transaction/verify/*' => Http::response([
                'status' => true,
                'data' => ['status' => 'success', 'reference' => 'BK_UNKNOWN'],
            ], 200),
        ]);

        $response = $this->get(route('website.payment.callback', ['reference' => 'BK_UNKNOWN']));

        $response->assertRedirect(route('website.booking'));
        $response->assertSessionHas('error');
    }

    // ─────────────────────────────────────────────────────────────
    // SCENARIO GROUP 3 — Paystack webhook (async events)
    // ─────────────────────────────────────────────────────────────

    private function signedWebhook(array $event): array
    {
        $payload = json_encode($event);

        return [
            'x-paystack-signature' => hash_hmac('sha512', $payload, $this->secret),
            'Content-Type' => 'application/json',
        ];
    }

    public function test_webhook_charge_success_marks_single_booking_paid_and_posts_to_ledger(): void
    {
        Mail::fake();
        $booking = $this->makePaidBooking('BK'.date('y').'WH01', 20000);
        $booking->update(['payment_status' => 'pending', 'status' => 'pending']);

        $event = [
            'event' => 'charge.success',
            'data' => [
                'reference' => $booking->booking_reference,
                'status' => 'success',
                'amount' => 2000000,
                'channel' => 'card',
            ],
        ];

        $response = $this->postJson('/paystack/webhook', $event, $this->signedWebhook($event));

        $response->assertStatus(200);
        $this->assertDatabaseHas('bookings', [
            'booking_reference' => $booking->booking_reference,
            'payment_status' => 'paid',
            'payment_method' => 'paystack',
        ]);

        // Finance integration: sale posts to the Paystack clearing account (1110)
        // and credits website room revenue (4000).
        $this->assertDatabaseHas('finance_journal_entries', [
            'entry_number' => 'SALE-booking-'.$booking->id,
        ]);
        $paystackClearing = ChartOfAccount::where('code', '1110')->firstOrFail()->id;
        $roomRevenue = ChartOfAccount::where('code', '4000')->firstOrFail()->id;
        $this->assertDatabaseHas('finance_journal_lines', [
            'account_id' => $paystackClearing,
            'debit' => 20000,
        ]);
        $this->assertDatabaseHas('finance_journal_lines', [
            'account_id' => $roomRevenue,
            'credit' => 20000,
        ]);
    }

    public function test_webhook_charge_success_marks_group_booking_paid(): void
    {
        Mail::fake();
        $groupId = 'GRP'.date('y').'WH02';
        $roomTypeId = $this->makeAvailableRoomType()->id;

        $b1 = Booking::create([
            'booking_reference' => 'BK'.date('y').'WHA', 'booking_group_id' => $groupId,
            'room_type_id' => $roomTypeId, 'source' => 'website', 'guest_name' => 'G', 'guest_email' => 'g@e.com',
            'guest_phone' => '08012345678', 'check_in_date' => now()->addDays(1), 'check_out_date' => now()->addDays(2),
            'adults' => 2, 'total_amount' => 15000, 'payment_status' => 'pending', 'status' => 'pending', 'payment_method' => 'paystack',
        ]);
        $b2 = Booking::create([
            'booking_reference' => 'BK'.date('y').'WHB', 'booking_group_id' => $groupId,
            'room_type_id' => $roomTypeId, 'source' => 'website', 'guest_name' => 'G', 'guest_email' => 'g@e.com',
            'guest_phone' => '08012345678', 'check_in_date' => now()->addDays(1), 'check_out_date' => now()->addDays(2),
            'adults' => 2, 'total_amount' => 15000, 'payment_status' => 'pending', 'status' => 'pending', 'payment_method' => 'paystack',
        ]);

        $event = [
            'event' => 'charge.success',
            'data' => ['reference' => $groupId, 'status' => 'success', 'amount' => 3000000, 'channel' => 'card'],
        ];

        $response = $this->postJson('/paystack/webhook', $event, $this->signedWebhook($event));

        $response->assertStatus(200);
        $this->assertEquals(2, Booking::where('booking_group_id', $groupId)->where('payment_status', 'paid')->count());
        $this->assertDatabaseHas('finance_journal_entries', ['entry_number' => 'SALE-booking-'.$b1->id]);
        $this->assertDatabaseHas('finance_journal_entries', ['entry_number' => 'SALE-booking-'.$b2->id]);
    }

    public function test_webhook_charge_failed_marks_booking_failed(): void
    {
        $booking = $this->makePaidBooking('BK'.date('y').'WH03', 20000);
        $booking->update(['payment_status' => 'pending']);

        $event = [
            'event' => 'charge.failed',
            'data' => ['reference' => $booking->booking_reference, 'gateway_response' => 'Insufficient funds'],
        ];

        $response = $this->postJson('/paystack/webhook', $event, $this->signedWebhook($event));

        $response->assertStatus(200);
        $this->assertDatabaseHas('bookings', [
            'booking_reference' => $booking->booking_reference,
            'payment_status' => 'failed',
        ]);
    }

    public function test_webhook_invalid_signature_is_rejected(): void
    {
        $event = ['event' => 'charge.success', 'data' => ['reference' => 'BKX']];

        $response = $this->postJson('/paystack/webhook', $event, [
            'x-paystack-signature' => 'bad-signature',
        ]);

        $response->assertStatus(401);
    }

    public function test_webhook_refund_processed_updates_refund_and_booking(): void
    {
        $booking = $this->makePaidBooking('BK'.date('y').'WH04', 20000);

        Refund::create([
            'gateway' => 'paystack',
            'gateway_reference' => 'RFR_WH04',
            'transaction_reference' => $booking->booking_reference,
            'refundable_type' => Booking::class,
            'refundable_id' => $booking->id,
            'amount' => 20000,
            'currency' => 'NGN',
            'status' => 'pending',
            'reason' => 'Test',
        ]);

        $event = [
            'event' => 'refund.processed',
            'data' => [
                'reference' => 'RFR_WH04',
                'transaction' => ['reference' => $booking->booking_reference],
                'amount' => 2000000,
            ],
        ];

        $response = $this->postJson('/paystack/webhook', $event, $this->signedWebhook($event));

        $response->assertStatus(200);
        $this->assertEquals('processed', Refund::where('gateway_reference', 'RFR_WH04')->first()->status);
        $this->assertDatabaseHas('bookings', [
            'booking_reference' => $booking->booking_reference,
            'payment_status' => 'refunded',
            'amount_paid' => 0,
        ]);
    }

    // ─────────────────────────────────────────────────────────────
    // SCENARIO GROUP 4 — Gateway selection via DB configuration
    // ─────────────────────────────────────────────────────────────

    public function test_booking_uses_gateway_configured_in_database_when_present(): void
    {
        Mail::fake();

        // Configure the gateway via the admin-configured payment_gateways table.
        PaymentGateway::create([
            'code' => 'paystack',
            'name' => 'Paystack (Live)',
            'driver' => 'paystack',
            'is_active' => true,
            'is_default' => true,
            'credentials' => ['secret' => $this->secret, 'public' => 'pk_test_x'],
        ]);

        $roomType = $this->makeAvailableRoomType();

        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => ['authorization_url' => 'https://checkout.paystack.com/db123'],
            ], 200),
        ]);

        $response = $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id, 'paystack'));

        $response->assertRedirect('https://checkout.paystack.com/db123');
        Http::assertSent(fn ($r) => str_contains($r->url(), 'api.paystack.co/transaction/initialize'));
    }

    // ─────────────────────────────────────────────────────────────
    // SCENARIO GROUP 5 — Pay Now (re-initialize payment from confirmation)
    // ─────────────────────────────────────────────────────────────

    private function makePendingBooking(string $reference, float $amount = 20000, ?string $group = null, ?RoomType $roomType = null): Booking
    {
        $roomType = $roomType ?? $this->makeAvailableRoomType($amount);

        return Booking::create([
            'booking_reference' => $reference,
            'booking_group_id' => $group,
            'room_type_id' => $roomType->id,
            'source' => 'website',
            'guest_name' => 'Web Guest',
            'guest_email' => 'webguest@example.com',
            'guest_phone' => '08012345678',
            'check_in_date' => now()->addDays(1),
            'check_out_date' => now()->addDays(2),
            'adults' => 2,
            'total_amount' => $amount,
            'amount_paid' => 0,
            'payment_status' => 'pending',
            'status' => 'pending',
            'payment_method' => 'paystack',
        ]);
    }

    public function test_pay_now_reinitializes_paystack_for_pending_single_booking(): void
    {
        Mail::fake();
        $roomType = $this->makeAvailableRoomType();

        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => ['authorization_url' => 'https://checkout.paystack.com/repay123'],
            ], 200),
        ]);

        // Booking created via storeBooking is pending and just_booked_ref is set
        // even on the paystack path (abandoned-payment recovery).
        $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id, 'paystack'));
        $booking = Booking::where('guest_email', 'guest@example.com')->first();

        $this->assertSame('pending', $booking->payment_status);
        $this->assertTrue(session()->has('just_booked_ref'));

        $response = $this->post(route('website.booking.pay', $booking->booking_reference));

        $response->assertRedirect('https://checkout.paystack.com/repay123');
        Http::assertSent(fn ($r) => str_contains($r->url(), 'api.paystack.co/transaction/initialize'));
        // Two init calls: the original storeBooking + the re-init from payNow.
        // (A NumVerify phone-validation call is also faked during storeBooking.)
        $this->assertSame(
            2,
            Http::recorded()->filter(fn ($r) => str_contains($r[0]->url(), 'api.paystack.co/transaction/initialize'))->count()
        );
        Http::assertSent(function ($request) use ($booking) {
            return str_contains($request->url(), 'api.paystack.co/transaction/initialize')
                && $request->data()['reference'] === $booking->booking_reference
                && (int) $request->data()['amount'] === 2000000;
        });
    }

    public function test_pay_now_reinitializes_paystack_for_pending_grouped_booking(): void
    {
        Mail::fake();
        $roomType = $this->makeAvailableRoomType();
        $group = 'GRP-'.strtoupper(substr(uniqid(), -6));
        $primary = $this->makePendingBooking('BK-REPAY-'.substr(uniqid(), -5), 40000, $group, $roomType);
        $this->makePendingBooking('BK-REPAY-'.substr(uniqid(), -5), 30000, $group, $roomType);

        session(['just_booked_ref' => $primary->booking_reference]);

        Http::fake([
            'api.paystack.co/transaction/initialize' => Http::response([
                'status' => true,
                'data' => ['authorization_url' => 'https://checkout.paystack.com/grouprepay123'],
            ], 200),
        ]);

        $response = $this->post(route('website.booking.pay', $primary->booking_reference));

        $response->assertRedirect('https://checkout.paystack.com/grouprepay123');
        Http::assertSent(function ($request) use ($group) {
            return str_contains($request->url(), 'api.paystack.co/transaction/initialize')
                && $request->data()['reference'] === $group
                && (int) $request->data()['amount'] === 7000000;
        });
    }

    public function test_pay_now_denies_unauthed_guest(): void
    {
        Mail::fake();
        $booking = $this->makePendingBooking('BK-DENY-'.substr(uniqid(), -5));

        $this->post(route('website.booking.pay', $booking->booking_reference))
            ->assertForbidden();

        Http::assertNothingSent();
    }

    public function test_pay_now_redirects_when_booking_already_paid(): void
    {
        Mail::fake();
        $booking = $this->makePaidBooking('BK-PAID-'.substr(uniqid(), -5));

        session(['just_booked_ref' => $booking->booking_reference]);

        $this->post(route('website.booking.pay', $booking->booking_reference))
            ->assertRedirect(route('website.booking.confirmation', $booking->booking_reference))
            ->assertSessionHas('info');

        Http::assertNothingSent();
    }

    public function test_confirmation_page_shows_complete_payment_button_for_pending_paystack_booking(): void
    {
        Mail::fake();
        $booking = $this->makePendingBooking('BK-PEND-'.substr(uniqid(), -5));

        session(['just_booked_ref' => $booking->booking_reference]);

        $this->get(route('website.booking.confirmation', $booking->booking_reference))
            ->assertOk()
            ->assertSee('Complete Payment');
    }

    public function test_confirmation_page_hides_complete_payment_button_when_paid(): void
    {
        Mail::fake();
        $booking = $this->makePaidBooking('BK-DONE-'.substr(uniqid(), -5));

        session(['just_booked_ref' => $booking->booking_reference]);

        $this->get(route('website.booking.confirmation', $booking->booking_reference))
            ->assertOk()
            ->assertDontSee('Complete Payment');
    }
}
