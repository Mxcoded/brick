<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // ==========================================================
        // DEFINE ALL PERMISSIONS
        // ==========================================================
        $permissions = [
            // Dashboards
            'access_admin_dashboard',
            'access_frontdesk_dashboard',
            'access_staff_dashboard',
            'access_restaurant_dashboard',
            'access_gym_dashboard',
            'access_inventory_dashboard',
            'access_maintenance_dashboard',
            'access_tasks_dashboard',
            'access_banquet_dashboard',
            'access_website_dashboard',

            // Admin
            'manage_users',
            'manage_roles',
            'manage_permissions',
            'manage_settings',

            // Front Desk
            'check_in_guest',
            'check_out_guest',
            'manage_rooms',

            // HR
            'view_employees',
            'manage_employees',
            'approve_leaves',

            // Inventory
            'view_inventory',
            'adjust_stock',
            'manage_suppliers',

            // Restaurant
            'take_orders',
            'manage_menu',

            // Tasks & Maintenance
            'view_tasks',
            'assign_tasks',
            'log_maintenance',

            // Banquet / Events
            'manage_banquet',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name'       => $permission,
                'guard_name' => 'web',
            ]);
        }

        // ==========================================================
        // ROLES & PERMISSION ASSIGNMENT
        // ==========================================================

        // SUPER ADMIN
        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
        $admin->syncPermissions($permissions);

        // HR MANAGER
        Role::firstOrCreate(['name' => 'hr_manager', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_staff_dashboard',
                'view_employees',
                'manage_employees',
                'approve_leaves',
            ]);

        // RECEPTIONIST
        Role::firstOrCreate(['name' => 'receptionist', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_frontdesk_dashboard',
                'check_in_guest',
                'check_out_guest',
                'access_tasks_dashboard',
                'view_tasks',
            ]);

        // RESTAURANT MANAGER
        Role::firstOrCreate(['name' => 'restaurant_manager', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_restaurant_dashboard',
                'manage_menu',
                'take_orders',
                'access_inventory_dashboard',
                'view_inventory',
            ]);

        // WAITER
        Role::firstOrCreate(['name' => 'waiter', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_restaurant_dashboard',
                'take_orders',
            ]);

        // GYM SUPERVISOR
        Role::firstOrCreate(['name' => 'gym_supervisor', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_gym_dashboard',
            ]);

        // STORE KEEPER
        Role::firstOrCreate(['name' => 'store_keeper', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_inventory_dashboard',
                'view_inventory',
                'adjust_stock',
                'manage_suppliers',
            ]);

        // MAINTENANCE ENGINEER
        Role::firstOrCreate(['name' => 'maintenance_engineer', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_maintenance_dashboard',
                'log_maintenance',
                'access_tasks_dashboard',
                'view_tasks',
                'access_inventory_dashboard',
                'view_inventory',
            ]);

        // EVENT MANAGER
        Role::firstOrCreate(['name' => 'event_manager', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_banquet_dashboard',
                'access_restaurant_dashboard',
                'manage_banquet',
            ]);

        // WEBSITE ADMIN
        Role::firstOrCreate(['name' => 'website_admin', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_website_dashboard',
                'manage_settings',
            ]);
    }
}
