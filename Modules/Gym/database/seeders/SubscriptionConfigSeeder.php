<?php

namespace Modules\Gym\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Gym\Models\SubscriptionConfig;

class SubscriptionConfigSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SubscriptionConfig::updateOrCreate(
            [], // Assuming only one configuration record
            [
                'monthly_fee' => 5000.00,
                'quarterly_fee' => 14000.00,
                'six_months_fee' => 25000.00,
                'yearly_fee' => 45000.00,
                'session_fee' => 1000.00,
            ]
        );
    }
}
