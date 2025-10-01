<?php

namespace Modules\Frontdeskcrm\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Frontdeskcrm\Models\BookingSource;

class BookingSourcesSeeder extends Seeder
{
    public function run(): void
    {
        $sources = [
            ['name' => 'Direct Walk-in', 'description' => 'In-person at front desk', 'type' => 'offline', 'commission_rate' => 0],
            ['name' => 'Website', 'description' => 'Hotel website bookings', 'type' => 'online', 'commission_rate' => 0],
            ['name' => 'Travel Agent', 'description' => 'Agency partnerships', 'type' => 'partner', 'commission_rate' => 10.00],
            ['name' => 'OTA', 'description' => 'Online Travel Agencies (e.g., Booking.com)', 'type' => 'online', 'commission_rate' => 15.00],
            ['name' => 'Referral', 'description' => 'Guest/partner referrals', 'type' => 'offline', 'commission_rate' => 0],
        ];

        foreach ($sources as $source) {
            BookingSource::create($source);
        }
    }
}
