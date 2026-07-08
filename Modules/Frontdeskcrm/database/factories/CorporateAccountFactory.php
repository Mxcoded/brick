<?php

namespace Modules\Frontdeskcrm\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Frontdeskcrm\Models\CityLedgerTransaction;
use Modules\Frontdeskcrm\Models\CorporateAccount;

class CorporateAccountFactory extends Factory
{
    protected $model = CorporateAccount::class;

    public function definition(): array
    {
        return [
            'company_name' => fake()->company,
            'contact_person' => fake()->name,
            'email' => fake()->companyEmail,
            'phone' => fake()->phoneNumber,
            'credit_limit' => fake()->randomFloat(2, 100000, 1000000),
            'current_balance' => 0,
            'is_active' => true,
        ];
    }
}
