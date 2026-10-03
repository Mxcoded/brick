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
            // First: the Filament PMS resources gate on this taxonomy, so the
            // permission names have to exist before anything renders.
            FrontdeskPermissionSeeder::class,
            BookingSourcesSeeder::class,
            GuestTypesSeeder::class,
            GuestsSeeder::class,
            RateAndSeasonSeeder::class,
            TaxCodeSeeder::class,
        ]);
    }
}
