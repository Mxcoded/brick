<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use App\Enums\RoleEnum; // Optional if you use the Enum, but strings are fine here for seeding

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // 1. Reset cached roles and permissions to ensure fresh start
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // =================================================================
        // 2. DEFINE ALL PERMISSIONS
        // =================================================================
        $permissions = [
            // --- Module Access Gatekeepers (Used in Sidebar & Routes) ---
            'access_admin_dashboard',
            'access_frontdesk_dashboard',
            'access_staff_dashboard',
            'access_restaurant_dashboard',
            'access_gym_dashboard',
            'access_inventory_dashboard',
            'access_maintenance_dashboard',
            'access_tasks_dashboard',
            'access_banquet_dashboard',

            // --- Admin Specific ---
            'manage_users',
            'manage_roles',
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

        // Create Permissions
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        // =================================================================
        // 3. DEFINE ROLES & ASSIGN PERMISSIONS
        // =================================================================

        // 1. SUPER ADMIN (The Owner/Manager)
        // Access: EVERYTHING
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // 2. HR MANAGER
        // Access: Staff Module + Admin Reports
        $hr = Role::firstOrCreate(['name' => 'hr_manager']);
        $hr->givePermissionTo([
            'access_staff_dashboard',
            'view_employees',
            'manage_employees',
            'approve_leaves'
        ]);

        // 3. RECEPTIONIST
        // Access: Front Desk + Housekeeping Tasks
        $receptionist = Role::firstOrCreate(['name' => 'receptionist']);
        $receptionist->givePermissionTo([
            'access_frontdesk_dashboard',
            'check_in_guest',
            'check_out_guest',
            'access_tasks_dashboard', // Needs to see room status tasks
            'view_tasks'
        ]);

        // 4. RESTAURANT MANAGER
        // Access: Restaurant + Inventory (Food Cost)
        $restoManager = Role::firstOrCreate(['name' => 'restaurant_manager']);
        $restoManager->givePermissionTo([
            'access_restaurant_dashboard',
            'manage_menu',
            'take_orders',
            'access_inventory_dashboard', // Needs to order food
            'view_inventory'
        ]);

        // 5. WAITER / SERVER
        // Access: Restaurant (Orders Only)
        $waiter = Role::firstOrCreate(['name' => 'waiter']);
        $waiter->givePermissionTo([
            'access_restaurant_dashboard',
            'take_orders'
        ]);

        // 6. GYM SUPERVISOR
        // Access: Gym Module
        $gymSup = Role::firstOrCreate(['name' => 'gym_supervisor']);
        $gymSup->givePermissionTo([
            'access_gym_dashboard',
        ]);

        // 7. STORE KEEPER
        // Access: Inventory Module Only
        $storeKeeper = Role::firstOrCreate(['name' => 'store_keeper']);
        $storeKeeper->givePermissionTo([
            'access_inventory_dashboard',
            'view_inventory',
            'adjust_stock',
            'manage_suppliers'
        ]);

        // 8. MAINTENANCE ENGINEER
        // Access: Maintenance + Tasks + Inventory (Spare parts)
        $engineer = Role::firstOrCreate(['name' => 'maintenance_engineer']);
        $engineer->givePermissionTo([
            'access_maintenance_dashboard',
            'log_maintenance',
            'access_tasks_dashboard',
            'view_tasks',
            'access_inventory_dashboard', // To check for spare parts
            'view_inventory'
        ]);

        // 9. EVENT / BANQUET MANAGER
        // Access: Banquets + Restaurant (Catering)
        $eventManager = Role::firstOrCreate(['name' => 'event_manager']);
        $eventManager->givePermissionTo([
            'access_banquet_dashboard',
            'access_restaurant_dashboard', // For food arrangements
        ]);

        // 10. GUEST (Standard User)
        // Access: None (Public Profile only)
        $guest = Role::firstOrCreate(['name' => 'guest']);
    }
}
