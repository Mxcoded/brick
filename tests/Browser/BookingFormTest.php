<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;

class BookingFormTest extends DuskTestCase
{
    protected RoomType $roomType;
    protected RoomUnit $roomUnit;

    protected function setUp(): void
    {
        parent::setUp();

        $uid = uniqid();

        $this->roomType = RoomType::create([
            'name' => 'Dusk Test Suite ' . $uid,
            'slug' => 'dusk-test-room-' . $uid,
            'price' => 50000.00,
            'capacity' => 2,
            'is_active' => true,
            'display_order' => 1,
        ]);

        $this->roomUnit = RoomUnit::create([
            'room_type_id' => $this->roomType->id,
            'room_number' => 'DN' . rand(100, 999),
            'floor' => 1,
            'status' => 'available',
        ]);
    }

    protected function tearDown(): void
    {
        if (isset($this->roomType)) {
            $this->roomType->units()->forceDelete();
            $this->roomType->bookings()->where('guest_email', 'alice.dusk@example.com')->forceDelete();
            $this->roomType->forceDelete();
        }

        parent::tearDown();
    }

    public function test_booking_page_renders_correctly(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/booking?room_type_id=' . $this->roomType->id)
                ->assertSee('Complete Your Reservation')
                ->assertPresent('.step-indicator')
                ->assertPresent('#bookingForm')
                ->assertPresent('.room-cards')
                ->assertPresent('.review-strip')
                ->assertPresent('#submitBtn')
                ->assertSee('Dusk Test Suite');
        });
    }

    public function test_room_card_selection(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/booking?room_type_id=' . $this->roomType->id);

            $browser->click('.room-card[data-room-type-id="' . $this->roomType->id . '"]');

            $selected = $browser->script(
                "return document.querySelector('.room-card.selected') !== null;"
            )[0];

            $this->assertTrue($selected, 'A room card should have the selected class after clicking');

            $hiddenValue = $browser->script(
                "return document.getElementById('room_type_id').value;"
            )[0];

            $this->assertEquals((string) $this->roomType->id, $hiddenValue,
                'Hidden room_type_id select should match the clicked card');
        });
    }

    public function test_form_validation_prevents_submission(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/booking?room_type_id=' . $this->roomType->id);
            $browser->script("document.getElementById('bookingForm').submit();");
            $browser->pause(2000)->assertPathIs('/booking');
        });
    }

    public function test_stepper_increments_adults(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/booking?room_type_id=' . $this->roomType->id);

            $initial = $browser->script(
                "return parseInt(document.getElementById('adults').value);"
            )[0];

            $browser->click('.step-inc[data-target="adults"]');

            $after = $browser->script(
                "return parseInt(document.getElementById('adults').value);"
            )[0];

            $this->assertEquals($initial + 1, $after, 'Adults should increment by 1');
        });
    }

    public function test_step_indicator_advances_as_fields_are_filled(): void
    {
        $this->browse(function (Browser $browser) {
            $tomorrow = now()->addDay()->format('Y-m-d');
            $dayAfter = now()->addDays(2)->format('Y-m-d');

            $browser->visit('/booking?room_type_id=' . $this->roomType->id);

            $browser->script(
                "document.getElementById('check_in_date').value = '$tomorrow';" .
                "document.getElementById('check_out_date').value = '$dayAfter';" .
                "document.getElementById('check_in_date').dispatchEvent(new Event('input', { bubbles: true }));" .
                "document.getElementById('check_out_date').dispatchEvent(new Event('input', { bubbles: true }));" .
                "document.querySelector('.room-card[data-room-type-id=\"" . $this->roomType->id . "\"]')?.click();"
            );

            $browser->pause(500);

            $browser->type('guest_name', 'Jane Dusk')
                ->type('guest_email', 'jane@example.com')
                ->type('guest_phone', '08011111111')
                ->select('guest_gender', 'female')
                ->type('guest_address', '456 Test Road')
                ->type('guest_nationality', 'Testian');

            $browser->select('guest_id_type', 'NIN')
                ->type('guest_id_number', '98765432109');

            $browser->pause(300);

            $activeStep = $browser->script(
                "var items = document.querySelectorAll('.step-indicator .step-item');" .
                "for (var i = items.length - 1; i >= 0; i--) {" .
                "  if (items[i].classList.contains('active') || items[i].classList.contains('completed')) {" .
                "    return i + 1;" .
                "  }" .
                "}" .
                "return 0;"
            )[0];

            $this->assertGreaterThanOrEqual(3, $activeStep,
                'Step indicator should be at step 3 or later with ID fields filled');
        });
    }

    public function test_full_booking_flow_pay_on_arrival(): void
    {
        $this->browse(function (Browser $browser) {
            $tomorrow = now()->addDay()->format('Y-m-d');
            $dayAfter = now()->addDays(2)->format('Y-m-d');

            $browser->visit('/booking?room_type_id=' . $this->roomType->id);

            $browser->script(
                "document.getElementById('check_in_date').value = '$tomorrow';" .
                "document.getElementById('check_out_date').value = '$dayAfter';"
            );

            $browser->click('.room-card[data-room-type-id="' . $this->roomType->id . '"]');

            $browser->type('guest_name', 'Alice Dusk')
                ->type('guest_email', 'alice.dusk@example.com')
                ->type('guest_phone', '08022222222')
                ->select('guest_gender', 'female')
                ->type('guest_address', '789 Dusk Lane')
                ->type('guest_nationality', 'Testania');

            $browser->select('guest_id_type', 'NIN')
                ->type('guest_id_number', '12345678901');

            $browser->radio('payment_method', 'pay_on_arrival');

            $browser->pause(4000);

            $browser->script("document.getElementById('bookingForm').submit();");

            $browser->waitUntil("window.location.pathname.startsWith('/booking/confirmation')", 15);

            $browser->assertSee('Hello, Alice Dusk');
        });
    }
}
