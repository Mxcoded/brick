<?php

namespace Modules\Website\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Frontdeskcrm\Models\RateCalendar;
use Modules\Frontdeskcrm\Models\RateCode;
use Modules\Website\Models\RoomType;
use Modules\Website\Services\WebsiteRateService;
use Tests\TestCase;

class WebsiteRateServiceTest extends TestCase
{
    use RefreshDatabase;

    protected WebsiteRateService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(WebsiteRateService::class);
    }

    public function test_falls_back_to_flat_rate_when_no_rate_code(): void
    {
        $roomType = RoomType::create([
            'name' => 'Standard',
            'slug' => 'std-rate-'.uniqid(),
            'price' => 25000,
            'capacity' => 2,
        ]);

        $result = $this->service->calculate($roomType, '2026-07-22', '2026-07-25');

        $this->assertEquals(25000, $result['price_per_night']);
        $this->assertEquals(75000, $result['total']);
        $this->assertNull($result['rate_code_id']);
    }

    public function test_uses_rate_code_when_assigned(): void
    {
        $rateCode = RateCode::create([
            'code' => 'WEB'.uniqid(),
            'name' => 'Website Rate',
            'default_rate' => 35000,
            'currency' => 'NGN',
            'is_active' => true,
        ]);

        $roomType = RoomType::create([
            'name' => 'Deluxe',
            'slug' => 'dlx-rate-'.uniqid(),
            'price' => 25000,
            'rate_code_id' => $rateCode->id,
            'capacity' => 2,
        ]);

        $result = $this->service->calculate($roomType, '2026-07-22', '2026-07-25');

        $this->assertEquals(35000, $result['price_per_night']);
        $this->assertEquals(105000, $result['total']);
        $this->assertEquals($rateCode->id, $result['rate_code_id']);
    }

    public function test_rate_calendar_override_applies(): void
    {
        $rateCode = RateCode::create([
            'code' => 'CAL'.uniqid(),
            'name' => 'Calendar Rate',
            'default_rate' => 30000,
            'currency' => 'NGN',
            'is_active' => true,
        ]);

        RateCalendar::create([
            'rate_code_id' => $rateCode->id,
            'date' => '2026-07-23',
            'rate' => 45000,
            'is_available' => true,
            'available_rooms' => 10,
        ]);

        $roomType = RoomType::create([
            'name' => 'Suite',
            'slug' => 'suite-rate-'.uniqid(),
            'price' => 25000,
            'rate_code_id' => $rateCode->id,
            'capacity' => 2,
        ]);

        $result = $this->service->calculate($roomType, '2026-07-22', '2026-07-25');

        $this->assertEquals(3, count($result['nights']));
        $this->assertEquals(30000, $result['nights']['2026-07-22']);
        $this->assertEquals(45000, $result['nights']['2026-07-23']);
        $this->assertEquals(30000, $result['nights']['2026-07-24']);
        $this->assertEquals(105000, $result['total']);
    }

    public function test_inactive_rate_code_falls_back_to_flat(): void
    {
        $rateCode = RateCode::create([
            'code' => 'INA'.uniqid(),
            'name' => 'Inactive Rate',
            'default_rate' => 50000,
            'currency' => 'NGN',
            'is_active' => false,
        ]);

        $roomType = RoomType::create([
            'name' => 'Premium',
            'slug' => 'prem-rate-'.uniqid(),
            'price' => 25000,
            'rate_code_id' => $rateCode->id,
            'capacity' => 2,
        ]);

        $result = $this->service->calculate($roomType, '2026-07-22', '2026-07-25');

        $this->assertEquals(25000, $result['price_per_night']);
        $this->assertNull($result['rate_code_id']);
    }

    public function test_single_night_stay(): void
    {
        $roomType = RoomType::create([
            'name' => 'Single',
            'slug' => 'single-rate-'.uniqid(),
            'price' => 20000,
            'capacity' => 2,
        ]);

        $result = $this->service->calculate($roomType, '2026-07-22', '2026-07-23');

        $this->assertEquals(20000, $result['price_per_night']);
        $this->assertEquals(20000, $result['total']);
    }
}
