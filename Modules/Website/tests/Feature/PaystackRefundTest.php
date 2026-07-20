<?php

namespace Modules\Website\Tests\Feature;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Modules\Website\Models\Booking;
use Modules\Website\Models\Refund;
use Modules\Website\Models\RoomType;
use Modules\Website\Services\PaymentGatewayManager;
use Tests\TestCase;

class PaystackRefundTest extends TestCase
{
    use DatabaseTransactions;

    private string $secret = 'sk_test_refund_secret';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        config(['services.paystack.secret' => $this->secret]);
    }

    private function makePaidBooking(float $amount = 20000.00): Booking
    {
        $roomType = RoomType::create([
            'name' => 'Refund Suite',
            'slug' => 'refund-suite-'.uniqid(),
            'price' => $amount,
            'capacity' => 2,
            'is_active' => true,
        ]);

        return Booking::create([
            'guest_name' => 'Refund Guest',
            'guest_email' => 'refund@example.com',
            'guest_phone' => '08012345678',
            'guest_gender' => 'male',
            'guest_address' => '123 Test Avenue',
            'guest_nationality' => 'Nigerian',
            'guest_id_type' => 'NIN',
            'guest_id_number' => '12345678901',
            'guest_dob' => '1990-01-01',
            'adults' => 2,
            'children' => 0,
            'payment_method' => 'paystack',
            'room_type_id' => $roomType->id,
            'check_in_date' => now()->addDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(2)->format('Y-m-d'),
            'total_amount' => $amount,
            'amount_paid' => $amount,
            'payment_status' => 'paid',
            'status' => 'confirmed',
        ]);
    }

    public function test_service_refund_calls_paystack_and_returns_response(): void
    {
        Http::fake([
            'api.paystack.co/refund' => Http::response([
                'status' => true,
                'data' => ['reference' => 'RFR_abc123', 'status' => 'pending', 'amount' => 2000000],
            ], 200),
        ]);

        $booking = $this->makePaidBooking();

        $result = app(PaymentGatewayManager::class)->driver()->refund($booking->booking_reference, (float) $booking->amount_paid, 'Test refund');

        $this->assertTrue($result['status']);
        $this->assertEquals('RFR_abc123', $result['data']['reference']);

        Http::assertSent(function ($request) {
            return $request->url() === 'https://api.paystack.co/refund'
                && $request->data()['transaction'] === Booking::first()->booking_reference;
        });
    }

    public function test_webhook_rejects_invalid_signature(): void
    {
        $payload = json_encode(['event' => 'refund.processed', 'data' => ['reference' => 'RFR_x']]);

        $response = $this->postJson('/paystack/webhook', ['event' => 'refund.processed'], [
            'x-paystack-signature' => 'invalid-signature',
        ]);

        $response->assertStatus(401);
    }

    public function test_webhook_refund_processed_updates_refund_and_booking(): void
    {
        $booking = $this->makePaidBooking();

        $refund = Refund::create([
            'gateway' => 'paystack',
            'gateway_reference' => 'RFR_abc123',
            'transaction_reference' => $booking->booking_reference,
            'refundable_type' => Booking::class,
            'refundable_id' => $booking->id,
            'amount' => $booking->amount_paid,
            'currency' => 'NGN',
            'status' => 'pending',
            'reason' => 'Test',
        ]);

        $event = [
            'event' => 'refund.processed',
            'data' => [
                'reference' => 'RFR_abc123',
                'transaction' => ['reference' => $booking->booking_reference],
                'amount' => (int) ($booking->amount_paid * 100),
            ],
        ];

        $signature = hash_hmac('sha512', json_encode($event), $this->secret);

        $response = $this->postJson('/paystack/webhook', $event, [
            'x-paystack-signature' => $signature,
        ]);

        $response->assertStatus(200);

        $this->assertEquals('processed', $refund->fresh()->status);
        $this->assertEquals('refunded', $booking->fresh()->payment_status);
        $this->assertEquals(0, (float) $booking->fresh()->amount_paid);
    }

    public function test_webhook_refund_failed_reverts_booking_to_paid(): void
    {
        $booking = $this->makePaidBooking();
        $booking->update(['payment_status' => 'refund_pending']);

        $refund = Refund::create([
            'gateway' => 'paystack',
            'gateway_reference' => 'RFR_failed',
            'transaction_reference' => $booking->booking_reference,
            'refundable_type' => Booking::class,
            'refundable_id' => $booking->id,
            'amount' => $booking->amount_paid,
            'currency' => 'NGN',
            'status' => 'pending',
            'reason' => 'Test',
        ]);

        $event = [
            'event' => 'refund.failed',
            'data' => [
                'reference' => 'RFR_failed',
                'transaction' => ['reference' => $booking->booking_reference],
                'amount' => (int) ($booking->amount_paid * 100),
            ],
        ];

        $signature = hash_hmac('sha512', json_encode($event), $this->secret);

        $this->postJson('/paystack/webhook', $event, ['x-paystack-signature' => $signature])
            ->assertStatus(200);

        $this->assertEquals('failed', $refund->fresh()->status);
        $this->assertEquals('paid', $booking->fresh()->payment_status);
    }
}
