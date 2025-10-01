<?php

namespace Modules\Frontdeskcrm\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\GuestPreference;

class GuestsSeeder extends Seeder
{
    public function run(): void
    {
        $guests = [
            [
                'title' => 'Mr.',
                'full_name' => 'Doe John',
                'nationality' => 'USA',
                'contact_number' => '+1-555-1234',
                'birthday' => '1980-05-15',
                'email' => 'john.doe@example.com',
                'occupation' => 'Engineer',
                'company_name' => 'Tech Corp',
                'home_address' => '123 Main St, New York',
                'emergency_name' => 'Jane Doe',
                'emergency_relationship' => 'Spouse',
                'emergency_contact' => '+1-555-5678',
                'last_visit_at' => now()->subDays(10),
                'visit_count' => 3,
                'opt_in_data_save' => true,
            ],
            [
                'title' => 'Ms.',
                'full_name' => 'Smith Jane',
                'nationality' => 'UK',
                'contact_number' => '+44-20-1234-5678',
                'birthday' => '1990-12-01',
                'email' => 'jane.smith@example.co.uk',
                'occupation' => 'Manager',
                'home_address' => '456 High St, London',
                'emergency_name' => 'Bob Smith',
                'emergency_relationship' => 'Brother',
                'emergency_contact' => '+44-20-8765-4321',
                'last_visit_at' => now()->subMonth(),
                'visit_count' => 1,
                'opt_in_data_save' => true,
            ],
        ];

        foreach ($guests as $guestData) {
            $guest = Guest::create($guestData);
            GuestPreference::create([
                'guest_id' => $guest->id,
                'preferences' => [
                    'preferred_room_type' => 'Deluxe',
                    'bb_included' => true,
                    'language' => 'en', // For generated column
                ],
            ]);
        }
    }
}
