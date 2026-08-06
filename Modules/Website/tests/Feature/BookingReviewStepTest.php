<?php

namespace Modules\Website\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Modules\Website\Services\BookingCartService;
use Tests\TestCase;

class BookingReviewStepTest extends TestCase
{
    use DatabaseTransactions;

    private RoomType $roomType;

    protected function setUp(): void
    {
        parent::setUp();

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

    public function test_booking_page_renders_review_step_before_payment(): void
    {
        $response = $this->get(route('website.booking', ['room_type_id' => $this->roomType->id]));

        $response->assertOk();
        $response->assertSee('Review Your Booking');
        $response->assertSee('id="rvGrandTotal"', false);
        $response->assertSee('id="rvRoom"', false);
        // Non-cart flow: review is step 5, payment is step 6.
        $response->assertSee('Step 5');
        $response->assertSee('Step 6');
    }

    public function test_cart_flow_booking_page_renders_review_step(): void
    {
        $this->withSession([
            BookingCartService::SESSION_KEY => [
                'check_in' => '2026-08-10',
                'check_out' => '2026-08-13',
                'nights' => 3,
                'items' => [
                    $this->roomType->id => [
                        'room_type_id' => $this->roomType->id,
                        'room_type_name' => $this->roomType->name,
                        'quantity' => 2,
                        'price_per_night' => 25000,
                        'base_total' => 150000,
                        'guest_fee_per_night' => 0,
                        'guest_fee_total' => 0,
                        'total_rate' => 150000,
                        'rate_code_id' => null,
                        'capacity' => 2,
                        'adults' => 1,
                        'children' => 0,
                        'image_url' => null,
                        'nights' => 3,
                        'subtotal' => 300000,
                    ],
                ],
            ],
        ]);

        $response = $this->get(route('website.booking'));

        $response->assertOk();
        $response->assertSee('Review Your Booking');
        // Cart flow: review is step 4, payment is step 5.
        $response->assertSee('Step 4');
        $response->assertSee('Step 5');
        // Cart room line is server-rendered into the review panel.
        $response->assertSee('id="rvRoom">Deluxe Suite × 2', false);
        $response->assertSee('id="rvBaseTotal">₦150,000.00', false);
        $response->assertSee('id="rvGrandTotal">₦300,000.00', false);
    }

    public function test_review_step_server_renders_price_summary(): void
    {
        $response = $this->get(route('website.booking', ['room_type_id' => $this->roomType->id]));

        $response->assertOk();
        // Single night (no dates passed) at ₦25,000.
        $response->assertSee('id="rvBaseTotal">₦25,000.00', false);
        $response->assertSee('id="rvGuestFee">Included', false);
        $response->assertSee('id="rvGrandTotal">₦25,000.00', false);
    }

    public function test_review_step_echoes_draft_guest_details(): void
    {
        $this->withSession([
            'booking_draft' => [
                'room_type_id' => $this->roomType->id,
                'guest_name' => 'John Doe',
                'adults' => 2,
                'children' => 1,
                'guest_id_type' => 'International Passport',
                'guest_id_number' => 'A123456',
                'special_requests' => 'Late check-in, Extra pillows',
                'payment_method' => 'pay_on_arrival',
            ],
        ]);

        $response = $this->get(route('website.booking'));

        $response->assertOk();
        $response->assertSee('id="rvGuestName">John Doe', false);
        $response->assertSee('id="rvGuests">2 Adults, 1 Child', false);
        $response->assertSee('id="rvId">International Passport · A123456', false);
        $response->assertSee('id="rvRequests">Late check-in, Extra pillows', false);
        $response->assertSee('id="rvPayment">Pay at Hotel', false);
    }

    public function test_review_step_edit_links_target_existing_steps(): void
    {
        $response = $this->get(route('website.booking', ['room_type_id' => $this->roomType->id]));

        $response->assertOk();
        // Stay & Room -> step 1, Guest -> step 2, ID -> step 3,
        // Requests -> step 4, Payment -> step 5 (non-cart flow).
        foreach ([1, 2, 3, 4, 5] as $step) {
            $response->assertSee('data-jump-step="'.$step.'"', false);
        }
    }

    public function test_progress_indicator_includes_review_step(): void
    {
        $response = $this->get(route('website.booking', ['room_type_id' => $this->roomType->id]));

        $response->assertOk();
        $response->assertSee('<div class="step-label">Review</div>', false);

        $this->withSession([
            BookingCartService::SESSION_KEY => [
                'check_in' => '2026-08-10',
                'check_out' => '2026-08-13',
                'nights' => 3,
                'items' => [
                    $this->roomType->id => [
                        'room_type_id' => $this->roomType->id,
                        'room_type_name' => $this->roomType->name,
                        'quantity' => 1,
                        'price_per_night' => 25000,
                        'base_total' => 75000,
                        'guest_fee_per_night' => 0,
                        'guest_fee_total' => 0,
                        'total_rate' => 75000,
                        'rate_code_id' => null,
                        'capacity' => 2,
                        'adults' => 1,
                        'children' => 0,
                        'image_url' => null,
                        'nights' => 3,
                        'subtotal' => 75000,
                    ],
                ],
            ],
        ]);

        $cartResponse = $this->get(route('website.booking'));

        $cartResponse->assertOk();
        $cartResponse->assertSee('<div class="step-label">Review</div>', false);
    }
}
