<?php

namespace Modules\Frontdeskcrm\Database\Factories;

use Modules\Frontdeskcrm\Models\Guest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Modules\Frontdeskcrm\Models\Registration;

class RegistrationFactory extends Factory
{
    protected $model = Registration::class;

    public function definition(): array
    {
        $checkIn = fake()->dateTimeBetween('-7 days', 'now');
        $checkOut = (clone $checkIn)->modify('+'.fake()->numberBetween(1, 5).' days');

        return [
            'guest_id' => Guest::factory(),
            'full_name' => fake()->name,
            'contact_number' => fake()->phoneNumber,
            'email' => fake()->safeEmail,
            'check_in' => $checkIn,
            'check_out' => $checkOut,
            'no_of_guests' => fake()->numberBetween(1, 4),
            'no_of_nights' => (int) (clone $checkIn)->diff($checkOut)->days,
            'room_rate' => fake()->randomFloat(2, 5000, 50000),
            'total_amount' => 0,
            'stay_status' => 'draft_by_guest',
            'agreed_to_policies' => true,
            'registration_date' => now(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn() => ['stay_status' => 'draft_by_guest']);
    }

    public function checkedIn(): static
    {
        return $this->state(fn() => [
            'stay_status' => 'checked_in',
            'checked_in_at' => now(),
            'front_desk_agent' => fake()->name,
        ]);
    }

    public function checkedOut(): static
    {
        return $this->state(fn() => [
            'stay_status' => 'checked_out',
            'checked_in_at' => now()->subDays(2),
            'actual_checkout_at' => now(),
            'checked_out_by_agent_id' => User::factory(),
        ]);
    }

    public function withGroup(string $groupId): static
    {
        return $this->state(fn() => ['booking_group_id' => $groupId]);
    }
}
