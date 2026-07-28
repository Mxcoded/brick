<?php

namespace Modules\Frontdeskcrm\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Frontdeskcrm\Models\GuestType;

class GuestTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'name' => 'Individual',
                'description' => 'Standard individual guest — pay at hotel, no corporate agreement',
                'color' => '#6c757d',
                'discount_rate' => 0,
                'valid_from' => null,
                'valid_to' => null,
            ],
            [
                'name' => 'Corporate',
                'description' => 'Business traveler with a corporate rate agreement',
                'color' => '#007bff',
                'discount_rate' => 5.00,
                'valid_from' => now()->startOfYear(),
                'valid_to' => now()->endOfYear(),
            ],
            [
                'name' => 'Leisure',
                'description' => 'Vacationer / leisure traveler',
                'color' => '#28a745',
                'discount_rate' => 0,
                'valid_from' => null,
                'valid_to' => null,
            ],
            [
                'name' => 'VIP',
                'description' => 'Loyalty or high-value guest — priority service',
                'color' => '#ffc107',
                'discount_rate' => 10.00,
                'valid_from' => null,
                'valid_to' => null,
            ],
            [
                'name' => 'OTA',
                'description' => 'Online Travel Agency (Booking.com, Expedia, etc.)',
                'color' => '#17a2b8',
                'discount_rate' => 0,
                'valid_from' => now()->startOfYear(),
                'valid_to' => now()->endOfYear(),
            ],
            [
                'name' => 'Other',
                'description' => 'Uncategorized / miscellaneous',
                'color' => '#dc3545',
                'discount_rate' => 0,
                'valid_from' => null,
                'valid_to' => null,
            ],
        ];

        foreach ($types as $type) {
            GuestType::updateOrCreate(
                ['name' => $type['name']],
                $type
            );
        }
    }
}
