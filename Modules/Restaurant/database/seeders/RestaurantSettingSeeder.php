<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Restaurant\Models\RestaurantSetting;

class RestaurantSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            'vat_rate' => '7.5',
            'service_charge_rate' => '0',
            'discount_limit' => '10000',
        ];

        foreach ($defaults as $key => $value) {
            RestaurantSetting::setValue($key, $value);
        }
    }
}
