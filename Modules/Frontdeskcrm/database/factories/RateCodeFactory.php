<?php

namespace Modules\Frontdeskcrm\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Frontdeskcrm\Models\RateCode;

class RateCodeFactory extends Factory
{
    protected $model = RateCode::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word.' Rate',
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'description' => fake()->sentence,
            'is_active' => true,
            'sort_order' => fake()->numberBetween(0, 100),
        ];
    }
}
