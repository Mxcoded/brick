<?php

namespace Database\Factories;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class GuestFactory extends Factory
{
    protected $model = \Modules\Frontdeskcrm\Models\Guest::class;

    public function definition(): array
    {
        return [
            'full_name' => fake()->name,
            'email' => fake()->unique()->safeEmail,
            'contact_number' => fake()->phoneNumber,
            'nationality' => fake()->countryCode,
            'home_address' => fake()->address,
            'title' => fake()->randomElement(['Mr.', 'Ms.', 'Mrs.', 'Dr.']),
        ];
    }
}
