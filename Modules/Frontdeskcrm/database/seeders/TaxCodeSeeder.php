<?php

namespace Modules\Frontdeskcrm\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Frontdeskcrm\Models\TaxCode;

class TaxCodeSeeder extends Seeder
{
    public function run(): void
    {
        $taxCodes = [
            [
                'code' => 'VAT7.5',
                'name' => 'VAT 7.5% (Exclusive)',
                'rate' => 7.50,
                'type' => 'exclusive',
                'is_active' => true,
            ],
            [
                'code' => 'VAT7.5I',
                'name' => 'VAT 7.5% (Inclusive)',
                'rate' => 7.50,
                'type' => 'inclusive',
                'is_active' => true,
            ],
            [
                'code' => 'SC10',
                'name' => 'Service Charge 10%',
                'rate' => 10.00,
                'type' => 'exclusive',
                'is_active' => true,
            ],
            [
                'code' => 'NHIS',
                'name' => 'NHIS Levy 1%',
                'rate' => 1.00,
                'type' => 'inclusive',
                'is_active' => true,
            ],
            [
                'code' => 'EXEMPT',
                'name' => 'Exempt (No Tax)',
                'rate' => 0.00,
                'type' => 'exclusive',
                'is_active' => true,
            ],
        ];

        foreach ($taxCodes as $taxCode) {
            TaxCode::updateOrCreate(
                ['code' => $taxCode['code']],
                $taxCode
            );
        }
    }
}
