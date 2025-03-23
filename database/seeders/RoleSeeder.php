<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Reset cached roles and permissions to avoid conflicts
        app(\Spatie\Permission\PermissionRegistrar::class)->forgetCachedPermissions();

        // Create Permissions
        $permissions = [
            'create-staff',
            'edit-staff',
            'delete-staff',
            'view-staff',
            'manage-leaves',
            'approve-leaves',
            'view-reports',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // Create Roles and Assign Permissions
        $adminRole = Role::firstOrCreate(['name' => 'admin']);
        $adminRole->syncPermissions($permissions); // Admin gets all permissions

        $staffRole = Role::firstOrCreate(['name' => 'staff']);
        $staffRole->syncPermissions([
            'view-staff',
            'manage-leaves',
            'view-reports',
        ]);

        $guestRole = Role::firstOrCreate(['name' => 'guest']);
        $guestRole->syncPermissions(['view-staff']); // Minimal access

        // Optionally assign roles to existing users (example)
        $adminUser = \App\Models\User::firstOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => bcrypt('password'),
            ]
        );
        $adminUser->assignRole('admin');

        $staffUser = \App\Models\User::firstOrCreate(
            ['email' => 'staff@example.com'],
            [
                'name' => 'Staff User',
                'password' => bcrypt('password'),
            ]
        );
        $staffUser->assignRole('staff');
    }
}
