<?php

namespace Database\Seeders;

use App\Models\Property;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(RoleSeeder::class);

        if (! User::where('email', 'admin@brickspoint.com')->exists()) {
            $admin = User::create([
                'name' => 'Super Admin',
                'email' => 'admin@brickspoint.com',
                'password' => 'password',
                'type' => 'staff',
                'status' => 'active',
            ]);

            $admin->assignRole('admin');

            if (Schema::hasTable('properties')) {
                $property = Property::first();
                if ($property) {
                    $admin->properties()->attach($property->id, ['is_default' => true]);
                }
            }
        }

        if (! User::where('email', 'staff@brickspoint.com')->exists()) {
            $staff = User::create([
                'name' => 'Staff User',
                'email' => 'staff@brickspoint.com',
                'password' => 'password',
                'type' => 'staff',
                'status' => 'active',
            ]);

            $staff->assignRole('receptionist');

            if (Schema::hasTable('properties')) {
                $property = Property::first();
                if ($property) {
                    $staff->properties()->attach($property->id, ['is_default' => true]);
                }
            }
        }

        User::firstOrCreate(
            ['email' => 'it@brickspoint.com'],
            [
                'name' => 'Oluwasheyi Makanjuola',
                'password' => bcrypt('password'),
                'type' => 'staff',
                'status' => 'active',
            ]
        )->assignRole('admin');
    }
}
