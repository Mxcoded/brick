<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run()
    {
        // 1. Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // =================================================================
        // 2. DEFINE ALL PERMISSIONS
        // =================================================================
        $permissions = [
            // --- Module Access Gatekeepers ---
            'access_admin_dashboard',
            'access_frontdesk_dashboard',
            'access_staff_dashboard',
            'access_restaurant_dashboard',
            'access_gym_dashboard',
            'access_inventory_dashboard',
            'access_maintenance_dashboard',
            'access_tasks_dashboard',
            'access_banquet_dashboard',
            'access_website_dashboard', // Added this

            // --- Admin Specific ---
            'manage_users',
            'manage_roles',
            'manage_permissions', // Added for granular control
            'manage_settings',

            // --- Front Desk Specific ---
            'check_in_guest',
            'check_out_guest',
            'manage_rooms',

            // --- Staff/HR Specific ---
            'view_employees',
            'manage_employees',
            'approve_leaves',

            // --- Inventory Specific ---
            'view_inventory',
            'adjust_stock',
            'manage_suppliers',

            // --- Restaurant Specific ---
            'take_orders',
            'manage_menu',

            // --- Operations ---
            'view_tasks',
            'assign_tasks',
            'log_maintenance',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // =================================================================
        // 3. DEFINE ROLES & ASSIGN PERMISSIONS
        // =================================================================

        // 1. SUPER ADMIN
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // 2. HR MANAGER
        $hr = Role::firstOrCreate(['name' => 'hr_manager']);
        $hr->givePermissionTo([
            'access_staff_dashboard',
            'view_employees',
            'manage_employees',
            'approve_leaves'
        ]);

        // 3. RECEPTIONIST
        $receptionist = Role::firstOrCreate(['name' => 'receptionist']);
        $receptionist->givePermissionTo([
            'access_frontdesk_dashboard',
            'check_in_guest',
            'check_out_guest',
            'access_tasks_dashboard',
            'view_tasks'
        ]);

        // 4. RESTAURANT MANAGER
        $restoManager = Role::firstOrCreate(['name' => 'restaurant_manager']);
        $restoManager->givePermissionTo([
            'access_restaurant_dashboard',
            'manage_menu',
            'take_orders',
            'access_inventory_dashboard',
            'view_inventory'
        ]);

        // 5. WAITER
        $waiter = Role::firstOrCreate(['name' => 'waiter']);
        $waiter->givePermissionTo([
            'access_restaurant_dashboard',
            'take_orders'
        ]);

        // 6. GYM SUPERVISOR
        $gymSup = Role::firstOrCreate(['name' => 'gym_supervisor']);
        $gymSup->givePermissionTo([
            'access_gym_dashboard',
        ]);

        // 7. STORE KEEPER
        $storeKeeper = Role::firstOrCreate(['name' => 'store_keeper']);
        $storeKeeper->givePermissionTo([
            'access_inventory_dashboard',
            'view_inventory',
            'adjust_stock',
            'manage_suppliers'
        ]);

        // 8. MAINTENANCE ENGINEER
        $engineer = Role::firstOrCreate(['name' => 'maintenance_engineer']);
        $engineer->givePermissionTo([
            'access_maintenance_dashboard',
            'log_maintenance',
            'access_tasks_dashboard',
            'view_tasks',
            'access_inventory_dashboard',
            'view_inventory'
        ]);

        // 9. EVENT MANAGER
        $eventManager = Role::firstOrCreate(['name' => 'event_manager']);
        $eventManager->givePermissionTo([
            'access_banquet_dashboard',
            'access_restaurant_dashboard',
        ]);

        // 10. WEBSITE ADMIN (New Role)
        $webAdmin = Role::firstOrCreate(['name' => 'website_admin']);
        $webAdmin->givePermissionTo([
            'access_website_dashboard',
            'manage_settings'
        ]);
    }
}
