<?php

namespace Modules\Frontdeskcrm\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Frontdeskcrm\Models\ChargeType;

class ChargeTypeFactory extends Factory
{
    protected $model = ChargeType::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->word.' Charge',
            'code' => strtoupper(fake()->unique()->lexify('???')),
            'description' => fake()->sentence,
            'is_active' => true,
        ];
    }

    public function roomNight(): static
    {
        return $this->state(fn () => [
            'name' => 'Room Night Charge',
            'code' => 'ROOM_NIGHT',
        ]);
    }

    public function roomUpgrade(): static
    {
        return $this->state(fn () => [
            'name' => 'Room Upgrade',
            'code' => 'room_upgrade',
        ]);
    }
}
