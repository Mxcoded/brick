<?php

namespace Modules\Frontdeskcrm\Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Modules\Frontdeskcrm\Models\RateCode;
use Modules\Frontdeskcrm\Models\Season;

class RateAndSeasonSeeder extends Seeder
{
    public function run(): void
    {
        RateCode::create([
            'code' => 'RACK',
            'name' => 'Rack Rate',
            'description' => 'Standard published rate for all room types.',
            'default_rate' => 45000.00,
            'currency' => 'NGN',
            'min_los' => 1,
            'max_los' => null,
            'closed_to_arrival' => false,
            'closed_to_departure' => false,
            'apply_weekdays' => true,
            'apply_weekends' => true,
            'is_active' => true,
            'sort_order' => 0,
        ]);

        RateCode::create([
            'code' => 'BAR',
            'name' => 'Best Available Rate',
            'description' => 'Dynamic best available rate with seasonal adjustments.',
            'default_rate' => 42000.00,
            'currency' => 'NGN',
            'min_los' => 1,
            'max_los' => null,
            'is_active' => true,
            'sort_order' => 1,
        ]);

        RateCode::create([
            'code' => 'CORP',
            'name' => 'Corporate Rate',
            'description' => 'Negotiated rate for corporate accounts.',
            'default_rate' => 35000.00,
            'currency' => 'NGN',
            'min_los' => 1,
            'max_los' => 30,
            'is_active' => true,
            'sort_order' => 2,
        ]);

        RateCode::create([
            'code' => 'PROMO',
            'name' => 'Promotional Rate',
            'description' => 'Special promotion rate with restrictions.',
            'default_rate' => 32000.00,
            'currency' => 'NGN',
            'min_los' => 2,
            'max_los' => 14,
            'closed_to_arrival' => false,
            'closed_to_departure' => false,
            'apply_weekdays' => true,
            'apply_weekends' => false,
            'is_active' => true,
            'sort_order' => 3,
        ]);

        RateCode::create([
            'code' => 'LTO',
            'name' => 'Long Stay Rate',
            'description' => 'Discounted rate for extended stays (7+ nights).',
            'default_rate' => 28000.00,
            'currency' => 'NGN',
            'min_los' => 7,
            'max_los' => 90,
            'is_active' => true,
            'sort_order' => 4,
        ]);

        Season::create([
            'code' => 'PEAK',
            'name' => 'Peak Season',
            'description' => 'High demand period (Dec–Jan holidays)',
            'valid_from' => Carbon::create(2026, 12, 15),
            'valid_to' => Carbon::create(2027, 1, 15),
            'rate_multiplier' => 1.5000,
            'is_active' => true,
        ]);

        Season::create([
            'code' => 'OFFPEAK',
            'name' => 'Off-Peak Season',
            'description' => 'Low demand period (Feb–Mar)',
            'valid_from' => Carbon::create(2027, 2, 1),
            'valid_to' => Carbon::create(2027, 3, 31),
            'rate_multiplier' => 0.8000,
            'is_active' => true,
        ]);

        Season::create([
            'code' => 'EASTER',
            'name' => 'Easter Period',
            'description' => 'Easter holiday premium',
            'valid_from' => Carbon::create(2027, 3, 24),
            'valid_to' => Carbon::create(2027, 4, 1),
            'rate_multiplier' => 1.2500,
            'is_active' => true,
        ]);
    }
}
