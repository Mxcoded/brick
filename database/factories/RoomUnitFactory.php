<?php

namespace Database\Factories;

use App\Models\RoomType;
use App\Models\RoomUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoomUnitFactory extends Factory
{
    protected $model = RoomUnit::class;

    public function definition(): array
    {
        return [
            'room_type_id' => RoomType::factory(),
            'room_number' => fake()->unique()->numerify('###'),
            'floor' => fake()->numberBetween(1, 10),
            'status' => 'available',
            'cleaning_status' => 'clean',
        ];
    }

    public function occupied(): static
    {
        return $this->state(fn () => ['status' => 'occupied']);
    }

    public function available(): static
    {
        return $this->state(fn () => ['status' => 'available']);
    }

    public function dirty(): static
    {
        return $this->state(fn () => ['status' => 'dirty']);
    }
}
