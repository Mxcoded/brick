<?php

namespace Modules\Website\Tests\Feature;

use App\Models\RoomType;
use App\Models\RoomUnit;
use Illuminate\Support\Facades\Session;
use Modules\Website\Services\BookingCartService;
use Modules\Website\Tests\WebsiteModuleTestCase;

class MultiPropertyCartTest extends WebsiteModuleTestCase
{
    private BookingCartService $cart;

    protected function setUp(): void
    {
        parent::setUp();
        $this->cart = app(BookingCartService::class);
        Session::start();
    }

    public function test_add_room_to_cart(): void
    {
        $result = $this->cart->add(
            roomTypeId: $this->roomType->id,
            quantity: 1,
            checkIn: now()->addDays(5)->format('Y-m-d'),
            checkOut: now()->addDays(7)->format('Y-m-d')
        );

        $this->assertTrue($result['success']);
        $this->assertCount(1, $this->cart->getItems());
    }

    public function test_cannot_add_rooms_from_different_properties(): void
    {
        $secondRoom = RoomType::factory()->create([
            'property_id' => $this->secondProperty->id,
            'price' => 25000,
            'is_active' => true,
        ]);
        RoomUnit::factory()->create([
            'room_type_id' => $secondRoom->id,
            'property_id' => $this->secondProperty->id,
            'status' => 'available',
        ]);

        $this->cart->add(
            roomTypeId: $this->roomType->id,
            quantity: 1,
            checkIn: now()->addDays(5)->format('Y-m-d'),
            checkOut: now()->addDays(7)->format('Y-m-d')
        );

        $result = $this->cart->add(
            roomTypeId: $secondRoom->id,
            quantity: 1,
            checkIn: now()->addDays(5)->format('Y-m-d'),
            checkOut: now()->addDays(7)->format('Y-m-d')
        );

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('different properties', $result['message']);
    }

    public function test_can_add_same_property_rooms(): void
    {
        $samePropertyRoom = RoomType::factory()->create([
            'property_id' => $this->property->id,
            'price' => 25000,
            'is_active' => true,
        ]);
        RoomUnit::factory()->create([
            'room_type_id' => $samePropertyRoom->id,
            'property_id' => $this->property->id,
            'status' => 'available',
        ]);

        $this->cart->add(
            roomTypeId: $this->roomType->id,
            quantity: 1,
            checkIn: now()->addDays(5)->format('Y-m-d'),
            checkOut: now()->addDays(7)->format('Y-m-d')
        );

        $result = $this->cart->add(
            roomTypeId: $samePropertyRoom->id,
            quantity: 1,
            checkIn: now()->addDays(5)->format('Y-m-d'),
            checkOut: now()->addDays(7)->format('Y-m-d')
        );

        $this->assertTrue($result['success']);
        $this->assertCount(2, $this->cart->getItems());
    }

    public function test_cart_clear_works(): void
    {
        $this->cart->add(
            roomTypeId: $this->roomType->id,
            quantity: 1,
            checkIn: now()->addDays(5)->format('Y-m-d'),
            checkOut: now()->addDays(7)->format('Y-m-d')
        );

        $this->assertFalse($this->cart->isEmpty());

        $this->cart->clear();

        $this->assertTrue($this->cart->isEmpty());
    }

    public function test_different_dates_clears_cart(): void
    {
        $this->cart->add(
            roomTypeId: $this->roomType->id,
            quantity: 1,
            checkIn: now()->addDays(5)->format('Y-m-d'),
            checkOut: now()->addDays(7)->format('Y-m-d')
        );

        $result = $this->cart->add(
            roomTypeId: $this->roomType->id,
            quantity: 1,
            checkIn: now()->addDays(10)->format('Y-m-d'),
            checkOut: now()->addDays(12)->format('Y-m-d')
        );

        $this->assertTrue($result['success']);
        $this->assertCount(1, $this->cart->getItems());
        $this->assertEquals(now()->addDays(10)->format('Y-m-d'), $this->cart->getDates()['check_in']);
    }

    public function test_cart_summary_contains_property_id(): void
    {
        $this->cart->add(
            roomTypeId: $this->roomType->id,
            quantity: 1,
            checkIn: now()->addDays(5)->format('Y-m-d'),
            checkOut: now()->addDays(7)->format('Y-m-d')
        );

        $summary = $this->cart->getCartSummary();

        $this->assertEquals(1, $summary['total_rooms']);
        $this->assertEquals(2, $summary['total_guests']);
        $this->assertEquals(2, $summary['nights']);
        $this->assertGreaterThan(0, $summary['total']);
        $this->assertEquals($this->property->id, $summary['items'][0]['property_id']);
    }
}
