<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Modules\Restaurant\Models\RestaurantSetting;

class RestaurantSettingSeeder extends Seeder
{
    private array $defaults = [
        'vat_rate' => '7.5',
        'service_charge_rate' => '0',
        'discount_limit' => '10000',
        'shift_start_time' => '06:00',
        'shift_end_time' => '22:00',
        'enable_room_service' => '0',
    ];

    public function run(): void
    {
        $propertyIds = DB::table('properties')->where('is_active', true)->pluck('id')->toArray();

        foreach ($propertyIds as $propertyId) {
            foreach ($this->defaults as $key => $value) {
                RestaurantSetting::updateOrCreate(
                    ['key' => $key, 'property_id' => $propertyId],
                    ['value' => $value]
                );
            }
        }
    }
}
