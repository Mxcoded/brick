<?php

namespace Modules\Gym\Database\Seeders;

use Illuminate\Database\Seeder;

class GymDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call (SubscriptionConfigSeeder::class);
    }
}
