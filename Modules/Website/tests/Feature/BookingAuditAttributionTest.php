<?php

namespace Modules\Website\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Modules\Website\Models\Booking;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use OwenIt\Auditing\Models\Audit;
use Tests\TestCase;

/**
 * Guest (anonymous) website bookings previously recorded "System" as the actor
 * because no authenticated user was in scope. These tests confirm the audit
 * trail now attributes the booking to a matching registered guest, or tags it
 * with the guest email when no account exists.
 */
class BookingAuditAttributionTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware([ValidateCsrfToken::class]);
    }

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

    private function bookingPayload(int $roomTypeId, string $email = 'guest@example.com'): array
    {
        return [
            'guest_name' => 'Test Guest',
            'guest_email' => $email,
            'guest_phone' => '08012345678',
            'guest_gender' => 'male',
            'guest_address' => '123 Test Avenue',
            'guest_nationality' => 'Nigerian',
            'guest_id_type' => 'NIN',
            'guest_id_number' => '12345678901',
            'guest_dob' => '1990-01-01',
            'adults' => 2,
            'children' => 0,
            'payment_method' => 'pay_on_arrival',
            'room_type_id' => $roomTypeId,
            'check_in_date' => now()->addDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(2)->format('Y-m-d'),
            'special_requests' => null,
        ];
    }

    public function test_anonymous_booking_is_attributed_to_matching_registered_guest(): void
    {
        Mail::fake();

        $guest = User::factory()->create(['email' => 'guest@example.com']);
        $roomType = $this->makeAvailableRoomType();

        $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id));

        $booking = Booking::where('guest_email', 'guest@example.com')->latest()->firstOrFail();

        $audit = Audit::where('auditable_type', Booking::class)
            ->where('auditable_id', $booking->id)
            ->where('event', 'created')
            ->firstOrFail();

        $this->assertEquals($guest->id, $audit->user_id, 'Audit should attribute the booking to the matching registered guest.');
        $this->assertEquals(User::class, $audit->user_type);
    }

    public function test_anonymous_booking_with_new_guest_is_tagged_with_email(): void
    {
        Mail::fake();

        $roomType = $this->makeAvailableRoomType();
        $email = 'newcomer@example.com';

        $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id, $email));

        $booking = Booking::where('guest_email', $email)->latest()->firstOrFail();

        $audit = Audit::where('auditable_type', Booking::class)
            ->where('auditable_id', $booking->id)
            ->where('event', 'created')
            ->firstOrFail();

        $this->assertNull($audit->user_id, 'No registered user exists for this guest email.');
        $this->assertStringContainsString('guest:'.$email, $audit->tags ?? '', 'Audit should be tagged with the guest email.');
    }
}
