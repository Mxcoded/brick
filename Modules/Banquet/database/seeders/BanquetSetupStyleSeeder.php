<?php

namespace Modules\Banquet\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Banquet\Models\BanquetSetupStyle;

class BanquetSetupStyleSeeder extends Seeder
{
    public function run()
    {
        $styles = [
            'Theater Style',
            'Classroom Style',
            'Boardroom Style',
            'U-Shape',
            'Banquet Style',
            'Reception',
            'Cabaret'
        ];

        foreach ($styles as $style) {
            BanquetSetupStyle::firstOrCreate(['name' => $style]);
        }
    }
}
