<?php

namespace Modules\Frontdeskcrm\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Frontdeskcrm\Models\GuestType;

class GuestTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            ['name' => 'Individual', 'description' => 'Standard guest', 'color' => '#6c757d', 'discount_rate' => 0],
            ['name' => 'Corporate', 'description' => 'Business traveler', 'color' => '#007bff', 'discount_rate' => 5.00],
            ['name' => 'Leisure', 'description' => 'Vacationer', 'color' => '#28a745', 'discount_rate' => 0],
            ['name' => 'VIP', 'description' => 'Loyalty/high-value', 'color' => '#ffc107', 'discount_rate' => 10.00],
            ['name' => 'Other', 'description' => 'Uncategorized', 'color' => '#dc3545', 'discount_rate' => 0],
        ];

        foreach ($types as $type) {
            GuestType::create($type);
        }
    }
}
