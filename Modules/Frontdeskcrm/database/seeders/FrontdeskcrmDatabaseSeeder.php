<?php

namespace Modules\Frontdeskcrm\Database\Seeders;

use Illuminate\Database\Seeder;

class FrontdeskcrmDatabaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->call([
            BookingSourcesSeeder::class,
            GuestTypesSeeder::class,
            GuestsSeeder::class,
        ]);
    }
}
