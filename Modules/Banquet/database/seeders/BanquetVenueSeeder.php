<?php

namespace Modules\Banquet\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Banquet\Models\BanquetVenue;

class BanquetVenueSeeder extends Seeder
{
    public function run()
    {
        // 1. Create Individual Venues
        $adamawa = BanquetVenue::firstOrCreate(
            ['name' => 'Adamawa Hall'],
            [
                'capacity' => 200,
                'rate_per_hour' => 50000,
                'description' => 'Standard hall suitable for conferences.',
                'is_active' => true
            ]
        );

        $kano = BanquetVenue::firstOrCreate(
            ['name' => 'Kano Hall'],
            [
                'capacity' => 150,
                'rate_per_hour' => 40000,
                'description' => 'Medium sized hall.',
                'is_active' => true
            ]
        );

        // Create other standalone venues
        $others = ['Board Room', 'Pent House', 'Restaurant', 'Pool Party'];
        foreach ($others as $name) {
            BanquetVenue::firstOrCreate(['name' => $name], ['capacity' => 50, 'rate_per_hour' => 20000]);
        }

        // 2. Create Combined Venue (Parent)
        $combined = BanquetVenue::firstOrCreate(
            ['name' => 'Adamawa Hall + Kano Hall'],
            [
                'capacity' => 400, // Combined capacity
                'rate_per_hour' => 85000, // Discounted combined rate?
                'description' => 'Grand hall combining Adamawa and Kano halls.',
                'is_active' => true
            ]
        );

        // 3. Link them in the pivot table (This replaces the complex if/else logic)
        // If "Adamawa + Kano" is booked, it blocks "Adamawa" and "Kano"
        if ($combined->subVenues()->count() == 0) {
            $combined->subVenues()->attach([$adamawa->id, $kano->id]);
        }
    }
}
