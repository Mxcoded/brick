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
    public function run()
    {
        // 1. Create "Module Access" Permissions
        $accessAdmin     = Permission::firstOrCreate(['name' => 'access_admin_dashboard']);
        $accessStaff     = Permission::firstOrCreate(['name' => 'access_staff_dashboard']);
        $accessFrontDesk = Permission::firstOrCreate(['name' => 'access_frontdesk_dashboard']);
        $accessGym       = Permission::firstOrCreate(['name' => 'access_gym_dashboard']);

        // 2. Create Roles & Assign Access

        // ADMIN (Master Access)
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // FRONT DESK (Standard Staff)
        $receptionist = Role::firstOrCreate(['name' => 'receptionist']);
        $receptionist->givePermissionTo([$accessStaff, $accessFrontDesk]);

        // HR (Dynamic Role Example)
        $hr = Role::firstOrCreate(['name' => 'human_resources']);
        $hr->givePermissionTo([$accessStaff]); // HR can access Staff dashboard but maybe not FrontDesk
    }
}