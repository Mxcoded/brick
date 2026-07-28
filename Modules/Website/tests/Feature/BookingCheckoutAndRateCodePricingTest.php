<?php

namespace Modules\Website\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use Modules\Frontdeskcrm\Models\RateCalendar;
use Modules\Frontdeskcrm\Models\RateCode;
use Modules\Frontdeskcrm\Models\Season;
use Modules\Website\Emails\BookingConfirmation;
use Modules\Website\Models\Booking;
use Modules\Website\Models\PaymentGateway;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Tests\TestCase;

class BookingCheckoutAndRateCodePricingTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        config(['mail.reservations_email' => 'rsv@brickspoint.com']);
        config(['services.paystack.secret' => 'sk_test_fake_key_for_tests']);

        PaymentGateway::query()->delete();
    }

    // ─────────────────────────────────────────────────────────────
    // Helpers
    // ─────────────────────────────────────────────────────────────

    private function makeRoomType(float $price, ?int $rateCodeId = null): RoomType
    {
        $roomType = RoomType::create([
            'name' => 'Deluxe Suite '.uniqid(),
            'slug' => 'deluxe-suite-'.uniqid(),
            'price' => $price,
            'rate_code_id' => $rateCodeId,
            'capacity' => 3,
            'base_occupancy' => 2,
            'extra_adult_fee' => 5000,
            'extra_child_fee' => 2500,
            'is_active' => true,
        ]);

        RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => (string) rand(100, 999),
            'floor' => 1,
            'status' => 'available',
        ]);

        return $roomType;
    }

    private function makeRateCode(float $defaultRate): RateCode
    {
        return RateCode::create([
            'code' => 'BAR-'.strtoupper(uniqid()),
            'name' => 'Best Available Rate',
            'default_rate' => $defaultRate,
            'currency' => 'NGN',
            'min_los' => 1,
            'max_los' => null,
            'closed_to_arrival' => false,
            'closed_to_departure' => false,
            'apply_weekdays' => true,
            'apply_weekends' => true,
            'is_active' => true,
        ]);
    }

    private function bookingPayload(int $roomTypeId, string $method = 'pay_on_arrival', array $overrides = []): array
    {
        return array_merge([
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
            'check_in_date' => now()->addDays(5)->format('Y-m-d'),
            'check_out_date' => now()->addDays(7)->format('Y-m-d'),
            'special_requests' => null,
        ], $overrides);
    }

    // ─────────────────────────────────────────────────────────────
    // Group 1 — Full Booking-to-Checkout Flow
    // ─────────────────────────────────────────────────────────────

    public function test_full_booking_flow_pay_on_arrival_creates_booking_and_redirects_to_confirmation()
    {
        Mail::fake();

        $roomType = $this->makeRoomType(25000);

        $response = $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id, 'pay_on_arrival'));

        $response->assertRedirect();
        $this->assertDatabaseHas('bookings', [
            'room_type_id' => $roomType->id,
            'guest_email' => 'guest@example.com',
            'payment_method' => 'pay_on_arrival',
            'payment_status' => 'pending',
            'status' => 'pending',
        ]);

        $booking = Booking::where('guest_email', 'guest@example.com')->first();
        $this->assertNotNull($booking->booking_reference);
        $this->assertNotNull($booking->guest_profile_id);

        $this->assertDatabaseHas('guests', [
            'email' => 'guest@example.com',
            'full_name' => 'Test Guest',
        ]);

        Mail::assertSent(BookingConfirmation::class, 2);
    }

    public function test_full_booking_flow_paystack_redirects_to_payment_gateway()
    {
        $roomType = $this->makeRoomType(25000);

        Http::fake([
            'api.paystack.co/*' => [
                'status' => true,
                'data' => [
                    'authorization_url' => 'https://checkout.paystack.com/test',
                    'access_code' => 'test_access_code',
                ],
            ],
        ]);

        $response = $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id, 'paystack'));

        $response->assertRedirect('https://checkout.paystack.com/test');

        $this->assertDatabaseHas('bookings', [
            'room_type_id' => $roomType->id,
            'payment_method' => 'paystack',
            'payment_status' => 'pending',
        ]);
    }

    public function test_paystack_callback_marks_booking_paid_and_confirms()
    {
        Mail::fake();

        $roomType = $this->makeRoomType(30000);

        $booking = Booking::create([
            'booking_reference' => 'BK'.now()->year.'CALLBACK1',
            'room_type_id' => $roomType->id,
            'source' => 'website',
            'guest_name' => 'Callback Test',
            'guest_email' => 'callback@example.com',
            'guest_phone' => '08012345678',
            'check_in_date' => now()->addDays(5),
            'check_out_date' => now()->addDays(7),
            'adults' => 2,
            'total_amount' => 60000,
            'payment_status' => 'pending',
            'status' => 'pending',
            'payment_method' => 'paystack',
        ]);

        session()->put('just_booked_ref', $booking->booking_reference);

        Http::fake([
            'api.paystack.co/transaction/verify/*' => [
                'status' => true,
                'data' => ['status' => 'success', 'amount' => 60000],
            ],
        ]);

        $response = $this->get(route('website.payment.callback', ['reference' => $booking->booking_reference]));

        $response->assertRedirect();

        $booking->refresh();
        $this->assertEquals('paid', $booking->payment_status);
        $this->assertEquals('confirmed', $booking->status);
        $this->assertEquals(60000, $booking->amount_paid);
    }

    public function test_booking_confirmation_page_displays_correct_details()
    {
        Mail::fake();

        $roomType = $this->makeRoomType(20000);

        $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id, 'pay_on_arrival'));

        $booking = Booking::where('guest_email', 'guest@example.com')->first();
        $this->assertNotNull($booking);

        $response = $this->get(route('website.booking.confirmation', $booking->booking_reference));

        $response->assertOk();
        $response->assertSee($booking->booking_reference);
        $response->assertSee('Test Guest');
    }

    public function test_booking_page_renders_all_form_sections()
    {
        $roomType = $this->makeRoomType(25000);

        $response = $this->get(route('website.booking', ['room_type_id' => $roomType->id]));

        $response->assertOk();
        $response->assertSee('Stay Dates');
        $response->assertSee('Guest Information');
        $response->assertSee('Identity Verification');
        $response->assertSee('Guests');
        $response->assertSee('Payment Method');
        $response->assertSee($roomType->name);
        $response->assertSee('Complete Your Reservation');
        $response->assertSee('Back to Rooms');
    }

    public function test_booking_page_shows_capacity_pill_with_room_selected()
    {
        $roomType = $this->makeRoomType(25000);

        $response = $this->get(route('website.booking', ['room_type_id' => $roomType->id]));

        $response->assertOk();
        $response->assertSee('capacityPill');
        $response->assertSee('Max '.$roomType->capacity.' guests');
    }

    public function test_booking_page_shows_empty_room_fallback_when_no_room_types()
    {
        $response = $this->get(route('website.booking'));

        $response->assertRedirect(route('website.book'));
    }

    public function test_booking_page_shows_date_chips_with_correct_nights()
    {
        $roomType = $this->makeRoomType(25000);

        $response = $this->get(route('website.booking', ['room_type_id' => $roomType->id]));

        $response->assertOk();
        $response->assertSee('data-nights="1"', false);
        $response->assertSee('data-nights="3"', false);
        $response->assertSee('data-nights="7"', false);
        $response->assertSee('3 Nights');
        $response->assertSee('1 Week');
    }

    public function test_booking_page_shows_request_chips_for_special_requests()
    {
        $roomType = $this->makeRoomType(25000);

        $response = $this->get(route('website.booking', ['room_type_id' => $roomType->id]));

        $response->assertOk();
        $response->assertSee('request-chip');
        $response->assertSee('Late check-in');
        $response->assertSee('Airport transfer');
        $response->assertSee('Anniversary setup');
    }

    public function test_booking_page_room_cards_are_keyboard_accessible()
    {
        $roomType = $this->makeRoomType(25000);

        $response = $this->get(route('website.booking', ['room_type_id' => $roomType->id]));

        $response->assertOk();
        $response->assertSee('role="button"', false);
        $response->assertSee('tabindex="0"', false);
        $response->assertSee('aria-label=', false);
    }

    public function test_booking_total_reflects_rate_code_price_not_flat_rate()
    {
        Mail::fake();

        $rateCode = $this->makeRateCode(42000);
        $roomType = $this->makeRoomType(25000, $rateCode->id);

        $checkIn = now()->addDays(5)->format('Y-m-d');
        $checkOut = now()->addDays(7)->format('Y-m-d');
        $nights = Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));

        $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id, 'pay_on_arrival', [
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
        ]));

        $booking = Booking::where('guest_email', 'guest@example.com')->first();
        $this->assertNotNull($booking);

        $expectedTotal = $rateCode->default_rate * $nights;
        $this->assertEquals($expectedTotal, (float) $booking->total_amount);
        $this->assertEquals($rateCode->id, $booking->rate_code_id);
        $this->assertNotEquals($roomType->price * $nights, (float) $booking->total_amount);
    }

    public function test_booking_falls_back_to_flat_rate_when_no_rate_code()
    {
        Mail::fake();

        $roomType = $this->makeRoomType(25000, null);

        $checkIn = now()->addDays(5)->format('Y-m-d');
        $checkOut = now()->addDays(7)->format('Y-m-d');
        $nights = Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));

        $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id, 'pay_on_arrival', [
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
        ]));

        $booking = Booking::where('guest_email', 'guest@example.com')->first();

        $expectedTotal = $roomType->price * $nights;
        $this->assertEquals($expectedTotal, (float) $booking->total_amount);
        $this->assertNull($booking->rate_code_id);
    }

    public function test_rate_calendar_override_takes_precedence_over_default_rate()
    {
        Mail::fake();

        $rateCode = $this->makeRateCode(42000);
        $roomType = $this->makeRoomType(25000, $rateCode->id);

        $checkIn = now()->addDays(5);
        $checkOut = now()->addDays(7);

        RateCalendar::create([
            'rate_code_id' => $rateCode->id,
            'date' => $checkIn,
            'rate' => 55000,
            'is_available' => true,
        ]);

        $nights = $checkIn->diffInDays($checkOut);

        $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id, 'pay_on_arrival', [
            'check_in_date' => $checkIn->format('Y-m-d'),
            'check_out_date' => $checkOut->format('Y-m-d'),
        ]));

        $booking = Booking::where('guest_email', 'guest@example.com')->first();

        $expectedTotal = (55000 + 42000) * 1;
        $this->assertEquals($expectedTotal, (float) $booking->total_amount);
        $this->assertEquals($rateCode->id, $booking->rate_code_id);
    }

    public function test_season_multiplier_applies_on_top_of_rate_code()
    {
        Mail::fake();

        $rateCode = $this->makeRateCode(40000);
        $roomType = $this->makeRoomType(25000, $rateCode->id);

        $checkIn = now()->addDays(5);
        $checkOut = now()->addDays(7);

        Season::updateOrCreate(
            ['code' => 'PEAK'],
            [
                'name' => 'Peak Season',
                'valid_from' => now()->subDays(10),
                'valid_to' => now()->addDays(30),
                'rate_multiplier' => 1.5,
                'is_active' => true,
            ]
        );

        $nights = $checkIn->diffInDays($checkOut);

        $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id, 'pay_on_arrival', [
            'check_in_date' => $checkIn->format('Y-m-d'),
            'check_out_date' => $checkOut->format('Y-m-d'),
        ]));

        $booking = Booking::where('guest_email', 'guest@example.com')->first();

        $expectedTotal = $rateCode->default_rate * 1.5 * $nights;
        $this->assertEqualsWithDelta($expectedTotal, (float) $booking->total_amount, 1);
        $this->assertEquals($rateCode->id, $booking->rate_code_id);
    }

    public function test_extra_guest_fees_added_on_top_of_rate_code_pricing()
    {
        Mail::fake();

        $rateCode = $this->makeRateCode(40000);
        $roomType = RoomType::create([
            'name' => 'Extra Guests Suite '.uniqid(),
            'slug' => 'extra-guests-suite-'.uniqid(),
            'price' => 25000,
            'rate_code_id' => $rateCode->id,
            'capacity' => 5,
            'base_occupancy' => 2,
            'extra_adult_fee' => 5000,
            'extra_child_fee' => 2500,
            'is_active' => true,
        ]);

        RoomUnit::create([
            'room_type_id' => $roomType->id,
            'room_number' => (string) rand(100, 999),
            'floor' => 1,
            'status' => 'available',
        ]);

        $checkIn = now()->addDays(5);
        $checkOut = now()->addDays(7);
        $nights = $checkIn->diffInDays($checkOut);

        $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id, 'pay_on_arrival', [
            'check_in_date' => $checkIn->format('Y-m-d'),
            'check_out_date' => $checkOut->format('Y-m-d'),
            'adults' => 3,
            'children' => 1,
        ]));

        $booking = Booking::where('guest_email', 'guest@example.com')->first();

        $baseRate = $rateCode->default_rate * $nights;
        $extraAdultFee = $roomType->extra_adult_fee * max(0, 3 - $roomType->base_occupancy) * $nights;
        $extraChildFee = $roomType->extra_child_fee * 1 * $nights;
        $expectedTotal = $baseRate + $extraAdultFee + $extraChildFee;

        $this->assertEqualsWithDelta($expectedTotal, (float) $booking->total_amount, 1);
        $this->assertEquals($rateCode->id, $booking->rate_code_id);
    }

    public function test_inactive_rate_code_falls_back_to_flat_rate()
    {
        Mail::fake();

        $rateCode = $this->makeRateCode(42000);
        $rateCode->update(['is_active' => false]);

        $roomType = $this->makeRoomType(25000, $rateCode->id);

        $checkIn = now()->addDays(5)->format('Y-m-d');
        $checkOut = now()->addDays(7)->format('Y-m-d');
        $nights = Carbon::parse($checkIn)->diffInDays(Carbon::parse($checkOut));

        $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id, 'pay_on_arrival', [
            'check_in_date' => $checkIn,
            'check_out_date' => $checkOut,
        ]));

        $booking = Booking::where('guest_email', 'guest@example.com')->first();

        $expectedTotal = $roomType->price * $nights;
        $this->assertEquals($expectedTotal, (float) $booking->total_amount);
        $this->assertNull($booking->rate_code_id);
    }

    public function test_booking_validation_requires_all_guest_fields()
    {
        $roomType = $this->makeRoomType(25000);

        $response = $this->post(route('website.booking.store'), [
            'room_type_id' => $roomType->id,
            'check_in_date' => now()->addDays(5)->format('Y-m-d'),
            'check_out_date' => now()->addDays(7)->format('Y-m-d'),
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

    public function test_booking_rejects_over_capacity_guests()
    {
        $roomType = $this->makeRoomType(25000);

        $response = $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id, 'pay_on_arrival', [
            'adults' => 5,
            'children' => 0,
        ]));

        $response->assertSessionHas('error');
        $this->assertStringContainsString('exceed', session('error'));
    }

    public function test_booking_rejects_invalid_payment_method()
    {
        $roomType = $this->makeRoomType(25000);

        $response = $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id, 'stripe'));

        $response->assertSessionHasErrors(['payment_method']);
    }

    public function test_booking_rejects_past_check_in_date()
    {
        $roomType = $this->makeRoomType(25000);

        $response = $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id, 'pay_on_arrival', [
            'check_in_date' => now()->subDays(1)->format('Y-m-d'),
            'check_out_date' => now()->addDays(2)->format('Y-m-d'),
        ]));

        $response->assertSessionHasErrors(['check_in_date']);
    }

    public function test_booking_rejects_checkout_before_checkin()
    {
        $roomType = $this->makeRoomType(25000);

        $response = $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id, 'pay_on_arrival', [
            'check_in_date' => now()->addDays(7)->format('Y-m-d'),
            'check_out_date' => now()->addDays(5)->format('Y-m-d'),
        ]));

        $response->assertSessionHasErrors(['check_out_date']);
    }

    public function test_special_requests_saved_on_booking()
    {
        Mail::fake();

        $roomType = $this->makeRoomType(25000);

        $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id, 'pay_on_arrival', [
            'special_requests' => 'Late check-in, extra pillows',
        ]));

        $booking = Booking::where('guest_email', 'guest@example.com')->first();
        $this->assertEquals('Late check-in, extra pillows', $booking->special_requests);
    }

    public function test_multi_night_stay_calculates_correct_total_with_rate_code()
    {
        Mail::fake();

        $rateCode = $this->makeRateCode(35000);
        $roomType = $this->makeRoomType(20000, $rateCode->id);

        $checkIn = now()->addDays(10);
        $checkOut = now()->addDays(15);
        $nights = $checkIn->diffInDays($checkOut);

        $this->post(route('website.booking.store'), $this->bookingPayload($roomType->id, 'pay_on_arrival', [
            'check_in_date' => $checkIn->format('Y-m-d'),
            'check_out_date' => $checkOut->format('Y-m-d'),
        ]));

        $booking = Booking::where('guest_email', 'guest@example.com')->first();

        $expectedTotal = $rateCode->default_rate * $nights;
        $this->assertEqualsWithDelta($expectedTotal, (float) $booking->total_amount, 1);
    }
}
