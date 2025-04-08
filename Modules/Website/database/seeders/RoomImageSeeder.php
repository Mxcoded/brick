<?php

namespace Modules\Website\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Website\Models\Room;
use Modules\Website\Models\RoomImage;

class RoomImageSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $room = Room::find(1); // Or create a new one
        RoomImage::create(['room_id' => $room->id, 'path' => 'images/room1-pic1.jpg', 'type' => 'image', 'order' => 1, 'caption' => 'Living Area']);
        RoomImage::create(['room_id' => $room->id, 'path' => 'images/room1-pic2.jpg', 'type' => 'image', 'order' => 2, 'caption' => 'Bedroom']);
        RoomImage::create(['room_id' => $room->id, 'path' => 'videos/room1-tour.mp4', 'type' => 'video', 'order' => 3, 'caption' => 'Room Tour']);
    }
}
