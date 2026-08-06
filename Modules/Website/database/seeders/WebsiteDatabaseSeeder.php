<?php

namespace Modules\Website\Database\Seeders;

use Illuminate\Database\Seeder;

class WebsiteDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call(AmenitySeeder::class);
        $this->call(AddonSeeder::class);
        $this->call(DiningSeeder::class);

        // Legacy room seeder (for backward compatibility)
        // $this->call(RoomSeeder::class);
        // $this->call(RoomImageSeeder::class);

        // NEW: Room Types & Units Architecture
        $this->call(RoomTypeSeeder::class);
    }
}
