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
        $this->call(DiningSeeder::class);
        $this->call(RoomSeeder::class);
        $this->call(RoomImageSeeder::class);
    }
}
