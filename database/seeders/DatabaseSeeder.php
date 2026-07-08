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
