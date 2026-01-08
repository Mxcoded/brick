<?php

namespace Modules\Website\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Website\Models\Room; // Ensure namespace matches your model
use Illuminate\Support\Str;

class RoomSeeder extends Seeder
{
    public function run()
    {
        // 1. Deluxe Room
        Room::create([
            'name' => 'Deluxe Ocean View',
            'slug' => 'deluxe-ocean-view',
            'price' => 75000.00,
            'capacity' => 2,
            'size' => '45 sqm',
            'bed_type' => 'King Size',
            'description' => 'Experience ultimate relaxation in our Deluxe Ocean View room. Featuring a private balcony with panoramic views of the Atlantic, a spacious workspace, and a luxury marble bathroom.',
          
            // Primary Image (Thumbnail)
            'image_url' => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?q=80&w=800&auto=format&fit=crop',
            'video_url' => 'https://www.youtube.com/watch?v=gymTeL-10pE', // Example Hotel Tour

            'status' => 'available',
            'is_featured' => true,
        ]);

        // 2. Executive Suite
        Room::create([
            'name' => 'Executive Suite',
            'slug' => 'executive-suite',
            'price' => 120000.00,
            'capacity' => 3,
            'size' => '65 sqm',
            'bed_type' => 'King + Sofa Bed',
            'description' => 'Designed for business and leisure, the Executive Suite offers a separate living area, premium soundproofing, and exclusive access to the Executive Lounge.',
          
            // Primary Image
            'image_url' => 'https://images.unsplash.com/photo-1590490360182-c33d57733427?q=80&w=800&auto=format&fit=crop',
            'video_url' => null,

            'status' => 'available',
            'is_featured' => true,
        ]);

        // 3. Family Room
        Room::create([
            'name' => 'Family Garden Room',
            'slug' => 'family-garden-room',
            'price' => 95000.00,
            'capacity' => 4,
            'size' => '55 sqm',
            'bed_type' => '2 Queen Beds',
            'description' => 'Perfect for families, this room features two queen beds, a kid-friendly layout, and direct access to the hotel gardens and pool area.',
           
            // Primary Image
            'image_url' => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?q=80&w=800&auto=format&fit=crop',
            'video_url' => null,

            'status' => 'maintenance', // Example of maintenance status
            'is_featured' => false,
        ]);

        // 4. Presidential Suite
        Room::create([
            'name' => 'Presidential Penthouse',
            'slug' => 'presidential-penthouse',
            'price' => 500000.00,
            'capacity' => 2,
            'size' => '120 sqm',
            'bed_type' => 'Emperor King',
            'description' => 'The pinnacle of luxury. Private elevator access, personal butler service, jacuzzi, and a rooftop terrace with 360-degree city views.',
         
            // Primary Image
            'image_url' => 'https://images.unsplash.com/photo-1631049307264-da0ec9d70304?q=80&w=800&auto=format&fit=crop',
            'video_url' => 'https://www.youtube.com/watch?v=sample',

            'status' => 'booked', // Example of booked status
            'is_featured' => true,
        ]);
    }
}
