<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Frontdeskcrm\Models\GuestType;
use Modules\Frontdeskcrm\Models\GuestTypeRate;
use Modules\Frontdeskcrm\Models\RateCalendar;
use Modules\Frontdeskcrm\Models\RateCode;
use Modules\Frontdeskcrm\Models\Season;
use Modules\Frontdeskcrm\Services\RateCalculator;
use Modules\Website\Models\RoomType;
use Modules\Website\Services\WebsiteRateService;
use Tests\TestCase;

/**
 * GuestType Rate System — Negotiated Rates & Contract Validation
 *
 * Tests the upgraded GuestType system:
 *   1. Negotiated rates per GuestType × RoomType override base pricing
 *   2. Discount rate % fallback when no negotiated rate exists
 *   3. Contract validity dates (valid_from/valid_to)
 *   4. RateCalculator integration — negotiated rates skip season/calendar
 *   5. WebsiteRateService integration
 *   6. Multiple negotiated rates for different room types
 *   7. End-to-end chain: GuestTypeRate → RateCalculator → final rate
 */
class GuestTypeRateSystemTest extends TestCase
{
    use DatabaseTransactions;

    protected RateCalculator $calculator;

    protected WebsiteRateService $rateService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->calculator = app(RateCalculator::class);
        $this->rateService = app(WebsiteRateService::class);
    }

    private function makeRateCode(array $overrides = []): RateCode
    {
        return RateCode::create(array_merge([
            'code' => 'RC-'.uniqid(),
            'name' => 'Rate '.uniqid(),
            'default_rate' => 50000,
            'min_los' => 1,
            'is_active' => true,
        ], $overrides));
    }

    private function makeRoomType(array $overrides = []): RoomType
    {
        return RoomType::create(array_merge([
            'name' => 'Deluxe Suite',
            'slug' => 'deluxe-'.uniqid(),
            'price' => 50000,
            'capacity' => 4,
            'base_occupancy' => 2,
            'extra_adult_fee' => 10000,
            'extra_child_fee' => 5000,
            'is_active' => true,
        ], $overrides));
    }

    private function makeGuestType(array $overrides = []): GuestType
    {
        return GuestType::create(array_merge([
            'name' => 'Corp-'.uniqid(),
            'description' => 'Business traveler',
            'color' => '#007bff',
            'discount_rate' => 5.00,
            'is_active' => true,
        ], $overrides));
    }

    // ════════════════════════════════════════════════════════════════
    // 1. NEGOTIATED RATES — Core feature
    // ════════════════════════════════════════════════════════════════

    public function test_negotiated_rate_overrides_base_rate(): void
    {
        $roomType = $this->makeRoomType(['price' => 50000]);
        $guestType = $this->makeGuestType(['discount_rate' => 5]);

        // Create negotiated rate of ₦40,000/night for this room type
        GuestTypeRate::create([
            'guest_type_id' => $guestType->id,
            'room_type_id' => $roomType->id,
            'rate' => 40000,
            'is_active' => true,
        ]);

        $result = $guestType->getNegotiatedRate($roomType->id);

        $this->assertTrue($result['has_negotiated_rate']);
        $this->assertEquals(40000, $result['rate']);
        $this->assertEquals('negotiated', $result['source']);
    }

    public function test_negotiated_rate_is_fixed_regardless_of_season(): void
    {
        $rateCode = $this->makeRateCode();
        $roomType = $this->makeRoomType(['price' => 50000, 'rate_code_id' => $rateCode->id]);
        $guestType = $this->makeGuestType(['discount_rate' => 5]);

        GuestTypeRate::create([
            'guest_type_id' => $guestType->id,
            'room_type_id' => $roomType->id,
            'rate' => 40000,
            'is_active' => true,
        ]);

        Season::create([
            'code' => 'PK-'.uniqid(),
            'name' => 'Peak Season',
            'valid_from' => '2026-07-01',
            'valid_to' => '2026-08-31',
            'rate_multiplier' => 2.0,
            'is_active' => true,
        ]);

        $rate = $this->calculator->calculate($rateCode, Carbon::parse('2026-07-15'), $guestType, $roomType->id);

        $this->assertEquals(40000, $rate);
    }

    public function test_rate_calculator_uses_negotiated_rate_for_stay(): void
    {
        $rateCode = $this->makeRateCode();
        $roomType = $this->makeRoomType(['price' => 50000, 'rate_code_id' => $rateCode->id]);
        $guestType = $this->makeGuestType(['discount_rate' => 5]);

        GuestTypeRate::create([
            'guest_type_id' => $guestType->id,
            'room_type_id' => $roomType->id,
            'rate' => 40000,
            'is_active' => true,
        ]);

        $result = $this->calculator->calculateForStay(
            $rateCode,
            Carbon::parse('2026-07-10'),
            Carbon::parse('2026-07-13'),
            $guestType,
            $roomType->id
        );

        // 3 nights × ₦40,000 = ₦120,000
        $this->assertEquals(120000, $result['total']);
        $this->assertEquals(40000, $result['average_rate']);
    }

    // ════════════════════════════════════════════════════════════════
    // 2. DISCOUNT RATE FALLBACK — When no negotiated rate exists
    // ════════════════════════════════════════════════════════════════

    public function test_discount_rate_applies_when_no_negotiated_rate(): void
    {
        $rateCode = $this->makeRateCode(['default_rate' => 50000]);
        $guestType = $this->makeGuestType(['discount_rate' => 10]);

        // No negotiated rate — discount_rate kicks in
        $rate = $this->calculator->calculate($rateCode, Carbon::parse('2026-07-15'), $guestType);

        // ₦50,000 × 0.90 = ₦45,000
        $this->assertEquals(45000, $rate);
    }

    public function test_zero_discount_rate_has_no_effect(): void
    {
        $rateCode = $this->makeRateCode(['default_rate' => 50000]);
        $guestType = $this->makeGuestType(['discount_rate' => 0]);

        $rate = $this->calculator->calculate($rateCode, Carbon::parse('2026-07-15'), $guestType);

        $this->assertEquals(50000, $rate);
    }

    public function test_negotiated_rate_takes_priority_over_discount_rate(): void
    {
        $rateCode = $this->makeRateCode(['default_rate' => 50000]);
        $roomType = $this->makeRoomType(['price' => 50000, 'rate_code_id' => $rateCode->id]);
        $guestType = $this->makeGuestType(['discount_rate' => 10]);

        // Negotiated rate ₦35,000 AND discount_rate 10%
        GuestTypeRate::create([
            'guest_type_id' => $guestType->id,
            'room_type_id' => $roomType->id,
            'rate' => 35000,
            'is_active' => true,
        ]);

        $rate = $this->calculator->calculate($rateCode, Carbon::parse('2026-07-15'), $guestType, $roomType->id);

        // Negotiated rate wins — ₦35,000, not ₦45,000 (discounted rack)
        $this->assertEquals(35000, $rate);
    }

    // ════════════════════════════════════════════════════════════════
    // 3. CONTRACT VALIDITY — Dates enforcement
    // ════════════════════════════════════════════════════════════════

    public function test_negotiated_rate_respects_valid_from(): void
    {
        $guestType = $this->makeGuestType();
        $roomType = $this->makeRoomType();

        GuestTypeRate::create([
            'guest_type_id' => $guestType->id,
            'room_type_id' => $roomType->id,
            'rate' => 40000,
            'valid_from' => '2026-08-01',
            'valid_to' => '2026-12-31',
            'is_active' => true,
        ]);

        // Before valid_from — no negotiated rate
        $result = $guestType->getNegotiatedRate($roomType->id, '2026-07-15');
        $this->assertFalse($result['has_negotiated_rate']);

        // On valid_from — negotiated rate applies
        $result = $guestType->getNegotiatedRate($roomType->id, '2026-08-01');
        $this->assertTrue($result['has_negotiated_rate']);
        $this->assertEquals(40000, $result['rate']);
    }

    public function test_negotiated_rate_respects_valid_to(): void
    {
        $guestType = $this->makeGuestType();
        $roomType = $this->makeRoomType();

        GuestTypeRate::create([
            'guest_type_id' => $guestType->id,
            'room_type_id' => $roomType->id,
            'rate' => 40000,
            'valid_from' => '2026-01-01',
            'valid_to' => '2026-06-30',
            'is_active' => true,
        ]);

        // Before valid_to — negotiated rate applies
        $result = $guestType->getNegotiatedRate($roomType->id, '2026-06-15');
        $this->assertTrue($result['has_negotiated_rate']);

        // After valid_to — no negotiated rate
        $result = $guestType->getNegotiatedRate($roomType->id, '2026-07-01');
        $this->assertFalse($result['has_negotiated_rate']);
    }

    public function test_guest_type_validity_check(): void
    {
        $guestType = $this->makeGuestType([
            'valid_from' => '2026-01-01',
            'valid_to' => '2026-12-31',
        ]);

        $this->assertTrue($guestType->isValidNow());

        $expired = $this->makeGuestType([
            'name' => 'Expired Corp',
            'valid_from' => '2025-01-01',
            'valid_to' => '2025-12-31',
        ]);

        $this->assertFalse($expired->isValidNow());
    }

    public function test_no_validity_dates_means_always_valid(): void
    {
        $guestType = $this->makeGuestType([
            'valid_from' => null,
            'valid_to' => null,
        ]);

        $this->assertTrue($guestType->isValidNow());
    }

    // ════════════════════════════════════════════════════════════════
    // 4. MULTIPLE NEGOTIATED RATES — Different room types
    // ════════════════════════════════════════════════════════════════

    public function test_different_room_types_get_different_negotiated_rates(): void
    {
        $guestType = $this->makeGuestType();
        $deluxe = $this->makeRoomType(['name' => 'Deluxe', 'slug' => 'deluxe-'.uniqid(), 'price' => 50000]);
        $standard = $this->makeRoomType(['name' => 'Standard', 'slug' => 'standard-'.uniqid(), 'price' => 30000]);

        GuestTypeRate::create([
            'guest_type_id' => $guestType->id,
            'room_type_id' => $deluxe->id,
            'rate' => 45000,
            'is_active' => true,
        ]);

        GuestTypeRate::create([
            'guest_type_id' => $guestType->id,
            'room_type_id' => $standard->id,
            'rate' => 27000,
            'is_active' => true,
        ]);

        $deluxeResult = $guestType->getNegotiatedRate($deluxe->id);
        $standardResult = $guestType->getNegotiatedRate($standard->id);

        $this->assertEquals(45000, $deluxeResult['rate']);
        $this->assertEquals(27000, $standardResult['rate']);
    }

    public function test_inactive_negotiated_rate_is_ignored(): void
    {
        $guestType = $this->makeGuestType();
        $roomType = $this->makeRoomType();

        GuestTypeRate::create([
            'guest_type_id' => $guestType->id,
            'room_type_id' => $roomType->id,
            'rate' => 40000,
            'is_active' => false,
        ]);

        $result = $guestType->getNegotiatedRate($roomType->id);

        $this->assertFalse($result['has_negotiated_rate']);
        $this->assertEquals('discount', $result['source']);
    }

    // ════════════════════════════════════════════════════════════════
    // 5. WEBSITE RATE SERVICE — Integration
    // ════════════════════════════════════════════════════════════════

    public function test_website_rate_service_with_negotiated_rate(): void
    {
        $roomType = $this->makeRoomType(['price' => 50000]);
        $guestType = $this->makeGuestType();

        GuestTypeRate::create([
            'guest_type_id' => $guestType->id,
            'room_type_id' => $roomType->id,
            'rate' => 42000,
            'is_active' => true,
        ]);

        $result = $this->rateService->calculate($roomType, '2026-07-10', '2026-07-13', $guestType);

        // 3 nights × ₦42,000 = ₦126,000
        $this->assertEquals(126000, $result['total']);
        $this->assertEquals(42000, $result['price_per_night']);
    }

    public function test_website_rate_service_falls_back_without_guest_type(): void
    {
        $roomType = $this->makeRoomType(['price' => 50000]);

        $result = $this->rateService->calculate($roomType, '2026-07-10', '2026-07-13');

        // 3 nights × ₦50,000 = ₦150,000 (flat fallback)
        $this->assertEquals(150000, $result['total']);
        $this->assertEquals(50000, $result['price_per_night']);
    }

    public function test_website_rate_service_with_guests_and_negotiated_rate(): void
    {
        $roomType = $this->makeRoomType([
            'price' => 50000,
            'base_occupancy' => 2,
            'extra_adult_fee' => 10000,
            'extra_child_fee' => 5000,
        ]);
        $guestType = $this->makeGuestType();

        GuestTypeRate::create([
            'guest_type_id' => $guestType->id,
            'room_type_id' => $roomType->id,
            'rate' => 42000,
            'is_active' => true,
        ]);

        $result = $this->rateService->calculateWithGuests(
            $roomType, '2026-07-10', '2026-07-13',
            3, 1, $guestType
        );

        // Base: 3 nights × ₦42,000 = ₦126,000
        // Guest fee: 1 extra adult (₦10,000) + 1 child (₦5,000) = ₦15,000/night × 3 = ₦45,000
        // Total: ₦126,000 + ₦45,000 = ₦171,000
        $this->assertEquals(171000, $result['total']);
        $this->assertEquals(126000, $result['base_total']);
        $this->assertEquals(45000, $result['guest_fee_total']);
    }

    // ════════════════════════════════════════════════════════════════
    // 6. SEASON + NEGOTIATED RATE INTERACTION
    // ════════════════════════════════════════════════════════════════

    public function test_negotiated_rate_bypasses_season_multiplier(): void
    {
        $rateCode = $this->makeRateCode(['default_rate' => 50000]);
        $roomType = $this->makeRoomType(['price' => 50000, 'rate_code_id' => $rateCode->id]);
        $guestType = $this->makeGuestType(['discount_rate' => 0]);

        GuestTypeRate::create([
            'guest_type_id' => $guestType->id,
            'room_type_id' => $roomType->id,
            'rate' => 40000,
            'is_active' => true,
        ]);

        Season::create([
            'code' => 'PK-'.uniqid(),
            'name' => 'Peak',
            'valid_from' => '2026-07-01',
            'valid_to' => '2026-08-31',
            'rate_multiplier' => 2.0,
            'is_active' => true,
        ]);

        $rate = $this->calculator->calculate($rateCode, Carbon::parse('2026-07-15'), $guestType, $roomType->id);
        $this->assertEquals(40000, $rate);
    }

    public function test_rate_calendar_plus_season_without_negotiated_rate(): void
    {
        $rateCode = $this->makeRateCode(['default_rate' => 50000]);
        $roomType = $this->makeRoomType(['price' => 50000, 'rate_code_id' => $rateCode->id]);
        $guestType = $this->makeGuestType(['discount_rate' => 0]);

        RateCalendar::create([
            'rate_code_id' => $rateCode->id,
            'date' => '2026-07-15',
            'rate' => 55000,
            'is_available' => true,
        ]);

        Season::create([
            'code' => 'HS-'.uniqid(),
            'name' => 'High Season',
            'valid_from' => '2026-07-01',
            'valid_to' => '2026-08-31',
            'rate_multiplier' => 1.5,
            'is_active' => true,
        ]);

        $rate = $this->calculator->calculate($rateCode, Carbon::parse('2026-07-15'), $guestType, $roomType->id);
        $this->assertEquals(82500, $rate);
    }

    // ════════════════════════════════════════════════════════════════
    // 7. END-TO-END CHAIN — Negotiated Rate → RateCalculator → Total
    // ════════════════════════════════════════════════════════════════

    public function test_full_chain_negotiated_rate_season_guest_fees(): void
    {
        $rateCode = $this->makeRateCode(['default_rate' => 50000]);
        $roomType = $this->makeRoomType([
            'price' => 50000,
            'rate_code_id' => $rateCode->id,
            'base_occupancy' => 2,
            'extra_adult_fee' => 10000,
        ]);
        $guestType = $this->makeGuestType(['discount_rate' => 10]);

        GuestTypeRate::create([
            'guest_type_id' => $guestType->id,
            'room_type_id' => $roomType->id,
            'rate' => 42000,
            'is_active' => true,
        ]);

        Season::create([
            'code' => 'HS-'.uniqid(),
            'name' => 'High',
            'valid_from' => '2026-07-01',
            'valid_to' => '2026-08-31',
            'rate_multiplier' => 1.5,
            'is_active' => true,
        ]);

        RateCalendar::create([
            'rate_code_id' => $rateCode->id,
            'date' => '2026-07-15',
            'rate' => 60000,
            'is_available' => true,
        ]);

        $result = $this->rateService->calculateWithGuests(
            $roomType, '2026-07-10', '2026-07-15',
            3, 0, $guestType
        );

        // Negotiated rate ₦42,000 bypasses season + calendar + discount
        // Base: 5 nights × ₦42,000 = ₦210,000
        // Guest fee: 1 extra adult (3 - 2 base) × ₦10,000 × 5 = ₦50,000
        // Total: ₦210,000 + ₦50,000 = ₦260,000
        $this->assertEquals(260000, $result['total']);
        $this->assertEquals(210000, $result['base_total']);
        $this->assertEquals(50000, $result['guest_fee_total']);
    }

    public function test_end_to_end_no_negotiated_rate_uses_discount_and_season(): void
    {
        $rateCode = $this->makeRateCode(['default_rate' => 50000]);
        $roomType = $this->makeRoomType([
            'price' => 50000,
            'rate_code_id' => $rateCode->id,
        ]);
        $guestType = $this->makeGuestType(['discount_rate' => 10]);

        Season::create([
            'code' => 'PK-'.uniqid(),
            'name' => 'Peak',
            'valid_from' => '2026-07-01',
            'valid_to' => '2026-08-31',
            'rate_multiplier' => 1.5,
            'is_active' => true,
        ]);

        $result = $this->calculator->calculateForStay(
            $rateCode,
            Carbon::parse('2026-07-10'),
            Carbon::parse('2026-07-13'),
            $guestType
        );

        // Per night: ₦50,000 × 1.5 (season) × 0.90 (discount) = ₦67,500
        // 3 nights × ₦67,500 = ₦202,500
        $this->assertEquals(202500, $result['total']);
        $this->assertEquals(67500, $result['average_rate']);
    }

    // ════════════════════════════════════════════════════════════════
    // 8. GUEST TYPE SCOPE & RELATIONSHIPS
    // ════════════════════════════════════════════════════════════════

    public function test_guest_type_rates_relationship(): void
    {
        $guestType = $this->makeGuestType();
        $roomType = $this->makeRoomType();

        GuestTypeRate::create([
            'guest_type_id' => $guestType->id,
            'room_type_id' => $roomType->id,
            'rate' => 40000,
            'is_active' => true,
        ]);

        $guestType->load('rates');

        $this->assertCount(1, $guestType->rates);
        $this->assertEquals(40000, $guestType->rates->first()->rate);
    }

    public function test_guest_type_without_negotiated_rates_returns_discount(): void
    {
        $guestType = $this->makeGuestType(['discount_rate' => 15]);
        $roomType = $this->makeRoomType();

        $result = $guestType->getNegotiatedRate($roomType->id);

        $this->assertFalse($result['has_negotiated_rate']);
        $this->assertEquals(15, $result['rate']);
        $this->assertEquals('discount', $result['source']);
    }
}
