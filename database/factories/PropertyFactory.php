<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PropertyFactory extends Factory
{
    protected $model = \App\Models\Property::class;

    public function definition(): array
    {
        $name = fake()->unique()->company.' Hotel';

        return [
            'name' => $name,
            'slug' => Str::slug($name),
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'address' => fake()->address,
            'city' => fake()->city,
            'country' => fake()->country,
            'contact_email' => fake()->companyEmail,
            'contact_phone' => fake()->phoneNumber,
            'is_active' => true,
            'is_headquarters' => false,
            'settings' => json_encode(['tax_rate' => 7.5]),
        ];
    }

    public function headquarters(): static
    {
        return $this->state(fn() => ['is_headquarters' => true]);
    }
}
