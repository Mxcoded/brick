<?php

namespace Modules\Website\Tests\Feature;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Mail;
use Modules\Website\Emails\BookingConfirmation;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Tests\TestCase;

class BookingEmailTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([
            ValidateCsrfToken::class,
        ]);

        // Ensure the reservations copy is dispatched to the requested inbox.
        config(['mail.reservations_email' => 'rsv@brickspoint.com']);
    }

    private function makeAvailableRoomType(): RoomType
    {
        $roomType = RoomType::create([
            'name' => 'Deluxe Room',
            'slug' => 'deluxe-room-'.uniqid(),
            'price' => 20000,
            'capacity' => 2,
            'is_active' => true,
        ]);

        RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => '101',
            'floor' => 1,
            'status' => 'available',
        ]);

        return $roomType;
    }

    private function bookingPayload(int $roomTypeId): array
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
            'payment_method' => 'pay_on_arrival',
            'room_type_id' => $roomTypeId,
            'check_in_date' => now()->addDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(2)->format('Y-m-d'),
            'special_requests' => null,
        ];
    }

    public function test_successful_booking_sends_confirmation_to_reservations_email()
    {
        Mail::fake();

        $roomType = $this->makeAvailableRoomType();

        $response = $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id));

        $response->assertRedirectContains('/booking/confirmation');
        $response->assertSessionHas('success');

        // Guest gets their own confirmation...
        Mail::assertSent(BookingConfirmation::class, function ($mail) {
            return $mail->hasTo('guest@example.com');
        });

        // ...and reservations receives a copy at the requested inbox.
        Mail::assertSent(BookingConfirmation::class, function ($mail) {
            return $mail->hasTo('rsv@brickspoint.com');
        });
    }

    public function test_successful_booking_is_persisted()
    {
        Mail::fake();

        $roomType = $this->makeAvailableRoomType();

        $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id));

        $this->assertDatabaseHas('bookings', [
            'guest_email' => 'guest@example.com',
            'guest_name' => 'Test Guest',
            'payment_status' => 'pending',
            'status' => 'pending',
            'payment_method' => 'pay_on_arrival',
        ]);
    }

    public function test_no_reservations_email_sent_when_config_not_set()
    {
        config(['mail.reservations_email' => null]);

        Mail::fake();

        $roomType = $this->makeAvailableRoomType();

        $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id));

        Mail::assertSent(BookingConfirmation::class, function ($mail) {
            return $mail->hasTo('guest@example.com');
        });

        Mail::assertNotSent(BookingConfirmation::class, function ($mail) {
            return $mail->hasTo('rsv@brickspoint.com');
        });
    }
}
