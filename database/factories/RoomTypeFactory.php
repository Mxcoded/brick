<?php

namespace Database\Factories;

use App\Models\RoomType;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomTypeFactory extends Factory
{
    protected $model = RoomType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word.' Room',
            'slug' => fake()->unique()->slug(1),
            'price' => fake()->randomFloat(2, 5000, 100000),
            'capacity' => fake()->numberBetween(1, 4),
            'is_active' => true,
        ];
    }
}
