<?php

namespace Modules\Website\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Website\Models\Room; // Ensure this matches your model namespace
use Illuminate\Support\Str;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Clear existing rooms to prevent duplicates during refresh
        // Room::truncate(); // Uncomment if you want to wipe data every seed

        $rooms = [
            [
                'name' => 'Deluxe King Room',
                'price' => 150000.00,
                'capacity' => 2,
                'size' => '35 sqm',
                'bed_type' => 'King Size',
                'description' => 'Experience ultimate comfort in our Deluxe King Room, featuring modern decor, a spacious work desk, and a marble bathroom with a rain shower. Perfect for business travelers and couples.',
                'amenities' => ['Free Wi-Fi', 'Smart TV', 'Mini Bar', 'Coffee Maker', 'Safe', 'Room Service'],
                'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', // Placeholder
                'is_featured' => false,
                'status' => 'available',
            ],
            [
                'name' => 'Executive Suite',
                'price' => 250000.00,
                'capacity' => 3,
                'size' => '55 sqm',
                'bed_type' => 'King Size + Sofa Bed',
                'description' => 'Upgrade to our Executive Suite for breathtaking city views and a separate living area. Includes access to the Executive Lounge with complimentary breakfast and evening cocktails.',
                'amenities' => ['City View', 'Lounge Access', 'Bathtub', 'Workstation', 'High-Speed Internet', 'Premium Toiletries'],
                'video_url' => null,
                'is_featured' => true,
                'status' => 'available',
            ],
            [
                'name' => 'Presidential Penthouse',
                'price' => 850000.00,
                'capacity' => 4,
                'size' => '120 sqm',
                'bed_type' => '2 King Beds',
                'description' => 'The epitome of luxury. Our Presidential Penthouse offers a private terrace, dining room for six, private jacuzzi, and 24-hour butler service. Designed for royalty.',
                'amenities' => ['Private Terrace', 'Jacuzzi', 'Butler Service', 'Dining Area', 'Private Check-in', 'Welcome Champagne'],
                'video_url' => null,
                'is_featured' => true,
                'status' => 'available',
            ],
            [
                'name' => 'Twin Standard Room',
                'price' => 120000.00,
                'capacity' => 2,
                'size' => '30 sqm',
                'bed_type' => '2 Twin Beds',
                'description' => 'Ideal for friends or colleagues, providing two comfortable twin beds and all essential modern amenities for a relaxing stay.',
                'amenities' => ['Free Wi-Fi', 'Flat Screen TV', 'Air Conditioning', 'Tea/Coffee Maker'],
                'video_url' => null,
                'is_featured' => false,
                'status' => 'available',
            ],
        ];

        foreach ($rooms as $roomData) {
            // Check if room exists by name to avoid duplicates
            $existing = Room::where('name', $roomData['name'])->first();

            if (!$existing) {
                Room::create([
                    'name' => $roomData['name'],
                    'slug' => Str::slug($roomData['name']), // Auto-generate slug
                    'price' => $roomData['price'],
                    'capacity' => $roomData['capacity'],
                    'size' => $roomData['size'],
                    'bed_type' => $roomData['bed_type'],
                    'description' => $roomData['description'],
                    'amenities' => $roomData['amenities'], // Model cast handles JSON conversion
                    'video_url' => $roomData['video_url'],
                    'is_featured' => $roomData['is_featured'],
                    'status' => $roomData['status'],
                ]);
            }
        }
    }
}
