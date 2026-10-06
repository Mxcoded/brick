<?php

namespace Modules\Frontdeskcrm\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Frontdeskcrm\Models\FrontdeskSetting;

class FrontdeskSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // Off by default: a scheduler posting money needs to be switched on
            // deliberately, not switch itself on at first deploy.
            'auto_charge_enabled' => '0',
            'auto_charge_time' => '02:00',
        ];

        foreach ($defaults as $key => $value) {
            FrontdeskSetting::setValue($key, $value);
        }
    }
}
