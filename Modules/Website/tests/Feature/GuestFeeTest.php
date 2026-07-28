<?php

namespace Modules\Website\Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Modules\Website\Services\BookingCartService;
use Modules\Website\Services\WebsiteRateService;
use Tests\TestCase;

class GuestFeeTest extends TestCase
{
    use DatabaseTransactions;

    private function makeRoomType(array $overrides = []): RoomType
    {
        return RoomType::create(array_merge([
            'name' => 'Deluxe Suite',
            'slug' => 'deluxe-'.uniqid(),
            'price' => 20000,
            'capacity' => 4,
            'base_occupancy' => 2,
            'extra_adult_fee' => 5000,
            'extra_child_fee' => 2000,
            'is_active' => true,
        ], $overrides));
    }

    private function makeUnit(RoomType $roomType, array $overrides = []): RoomUnit
    {
        return RoomUnit::create(array_merge([
            'room_type_id' => $roomType->id,
            'room_number' => '101',
            'floor' => 1,
            'status' => 'available',
            'housekeeping_status' => 'clean',
        ], $overrides));
    }

    // ── RoomType::calculateGuestFee() ──

    public function test_no_extra_guests_returns_zero_fee(): void
    {
        $roomType = $this->makeRoomType();

        $result = $roomType->calculateGuestFee(2, 0);

        $this->assertEquals(0, $result['extra_fee_per_night']);
        $this->assertEquals(0, $result['extra_adults']);
        $this->assertEquals(0, $result['extra_children']);
    }

    public function test_extra_adult_fee(): void
    {
        $roomType = $this->makeRoomType([
            'base_occupancy' => 2,
            'extra_adult_fee' => 5000,
        ]);

        $result = $roomType->calculateGuestFee(3, 0);

        $this->assertEquals(5000, $result['extra_fee_per_night']);
        $this->assertEquals(1, $result['extra_adults']);
    }

    public function test_extra_child_fee(): void
    {
        $roomType = $this->makeRoomType([
            'extra_child_fee' => 2000,
        ]);

        $result = $roomType->calculateGuestFee(2, 1);

        $this->assertEquals(2000, $result['extra_fee_per_night']);
        $this->assertEquals(1, $result['extra_children']);
    }

    public function test_combined_extra_fees(): void
    {
        $roomType = $this->makeRoomType([
            'base_occupancy' => 2,
            'extra_adult_fee' => 5000,
            'extra_child_fee' => 2000,
        ]);

        $result = $roomType->calculateGuestFee(4, 2);

        $this->assertEquals(14000, $result['extra_fee_per_night']); // 2*5000 + 2*2000
        $this->assertEquals(2, $result['extra_adults']);
        $this->assertEquals(2, $result['extra_children']);
    }

    public function test_no_extra_fees_when_zero(): void
    {
        $roomType = $this->makeRoomType([
            'extra_adult_fee' => 0,
            'extra_child_fee' => 0,
        ]);

        $result = $roomType->calculateGuestFee(5, 3);

        $this->assertEquals(0, $result['extra_fee_per_night']);
    }

    // ── WebsiteRateService::calculateWithGuests() ──

    public function test_calculate_with_guests_includes_guest_fee(): void
    {
        $roomType = $this->makeRoomType([
            'price' => 20000,
            'base_occupancy' => 2,
            'extra_adult_fee' => 5000,
        ]);

        $service = app(WebsiteRateService::class);
        $result = $service->calculateWithGuests($roomType, '2026-08-01', '2026-08-04', 3, 0);

        // 3 nights * 20000 base = 60000 base_total
        // 3 nights * 5000 extra adult = 15000 guest_fee_total
        // total = 75000
        $this->assertEquals(60000, $result['base_total']);
        $this->assertEquals(5000, $result['guest_fee_per_night']);
        $this->assertEquals(15000, $result['guest_fee_total']);
        $this->assertEquals(75000, $result['total']);
    }

    public function test_calculate_with_guests_no_fee_when_base_occupancy(): void
    {
        $roomType = $this->makeRoomType([
            'price' => 20000,
            'base_occupancy' => 2,
            'extra_adult_fee' => 5000,
            'extra_child_fee' => 2000,
        ]);

        $service = app(WebsiteRateService::class);
        $result = $service->calculateWithGuests($roomType, '2026-08-01', '2026-08-04', 2, 0);

        $this->assertEquals(0, $result['guest_fee_total']);
        $this->assertEquals($result['base_total'], $result['total']);
    }

    // ── BookingCartService capacity validation ──

    public function test_cart_rejects_guests_exceeding_capacity(): void
    {
        $roomType = $this->makeRoomType(['capacity' => 2]);
        $this->makeUnit($roomType);

        $cartService = new BookingCartService;
        $result = $cartService->add($roomType->id, 1, '2026-08-01', '2026-08-03', 3, 0);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('exceed', $result['message']);
    }

    public function test_cart_accepts_guests_within_capacity(): void
    {
        $roomType = $this->makeRoomType(['capacity' => 4]);
        $this->makeUnit($roomType);

        $cartService = new BookingCartService;
        $result = $cartService->add($roomType->id, 1, '2026-08-01', '2026-08-03', 2, 1);

        $this->assertTrue($result['success']);
    }

    public function test_cart_update_guests_validates_capacity(): void
    {
        $roomType = $this->makeRoomType(['capacity' => 2]);
        $this->makeUnit($roomType);

        $cartService = new BookingCartService;
        $cartService->add($roomType->id, 1, '2026-08-01', '2026-08-03', 2, 0);

        $result = $cartService->updateGuests($roomType->id, 3, 0);

        $this->assertFalse($result['success']);
        $this->assertStringContainsString('exceed', $result['message']);
    }

    public function test_cart_update_guests_recalculates_price(): void
    {
        $roomType = $this->makeRoomType([
            'price' => 20000,
            'capacity' => 4,
            'base_occupancy' => 2,
            'extra_adult_fee' => 5000,
        ]);
        $this->makeUnit($roomType);

        $cartService = new BookingCartService;
        $cartService->add($roomType->id, 1, '2026-08-01', '2026-08-03', 2, 0);

        $result = $cartService->updateGuests($roomType->id, 3, 0);

        $this->assertTrue($result['success']);
        // 2 nights * 20000 base = 40000 + 2 nights * 5000 extra = 50000
        $this->assertEquals(50000, $result['item']['total_rate']);
        $this->assertEquals(5000, $result['item']['guest_fee_per_night']);
    }
}
