<?php

namespace Modules\Website\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Website\Models\Room;
use Modules\Website\Models\Amenity;

class RoomSeeder extends Seeder
{
    public function run()
    {
        $room = Room::create([
            'name' => 'Deluxe Suite',
            'description' => 'A spacious suite with stunning views.',
            'price_per_night' => 450,
            'image' => 'images/deluxe-suite.jpg',
            'featured' => true,
            'size' => 600,
            'capacity' => 2,
        ]);

        $amenity1 = Amenity::create(['name' => 'King Bed', 'icon' => 'fas fa-bed']);
        $amenity2 = Amenity::create(['name' => 'Private Balcony', 'icon' => 'fas fa-door-open']);

        $room->amenities()->attach([$amenity1->id, $amenity2->id]);
    }
}
