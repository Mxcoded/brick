<?php

namespace Modules\Website\Database\Seeders;

use App\Models\Property;
use App\Models\RoomType;
use App\Models\RoomUnit;
use App\Models\Scopes\PropertyScope;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Modules\Website\Models\Amenity;

class RoomTypeSeeder extends Seeder
{
    public function run()
    {
        $amenityIds = Amenity::pluck('id')->toArray();

        $asokoro = Property::where('slug', 'asokoro')->firstOrFail();
        $wuse = Property::where('slug', 'wuse')->firstOrFail();

        // Reassign orphaned room types (pre-property migration) to Asokoro
        RoomType::withoutGlobalScope(PropertyScope::class)
            ->whereNull('property_id')
            ->update(['property_id' => $asokoro->id]);
        RoomUnit::withoutGlobalScope(PropertyScope::class)
            ->whereNull('property_id')
            ->update(['property_id' => $asokoro->id]);

        // ==========================================
        // ASOKORO ROOM TYPES
        // ==========================================
        $this->seedPropertyRooms($asokoro->id, $amenityIds, [
            // Studio Suites
            [
                'name' => 'Standard Studio Suite',
                'price' => 290000, 'capacity' => 2, 'size' => '39.8 - 47.1 sqm',
                'bed_type' => 'King Size',
                'description' => 'A cozy studio suite featuring a comfortable sitting area, bedroom space, modern bathroom with standing shower. Perfect for business travelers or couples seeking a compact yet comfortable stay.',
                'image_url' => 'https://images.unsplash.com/photo-1505691938895-1758d7eaa511?q=80&w=800&auto=format&fit=crop',
                'display_order' => 1,
                'units' => [
                    ['room_number' => 'AP 8', 'floor' => 'Block B'],
                    ['room_number' => 'AP 10', 'floor' => 'Block B'],
                ],
            ],
            [
                'name' => 'Deluxe Studio Suite',
                'price' => 320000, 'capacity' => 2, 'size' => '48.6 - 48.7 sqm',
                'bed_type' => 'King Size',
                'description' => 'An upgraded studio suite with a spacious sitting area, comfortable bedroom, well-appointed bathroom with standing shower.',
                'image_url' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?q=80&w=800&auto=format&fit=crop',
                'display_order' => 2,
                'units' => [
                    ['room_number' => 'AP 9', 'floor' => 'Block B'],
                    ['room_number' => 'AP 11', 'floor' => 'Block B'],
                ],
            ],
            [
                'name' => 'Executive Studio Suite',
                'price' => 360000, 'capacity' => 2, 'size' => '61.8 sqm',
                'bed_type' => 'King Size',
                'description' => 'A spacious studio suite featuring a generous living area, comfortable bedroom, modern bathroom with standing shower, and a private balcony.',
                'image_url' => 'https://images.unsplash.com/photo-1631049307264-da039d59562a?q=80&w=800&auto=format&fit=crop',
                'display_order' => 3,
                'units' => [
                    ['room_number' => 'AP 4', 'floor' => 'Block A'],
                ],
            ],
            [
                'name' => 'Executive Studio Apartment',
                'price' => 410000, 'capacity' => 2, 'size' => '51.4 sqm',
                'bed_type' => 'King Size',
                'description' => 'A well-appointed studio apartment featuring a sitting area, comfortable bedroom, kitchenette for light cooking, modern bathroom with standing shower.',
                'image_url' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?q=80&w=800&auto=format&fit=crop',
                'display_order' => 4,
                'units' => [
                    ['room_number' => 'AP 16', 'floor' => 'Block B'],
                    ['room_number' => 'AP 18', 'floor' => 'Block B'],
                ],
            ],
            // One Bedroom Suites
            [
                'name' => 'Special Needs One Bedroom Suite',
                'price' => 360000, 'capacity' => 2, 'size' => '49.5 sqm',
                'bed_type' => 'King Size',
                'description' => 'A thoughtfully designed accessible suite with separate living room, bedroom, sitting balcony, and bathroom with standing shower.',
                'image_url' => 'https://images.unsplash.com/photo-1611892440504-42a792e24d32?q=80&w=800&auto=format&fit=crop',
                'display_order' => 5,
                'units' => [
                    ['room_number' => 'AP 1', 'floor' => 'Block A'],
                ],
            ],
            [
                'name' => 'Deluxe One Bedroom Suite',
                'price' => 410000, 'capacity' => 2, 'size' => '60.9 sqm',
                'bed_type' => 'King Size',
                'description' => 'An elegant suite featuring a separate living room, comfortable bedroom, dining area, bathroom with standing shower, and views of the art gallery.',
                'image_url' => 'https://images.unsplash.com/photo-1566665797739-1674de7a421a?q=80&w=800&auto=format&fit=crop',
                'display_order' => 6,
                'units' => [
                    ['room_number' => 'AP 5', 'floor' => 'Block A'],
                ],
            ],
            [
                'name' => 'Superior One Bedroom Suite',
                'price' => 430000, 'capacity' => 2, 'size' => '76 sqm',
                'bed_type' => 'King Size',
                'description' => 'A premium suite with separate living room, spacious bedroom, reading table, and modern bathroom with standing shower.',
                'image_url' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?q=80&w=800&auto=format&fit=crop',
                'display_order' => 7,
                'units' => [
                    ['room_number' => 'AP 2', 'floor' => 'Block A'],
                ],
            ],
            // One Bedroom Apartments
            [
                'name' => 'Standard One Bedroom Apartment',
                'price' => 430000, 'capacity' => 2, 'size' => '66.9 sqm',
                'bed_type' => 'King Size',
                'description' => 'A comfortable apartment with separate living room, bedroom, fully-equipped kitchenette, and bathroom with standing shower.',
                'image_url' => 'https://images.unsplash.com/photo-1584132967334-10e028bd69f7?q=80&w=800&auto=format&fit=crop',
                'display_order' => 8,
                'units' => [
                    ['room_number' => 'AP 6', 'floor' => 'Block A'],
                ],
            ],
            [
                'name' => 'Premium One Bedroom Apartment',
                'price' => 430000, 'capacity' => 2, 'size' => '51.4 - 83.1 sqm',
                'bed_type' => 'King Size',
                'description' => 'A premium apartment featuring separate living room, kitchenette, dining area, sitting balcony, bedroom, bathroom with standing shower.',
                'image_url' => 'https://images.unsplash.com/photo-1560448204-e02f11c3d0e2?q=80&w=800&auto=format&fit=crop',
                'is_featured' => true, 'display_order' => 9,
                'units' => [
                    ['room_number' => 'AP 17', 'floor' => 'Block B'],
                    ['room_number' => 'AP 19', 'floor' => 'Block B'],
                ],
            ],
            [
                'name' => 'Executive One Bedroom Apartment',
                'price' => 480000, 'capacity' => 2, 'size' => '109.3 sqm',
                'bed_type' => 'King Size',
                'description' => 'A luxurious apartment with separate spacious living room, kitchenette, bedroom, sitting balcony, bathroom with standing shower, and an ante room.',
                'image_url' => 'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?q=80&w=800&auto=format&fit=crop',
                'is_featured' => true, 'display_order' => 10,
                'units' => [
                    ['room_number' => 'AP 3', 'floor' => 'Block A'],
                ],
            ],
            [
                'name' => 'Luxury One Bedroom Apartment',
                'price' => 480000, 'capacity' => 2, 'size' => '115.4 sqm',
                'bed_type' => 'King Size',
                'description' => 'Our most spacious one-bedroom option featuring a separate living room, fully-equipped kitchenette, bedroom, sitting balcony, and a bathroom with both standing shower and bathtub.',
                'image_url' => 'https://images.unsplash.com/photo-1590490359683-658d3d23f972?q=80&w=800&auto=format&fit=crop',
                'is_featured' => true, 'display_order' => 11,
                'units' => [
                    ['room_number' => 'AP 7', 'floor' => 'Block A'],
                ],
            ],
            // Two Bedroom Apartment
            [
                'name' => 'Executive Two Bedroom Apartment',
                'price' => 540000, 'capacity' => 4, 'size' => '138 sqm',
                'bed_type' => 'King Size + Twin',
                'description' => 'A spacious family apartment featuring separate living room, dining area, kitchenette, sitting balcony, master and secondary bedrooms.',
                'image_url' => 'https://images.unsplash.com/photo-1554995207-c18c203602cb?q=80&w=800&auto=format&fit=crop',
                'is_featured' => true, 'display_order' => 12,
                'units' => [
                    ['room_number' => 'AP 12', 'floor' => 'Block B'],
                    ['room_number' => 'AP 13', 'floor' => 'Block B'],
                    ['room_number' => 'AP 14', 'floor' => 'Block B'],
                    ['room_number' => 'AP 15', 'floor' => 'Block B'],
                ],
            ],
            // Duplex
            [
                'name' => 'One Bedroom Duplex',
                'price' => 500000, 'capacity' => 2, 'size' => '89.9 sqm',
                'bed_type' => 'King Size',
                'description' => 'A unique two-level duplex with living room, dining area, and kitchenette on the ground floor; bedroom, reading table, and bathroom upstairs.',
                'image_url' => 'https://images.unsplash.com/photo-1512918728675-ed5a9ecdebfd?q=80&w=800&auto=format&fit=crop',
                'is_featured' => true, 'display_order' => 13,
                'units' => [
                    ['room_number' => 'T1', 'floor' => 'Block C'],
                    ['room_number' => 'T2', 'floor' => 'Block C'],
                ],
            ],
            // Presidential
            [
                'name' => 'Presidential Suite (Penthouse)',
                'price' => 2000000, 'capacity' => 4, 'size' => '270 sqm',
                'bed_type' => 'King Size + Queen',
                'description' => 'The crown jewel. This penthouse features a separate living room, dining area, open-plan kitchen, spacious terrace with garden, mini office, master and secondary bedrooms, and panoramic 180° city views.',
                'image_url' => 'https://images.unsplash.com/photo-1590490359683-658d3d23f972?q=80&w=800&auto=format&fit=crop',
                'is_featured' => true, 'display_order' => 14,
                'units' => [
                    ['room_number' => 'Penthouse', 'floor' => 'Rooftop'],
                ],
            ],
        ]);

        // ==========================================
        // WUSE ROOM TYPES
        // ==========================================
        $this->seedPropertyRooms($wuse->id, $amenityIds, [
            [
                'name' => 'Standard Studio Suite',
                'price' => 270000, 'capacity' => 2, 'size' => '35 sqm',
                'bed_type' => 'Queen Size',
                'description' => 'A comfortable studio suite with a sitting area, bedroom space, and modern bathroom. Perfect for business travelers.',
                'image_url' => 'https://images.unsplash.com/photo-1505691938895-1758d7eaa511?q=80&w=800&auto=format&fit=crop',
                'display_order' => 1,
                'units' => [
                    ['room_number' => 'W-101', 'floor' => '1st Floor'],
                    ['room_number' => 'W-102', 'floor' => '1st Floor'],
                ],
            ],
            [
                'name' => 'Deluxe Room',
                'price' => 310000, 'capacity' => 2, 'size' => '42 sqm',
                'bed_type' => 'King Size',
                'description' => 'A well-appointed deluxe room with a comfortable seating area, workspace, and bathroom with shower.',
                'image_url' => 'https://images.unsplash.com/photo-1618773928121-c32242e63f39?q=80&w=800&auto=format&fit=crop',
                'display_order' => 2,
                'units' => [
                    ['room_number' => 'W-201', 'floor' => '2nd Floor'],
                    ['room_number' => 'W-202', 'floor' => '2nd Floor'],
                    ['room_number' => 'W-203', 'floor' => '2nd Floor'],
                ],
            ],
            [
                'name' => 'Executive Room',
                'price' => 350000, 'capacity' => 2, 'size' => '50 sqm',
                'bed_type' => 'King Size',
                'description' => 'An executive room with a separate sitting area, work desk, and bathroom with standing shower.',
                'image_url' => 'https://images.unsplash.com/photo-1631049307264-da039d59562a?q=80&w=800&auto=format&fit=crop',
                'is_featured' => true, 'display_order' => 3,
                'units' => [
                    ['room_number' => 'W-301', 'floor' => '3rd Floor'],
                    ['room_number' => 'W-302', 'floor' => '3rd Floor'],
                ],
            ],
            [
                'name' => 'Junior Suite',
                'price' => 390000, 'capacity' => 2, 'size' => '58 sqm',
                'bed_type' => 'King Size',
                'description' => 'A stylish junior suite with a separate living area, bedroom, and bathroom with bathtub.',
                'image_url' => 'https://images.unsplash.com/photo-1582719478250-c89cae4dc85b?q=80&w=800&auto=format&fit=crop',
                'is_featured' => true, 'display_order' => 4,
                'units' => [
                    ['room_number' => 'W-401', 'floor' => '4th Floor'],
                ],
            ],
            [
                'name' => 'One Bedroom Apartment',
                'price' => 420000, 'capacity' => 2, 'size' => '70 sqm',
                'bed_type' => 'King Size',
                'description' => 'A spacious apartment with separate living room, fully-equipped kitchenette, bedroom, and bathroom with shower.',
                'image_url' => 'https://images.unsplash.com/photo-1596394516093-501ba68a0ba6?q=80&w=800&auto=format&fit=crop',
                'is_featured' => true, 'display_order' => 5,
                'units' => [
                    ['room_number' => 'W-501', 'floor' => '5th Floor'],
                    ['room_number' => 'W-502', 'floor' => '5th Floor'],
                ],
            ],
            [
                'name' => 'Executive Two Bedroom Apartment',
                'price' => 520000, 'capacity' => 4, 'size' => '120 sqm',
                'bed_type' => 'King Size + Single',
                'description' => 'A family-friendly two-bedroom apartment with living room, kitchenette, master and guest bedrooms.',
                'image_url' => 'https://images.unsplash.com/photo-1554995207-c18c203602cb?q=80&w=800&auto=format&fit=crop',
                'is_featured' => true, 'display_order' => 6,
                'units' => [
                    ['room_number' => 'W-601', 'floor' => '6th Floor'],
                    ['room_number' => 'W-602', 'floor' => '6th Floor'],
                ],
            ],
        ]);

        $this->command->info('✅ Room types and units seeded successfully!');
        $this->command->info('   - '.RoomType::withoutGlobalScope(PropertyScope::class)->count().' room types created');
        $this->command->info('   - '.RoomUnit::withoutGlobalScope(PropertyScope::class)->count().' room units created');
    }

    private function seedPropertyRooms(int $propertyId, array $amenityIds, array $roomTypeData): void
    {
        foreach ($roomTypeData as $data) {
            $units = $data['units'] ?? [];
            unset($data['units']);

            $roomType = RoomType::withoutGlobalScope(PropertyScope::class)
                ->updateOrCreate(
                    ['name' => $data['name'], 'property_id' => $propertyId],
                    array_merge($data, [
                        'slug' => Str::slug($data['name']),
                        'property_id' => $propertyId,
                        'is_active' => $data['is_active'] ?? true,
                        'is_featured' => $data['is_featured'] ?? false,
                    ])
                );

            if (! empty($amenityIds)) {
                $randomAmenities = array_slice($amenityIds, 0, rand(4, min(8, count($amenityIds))));
                $roomType->amenities()->sync($randomAmenities);
            }

            foreach ($units as $unitData) {
                RoomUnit::withoutGlobalScope(PropertyScope::class)
                    ->updateOrCreate(
                        ['room_number' => $unitData['room_number']],
                        [
                            'room_type_id' => $roomType->id,
                            'property_id' => $propertyId,
                            'room_number' => $unitData['room_number'],
                            'floor' => $unitData['floor'],
                            'status' => 'available',
                        ]
                    );
            }
        }
    }
}
