<?php

namespace Modules\Frontdeskcrm\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Frontdeskcrm\Models\NightAudit;
use Modules\Frontdeskcrm\Models\NightAuditLog;

class NightAuditLogFactory extends Factory
{
    protected $model = NightAuditLog::class;

    public function definition(): array
    {
        return [
            'night_audit_id' => NightAudit::factory(),
            'action' => fake()->randomElement(['charge_posted', 'payment_recorded', 'room_closed']),
            'description' => fake()->sentence,
            'amount' => fake()->randomFloat(2, 1000, 50000),
        ];
    }
}
