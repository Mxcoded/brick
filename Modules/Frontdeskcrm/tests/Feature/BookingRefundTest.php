<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Modules\Website\Models\Booking;
use Modules\Website\Models\RoomType;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class BookingRefundTest extends TestCase
{
    use DatabaseTransactions;

    private string $secret = 'sk_test_refund_secret';

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        config(['services.paystack.secret' => $this->secret]);

        $permission = Permission::firstOrCreate([
            'name' => 'access_frontdesk_dashboard',
            'guard_name' => 'web',
        ]);
        $this->user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->user->givePermissionTo($permission);
        $this->actingAs($this->user);
    }

    private function makePaidBooking(float $amount = 15000.00): Booking
    {
        $roomType = RoomType::create([
            'name' => 'Frontdesk Refund Room',
            'slug' => 'fd-refund-room-'.uniqid(),
            'price' => $amount,
            'capacity' => 2,
            'is_active' => true,
        ]);

        return Booking::create([
            'guest_name' => 'FD Refund Guest',
            'guest_email' => 'fdrefund@example.com',
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

    public function test_staff_can_initiate_refund_for_paid_booking(): void
    {
        Http::fake([
            'api.paystack.co/refund' => Http::response([
                'status' => true,
                'data' => ['reference' => 'RFR_fd123', 'status' => 'pending', 'amount' => 1500000],
            ], 200),
        ]);

        $booking = $this->makePaidBooking();

        $response = $this->post(route('frontdesk.bookings.refund', $booking->booking_reference), [
            'reason' => 'Guest cancelled',
        ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('refunds', [
            'gateway_reference' => 'RFR_fd123',
            'transaction_reference' => $booking->booking_reference,
            'status' => 'pending',
        ]);

        $this->assertEquals('refund_pending', $booking->fresh()->payment_status);
    }

    public function test_refund_is_rejected_for_unpaid_booking(): void
    {
        $booking = $this->makePaidBooking();
        $booking->update(['payment_status' => 'pending']);

        $response = $this->post(route('frontdesk.bookings.refund', $booking->booking_reference), []);

        $response->assertRedirect();
        $response->assertSessionHas('error');
        $this->assertDatabaseMissing('refunds', [
            'transaction_reference' => $booking->booking_reference,
        ]);
    }
}
