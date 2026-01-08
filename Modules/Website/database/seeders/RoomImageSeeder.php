<?php

namespace Modules\Website\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Website\Models\Room;
use Modules\Website\Models\RoomImage;

class RoomImageSeeder extends Seeder
{
    public function run()
    {
        $rooms = Room::all();

        // High-quality hotel images for the gallery
        $galleryPool = [
            'https://images.unsplash.com/photo-1584622050111-993a426fbf0a?q=80&w=800&auto=format&fit=crop', // Bathroom
            'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?q=80&w=800&auto=format&fit=crop', // Interior
            'https://images.unsplash.com/photo-1564501049412-61c2a3083791?q=80&w=800&auto=format&fit=crop', // View
            'https://images.unsplash.com/photo-1551882547-ff40c63fe5fa?q=80&w=800&auto=format&fit=crop', // Bed Detail
            'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?q=80&w=800&auto=format&fit=crop'  // Amenities
        ];

        foreach ($rooms as $room) {
            // Assign 3 random images to each room's gallery
            $randomImages = collect($galleryPool)->random(3);

            foreach ($randomImages as $url) {
                RoomImage::create([
                    'room_id' => $room->id,
                    'image_url' => $url,
                    'path' => null, // No local path for seeded unsplash images
                ]);
            }
        }
    }
}
