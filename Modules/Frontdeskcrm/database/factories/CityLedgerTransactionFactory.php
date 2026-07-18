<?php

namespace Modules\Frontdeskcrm\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Frontdeskcrm\Models\CityLedgerTransaction;
use Modules\Frontdeskcrm\Models\CorporateAccount;

class CityLedgerTransactionFactory extends Factory
{
    protected $model = CityLedgerTransaction::class;

    public function definition(): array
    {
        $balance = fake()->randomFloat(2, 5000, 200000);

        return [
            'corporate_account_id' => CorporateAccount::factory(),
            'type' => fake()->randomElement(['charge', 'payment']),
            'amount' => $balance,
            'balance_before' => 0,
            'balance_after' => $balance,
            'description' => fake()->sentence,
            'created_by' => User::factory(),
        ];
    }

    public function charge(): static
    {
        return $this->state(fn () => ['type' => 'charge']);
    }

    public function payment(): static
    {
        return $this->state(fn () => ['type' => 'payment']);
    }
}
