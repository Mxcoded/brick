<?php

namespace Modules\Frontdeskcrm\Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Frontdeskcrm\Models\Channel;

class ChannelFactory extends Factory
{
    protected $model = Channel::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->company.' (OTA)',
            'provider' => fake()->randomElement(['booking.com', 'expedia', 'direct']),
            'api_endpoint' => 'https://api.'.fake()->domainName.'/v1',
            'api_key' => fake()->sha256,
            'is_active' => true,
            'created_by' => User::factory(),
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn () => ['is_active' => false]);
    }
}
