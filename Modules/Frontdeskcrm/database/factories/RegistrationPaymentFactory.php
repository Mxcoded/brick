<?php

namespace Modules\Frontdeskcrm\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationPayment;

class RegistrationPaymentFactory extends Factory
{
    protected $model = RegistrationPayment::class;

    public function definition(): array
    {
        return [
            'registration_id' => Registration::factory(),
            'amount' => fake()->randomFloat(2, 1000, 50000),
            'payment_method' => fake()->randomElement(['cash', 'card', 'bank_transfer', 'pos']),
            'payment_type' => 'payment',
            'payment_date' => now(),
            'received_by' => User::factory(),
        ];
    }

    public function deposit(): static
    {
        return $this->state(fn() => ['payment_type' => 'deposit']);
    }

    public function securityDeposit(): static
    {
        return $this->state(fn() => [
            'payment_type' => 'security_deposit',
            'payment_category' => 'security_deposit',
        ]);
    }
}
