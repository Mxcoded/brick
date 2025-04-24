<?php

namespace Modules\Website\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Website\Models\Dining;

class DiningSeeder extends Seeder
{
    public function run()
    {
        Dining::create([
            'name' => 'The Grand Dining Room',
            'description' => 'Experience fine dining with a menu curated by our Michelin-starred chef.',
            'image' => asset('images/dining-1.jpg'),
            'opening_hours' => '6:00 PM - 10:00 PM',
            'cuisine_type' => 'French',
            'dress_code' => 'Formal',
            'menu_link' => '/menus/grand-dining-room',
            'is_featured' => true,
        ]);

        Dining::create([
            'name' => 'Sky Lounge',
            'description' => 'Enjoy panoramic views with cocktails and light bites.',
            'image' => asset('images/dining-2.jpg'),
            'opening_hours' => '4:00 PM - 12:00 AM',
            'cuisine_type' => 'International',
            'dress_code' => 'Smart Casual',
            'menu_link' => '/menus/sky-lounge',
            'is_featured' => true,
        ]);

        Dining::create([
            'name' => 'Poolside Cafe',
            'description' => 'Relax by the pool with refreshing drinks and healthy snacks.',
            'image' => asset('images/dining-3.jpg'),
            'opening_hours' => '10:00 AM - 6:00 PM',
            'cuisine_type' => 'Light Fare',
            'dress_code' => 'Casual',
            'menu_link' => '/menus/poolside-cafe',
            'is_featured' => true,
        ]);
    }
}
