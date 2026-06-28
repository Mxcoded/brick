<?php

namespace Modules\Frontdeskcrm\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Frontdeskcrm\Models\ChargeType;
use Modules\Frontdeskcrm\Models\FolioCharge;
use Modules\Frontdeskcrm\Models\Registration;

class FolioChargeFactory extends Factory
{
    protected $model = FolioCharge::class;

    public function definition(): array
    {
        return [
            'registration_id' => Registration::factory(),
            'charge_type_id' => ChargeType::factory(),
            'description' => fake()->sentence(3),
            'quantity' => 1,
            'unit_price' => fake()->randomFloat(2, 1000, 20000),
            'amount' => 0,
            'posted_by' => User::factory(),
        ];
    }

    public function configure(): static
    {
        return $this->afterMaking(function (FolioCharge $charge) {
            if (! $charge->amount || $charge->amount == 0) {
                $charge->amount = $charge->quantity * $charge->unit_price;
            }
        });
    }
}
