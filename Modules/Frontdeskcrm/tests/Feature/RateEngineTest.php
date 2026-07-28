<?php

namespace Modules\Frontdeskcrm\Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Frontdeskcrm\Models\GuestType;
use Modules\Frontdeskcrm\Models\RateCode;
use Modules\Frontdeskcrm\Models\Season;
use Modules\Frontdeskcrm\Services\RateCalculator;
use Tests\TestCase;

class RateEngineTest extends TestCase
{
    use DatabaseTransactions;

    protected RateCalculator $calculator;

    protected function setUp(): void
    {
        parent::setUp();
        Season::query()->delete();
        $this->calculator = app(RateCalculator::class);
    }

    public function test_calculates_base_rate_from_rate_code(): void
    {
        $rateCode = RateCode::create([
            'code' => 'TEST',
            'name' => 'Test Rate',
            'default_rate' => 100.00,
            'min_los' => 1,
        ]);

        $rate = $this->calculator->calculate($rateCode, Carbon::today());

        $this->assertEquals(100.00, $rate);
    }

    public function test_rate_calendar_overrides_default_rate(): void
    {
        $rateCode = RateCode::create([
            'code' => 'OVERRIDE',
            'name' => 'Override Test',
            'default_rate' => 100.00,
            'min_los' => 1,
        ]);

        $date = Carbon::today();
        $rateCode->calendar()->create([
            'date' => $date,
            'rate' => 150.00,
        ]);

        $rate = $this->calculator->calculate($rateCode, $date);

        $this->assertEquals(150.00, $rate);
    }

    public function test_season_multiplier_applies_to_rate(): void
    {
        $rateCode = RateCode::create([
            'code' => 'SEASON',
            'name' => 'Season Test',
            'default_rate' => 100.00,
            'min_los' => 1,
        ]);

        $peakDate = Carbon::create(2026, 12, 20);

        Season::create([
            'code' => 'PEAKTEST',
            'name' => 'Peak Test',
            'valid_from' => Carbon::create(2026, 12, 1),
            'valid_to' => Carbon::create(2027, 1, 15),
            'rate_multiplier' => 2.0000,
            'is_active' => true,
        ]);

        $rate = $this->calculator->calculate($rateCode, $peakDate);

        $this->assertEquals(200.00, $rate);
    }

    public function test_guest_type_discount_applies(): void
    {
        $rateCode = RateCode::create([
            'code' => 'DISC',
            'name' => 'Discount Test',
            'default_rate' => 100.00,
            'min_los' => 1,
        ]);

        $vipGuestType = GuestType::create([
            'name' => 'VIP Test',
            'discount_rate' => 10.00,
            'is_active' => true,
            'color' => '#ff0000',
        ]);

        $rate = $this->calculator->calculate($rateCode, Carbon::today(), $vipGuestType);

        $this->assertEquals(90.00, $rate);
    }

    public function test_restrictions_validated_properly(): void
    {
        $rateCode = RateCode::create([
            'code' => 'RESTRICT',
            'name' => 'Restrictions Test',
            'default_rate' => 100.00,
            'min_los' => 3,
            'max_los' => 7,
        ]);

        $errors = $this->calculator->validateRestrictions(
            $rateCode,
            Carbon::today(),
            Carbon::today()->addDays(10),
            1,
        );

        $this->assertNotEmpty($errors);
        $this->assertStringContainsString('Minimum', $errors[0]);
    }

    public function test_calculate_for_stay_returns_nightly_breakdown(): void
    {
        $rateCode = RateCode::create([
            'code' => 'STAY',
            'name' => 'Stay Test',
            'default_rate' => 100.00,
            'min_los' => 1,
        ]);

        $result = $this->calculator->calculateForStay(
            $rateCode,
            Carbon::today(),
            Carbon::today()->addDays(3),
        );

        $this->assertCount(3, $result['nights']);
        $this->assertEquals(300.00, $result['total']);
        $this->assertEquals(100.00, $result['average_rate']);
    }

    public function test_no_active_season_returns_default_rate(): void
    {
        $rateCode = RateCode::create([
            'code' => 'NOSEASON',
            'name' => 'No Season',
            'default_rate' => 100.00,
            'min_los' => 1,
        ]);

        Season::create([
            'code' => 'INACTIVES',
            'name' => 'Inactive',
            'valid_from' => Carbon::today()->subDays(10),
            'valid_to' => Carbon::today()->addDays(10),
            'rate_multiplier' => 3.0000,
            'is_active' => false,
        ]);

        $rate = $this->calculator->calculate($rateCode, Carbon::today());

        $this->assertEquals(100.00, $rate);
    }

    public function test_closed_to_arrival_restriction(): void
    {
        $rateCode = RateCode::create([
            'code' => 'CTATEST',
            'name' => 'CTA Test',
            'default_rate' => 100.00,
            'min_los' => 1,
            'closed_to_arrival' => true,
        ]);

        $errors = $this->calculator->validateRestrictions(
            $rateCode,
            Carbon::today(),
            Carbon::today()->addDay(),
            1,
        );

        $this->assertStringContainsString('closed to arrival', strtolower($errors[0]));
    }
}
