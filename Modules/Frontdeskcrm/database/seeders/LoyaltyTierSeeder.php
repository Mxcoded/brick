<?php

namespace Modules\Frontdeskcrm\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Frontdeskcrm\Models\LoyaltyTier;

class LoyaltyTierSeeder extends Seeder
{
    public function run(): void
    {
        LoyaltyTier::upsert([
            [
                'name' => 'Bronze',
                'slug' => 'bronze',
                'min_points' => 0,
                'multiplier' => 1.0,
                'points_per_currency' => 1,
                'color' => '#CD7F32',
                'benefits' => 'Standard earn rate, birthday bonus',
            ],
            [
                'name' => 'Silver',
                'slug' => 'silver',
                'min_points' => 1000,
                'multiplier' => 1.2,
                'points_per_currency' => 1,
                'color' => '#C0C0C0',
                'benefits' => '20% bonus points, priority check-in, late checkout on request',
            ],
            [
                'name' => 'Gold',
                'slug' => 'gold',
                'min_points' => 5000,
                'multiplier' => 1.5,
                'points_per_currency' => 2,
                'color' => '#FFD700',
                'benefits' => '50% bonus points, complimentary breakfast, late checkout guaranteed, room upgrade on availability',
            ],
            [
                'name' => 'Platinum',
                'slug' => 'platinum',
                'min_points' => 15000,
                'multiplier' => 2.0,
                'points_per_currency' => 3,
                'color' => '#E5E4E2',
                'benefits' => '100% bonus points, complimentary breakfast & dinner, guaranteed late checkout, room upgrade, dedicated concierge',
            ],
        ], ['slug'], ['name', 'min_points', 'multiplier', 'points_per_currency', 'color', 'benefits']);
    }
}
