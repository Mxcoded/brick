<?php

namespace Modules\Website\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Website\Models\Amenity;

class AmenitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $amenities = [
            ['name' => 'Free Wi-Fi', 'icon' => 'fas fa-wifi'],
            ['name' => 'Swimming Pool', 'icon' => 'fas fa-swimming-pool'],
            ['name' => 'Fitness Center', 'icon' => 'fas fa-dumbbell'],
            ['name' => 'Spa & Wellness', 'icon' => 'fas fa-spa'],
            ['name' => 'Restaurant', 'icon' => 'fas fa-utensils'],
            ['name' => '24/7 Room Service', 'icon' => 'fas fa-concierge-bell'],
            ['name' => 'Bar & Lounge', 'icon' => 'fas fa-cocktail'],
            ['name' => 'Free Parking', 'icon' => 'fas fa-parking'],
            ['name' => 'Air Conditioning', 'icon' => 'fas fa-snowflake'],
            ['name' => 'Smart TV', 'icon' => 'fas fa-tv'],
            ['name' => 'Mini Bar', 'icon' => 'fas fa-wine-glass'],
            ['name' => 'Ocean View', 'icon' => 'fas fa-water'],
            ['name' => 'Conference Room', 'icon' => 'fas fa-briefcase'],
            ['name' => 'Laundry Service', 'icon' => 'fas fa-tshirt'],
            ['name' => 'Airport Shuttle', 'icon' => 'fas fa-shuttle-van'],
        ];

        foreach ($amenities as $amenity) {
            Amenity::firstOrCreate(
                ['name' => $amenity['name']], // Check by name to avoid duplicates
                ['icon' => $amenity['icon']]
            );
        }
    }
}
