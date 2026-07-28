<?php

namespace Modules\Website\Tests\Feature;

use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Website\Models\Booking;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Tests\TestCase;

class BookingFlowTest extends TestCase
{
    use DatabaseTransactions;

    private RoomType $roomType;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $this->roomType = RoomType::create([
            'name' => 'Deluxe Suite',
            'slug' => 'deluxe-suite-'.uniqid(),
            'price' => 25000,
            'capacity' => 2,
            'is_active' => true,
        ]);

        RoomUnit::create([
            'room_type_id' => $this->roomType->id,
            'room_number' => '101',
            'floor' => 1,
            'status' => 'available',
        ]);
    }

    public function test_booking_page_loads_with_room_type_id()
    {
        $response = $this->get(route('website.booking', ['room_type_id' => $this->roomType->id]));

        $response->assertOk();
        $response->assertSee('Complete Your Reservation');
        $response->assertSee($this->roomType->name);
    }

    public function test_booking_page_redirects_without_room_type_or_cart()
    {
        $response = $this->get(route('website.booking'));

        $response->assertRedirect(route('website.book'));
        $response->assertSessionHas('info', 'Please select your rooms first.');
    }

    public function test_booking_form_submission_pay_on_arrival()
    {
        $response = $this->post(route('website.booking.store'), [
            'room_type_id' => $this->roomType->id,
            'check_in_date' => now()->addDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(3)->format('Y-m-d'),
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
            'guest_phone' => '08012345678',
            'guest_gender' => 'male',
            'guest_address' => '123 Test Street',
            'guest_nationality' => 'Nigerian',
            'guest_id_type' => 'NIN',
            'guest_id_number' => '12345678901',
            'guest_dob' => '1990-05-15',
            'adults' => 2,
            'children' => 0,
            'payment_method' => 'pay_on_arrival',
        ]);

        $response->assertSessionHas('success', 'Booking Reserved! Please pay upon arrival.');

        $this->assertDatabaseHas('bookings', [
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
            'room_type_id' => $this->roomType->id,
            'payment_method' => 'pay_on_arrival',
        ]);
    }

    public function test_booking_validation_fails_for_missing_fields()
    {
        $response = $this->post(route('website.booking.store'), [
            'room_type_id' => $this->roomType->id,
            'check_in_date' => now()->addDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(3)->format('Y-m-d'),
            'guest_name' => '',
            'guest_email' => 'not-an-email',
        ]);

        $response->assertSessionHasErrors([
            'guest_name',
            'guest_email',
            'guest_phone',
            'guest_gender',
            'guest_address',
            'guest_nationality',
            'guest_id_type',
            'guest_id_number',
            'adults',
            'payment_method',
        ]);
    }

    public function test_booking_validates_phone_number_format()
    {
        $response = $this->post(route('website.booking.store'), [
            'room_type_id' => $this->roomType->id,
            'check_in_date' => now()->addDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(3)->format('Y-m-d'),
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
            'guest_phone' => 'invalid',
            'guest_gender' => 'male',
            'guest_address' => '123 Street',
            'guest_nationality' => 'Nigerian',
            'guest_id_type' => 'NIN',
            'guest_id_number' => '12345678901',
            'adults' => 1,
            'payment_method' => 'pay_on_arrival',
        ]);

        $response->assertSessionHasErrors(['guest_phone']);
    }

    public function test_booking_nin_validation()
    {
        $response = $this->post(route('website.booking.store'), [
            'room_type_id' => $this->roomType->id,
            'check_in_date' => now()->addDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(3)->format('Y-m-d'),
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
            'guest_phone' => '08012345678',
            'guest_gender' => 'male',
            'guest_address' => '123 Street',
            'guest_nationality' => 'Nigerian',
            'guest_id_type' => 'NIN',
            'guest_id_number' => '12345',
            'adults' => 1,
            'payment_method' => 'pay_on_arrival',
        ]);

        $response->assertSessionHasErrors(['guest_id_number']);
        $response->assertSessionHas('errors');
        $errors = session('errors');
        $this->assertStringContainsString(
            'NIN must be exactly 11 digits',
            $errors->first('guest_id_number')
        );
    }

    public function test_confirmation_page_shows_booking_details()
    {
        $booking = Booking::create([
            'booking_reference' => 'BK'.now()->year.strtoupper(substr(uniqid(), -4)),
            'room_type_id' => $this->roomType->id,
            'source' => 'website',
            'guest_name' => 'John Doe',
            'guest_email' => 'john@example.com',
            'guest_phone' => '08012345678',
            'check_in_date' => now()->addDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(3)->format('Y-m-d'),
            'adults' => 2,
            'children' => 0,
            'total_amount' => 50000,
            'payment_status' => 'pending',
            'status' => 'pending',
            'payment_method' => 'pay_on_arrival',
        ]);

        session()->put('just_booked_ref', $booking->booking_reference);

        $response = $this->get(route('website.booking.confirmation', $booking->booking_reference));

        $response->assertOk();
        $response->assertSee($booking->booking_reference);
        $response->assertSee('John Doe');
        $response->assertSee('Booking Details');
    }

    public function test_confirmation_page_denies_unauthorized_access()
    {
        $booking = Booking::create([
            'booking_reference' => 'BK'.now()->year.strtoupper(substr(uniqid(), -4)),
            'room_type_id' => $this->roomType->id,
            'source' => 'website',
            'guest_name' => 'Jane Doe',
            'guest_email' => 'jane@example.com',
            'guest_phone' => '08087654321',
            'check_in_date' => now()->addDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(3)->format('Y-m-d'),
            'adults' => 1,
            'children' => 0,
            'total_amount' => 50000,
            'payment_status' => 'pending',
            'status' => 'pending',
            'payment_method' => 'pay_on_arrival',
        ]);

        $response = $this->get(route('website.booking.confirmation', $booking->booking_reference));

        $response->assertRedirect(route('website.home'));

        $response->assertRedirect(route('website.home'));
    }

    public function test_booking_progress_partial_renders_correct_step()
    {
        $view = $this->view('website::partials.booking-progress', ['step' => 3]);

        $view->assertSee('Guest Details');
        $view->assertSee('active');
    }

    public function test_booking_progress_all_steps_appear()
    {
        $view = $this->view('website::partials.booking-progress', ['step' => 1]);

        $view->assertSee('Select Dates');
        $view->assertSee('Choose Room');
        $view->assertSee('Guest Details');
        $view->assertSee('Confirmation');
    }

    public function test_store_booking_creates_guest_profile()
    {
        $this->post(route('website.booking.store'), [
            'room_type_id' => $this->roomType->id,
            'check_in_date' => now()->addDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(3)->format('Y-m-d'),
            'guest_name' => 'Profile Test',
            'guest_email' => 'profiletest@example.com',
            'guest_phone' => '08098765432',
            'guest_gender' => 'female',
            'guest_address' => '456 Profile Ave',
            'guest_nationality' => 'Nigerian',
            'guest_id_type' => 'NIN',
            'guest_id_number' => '98765432101',
            'guest_dob' => '1992-08-20',
            'adults' => 1,
            'payment_method' => 'pay_on_arrival',
        ]);

        $this->assertDatabaseHas('guests', [
            'email' => 'profiletest@example.com',
            'full_name' => 'Profile Test',
            'identification_number' => '98765432101',
        ]);
    }

    public function test_booking_with_check_in_date_before_today_fails_validation()
    {
        $response = $this->post(route('website.booking.store'), [
            'room_type_id' => $this->roomType->id,
            'check_in_date' => now()->subDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(2)->format('Y-m-d'),
            'guest_name' => 'Past Date',
            'guest_email' => 'past@example.com',
            'guest_phone' => '08011111111',
            'guest_gender' => 'male',
            'guest_address' => '123 Street',
            'guest_nationality' => 'Nigerian',
            'guest_id_type' => 'NIN',
            'guest_id_number' => '12345678901',
            'adults' => 1,
            'payment_method' => 'pay_on_arrival',
        ]);

        $response->assertSessionHasErrors(['check_in_date']);
    }

    public function test_booking_with_checkout_before_checkin_fails()
    {
        $response = $this->post(route('website.booking.store'), [
            'room_type_id' => $this->roomType->id,
            'check_in_date' => now()->addDays(3)->format('Y-m-d'),
            'check_out_date' => now()->addDays(1)->format('Y-m-d'),
            'guest_name' => 'Bad Dates',
            'guest_email' => 'baddates@example.com',
            'guest_phone' => '08022222222',
            'guest_gender' => 'male',
            'guest_address' => '123 Street',
            'guest_nationality' => 'Nigerian',
            'guest_id_type' => 'NIN',
            'guest_id_number' => '12345678901',
            'adults' => 1,
            'payment_method' => 'pay_on_arrival',
        ]);

        $response->assertSessionHasErrors(['check_out_date']);
    }
}
