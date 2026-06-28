<?php

namespace Modules\Frontdeskcrm\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Frontdeskcrm\Models\NightAudit;

class NightAuditFactory extends Factory
{
    protected $model = NightAudit::class;

    public function definition(): array
    {
        return [
            'audit_date' => fake()->unique()->date('Y-m-d', 'yesterday'),
            'status' => 'open',
            'started_at' => now(),
            'started_by' => User::factory(),
            'total_rooms' => fake()->numberBetween(10, 100),
        ];
    }

    public function completed(): static
    {
        return $this->state(fn() => [
            'status' => 'completed',
            'completed_at' => now(),
            'completed_by' => User::factory(),
            'checked_in_count' => fake()->numberBetween(5, 50),
            'occupancy_count' => fake()->numberBetween(5, 80),
            'occupancy_percentage' => fake()->randomFloat(2, 20, 100),
            'room_revenue' => fake()->randomFloat(2, 50000, 500000),
            'total_revenue' => fake()->randomFloat(2, 100000, 1000000),
            'total_payments' => fake()->randomFloat(2, 50000, 500000),
        ]);
    }
}
