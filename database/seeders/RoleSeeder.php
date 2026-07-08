<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // ──────────────────────────────────────────
            // MODULE ACCESS (keep existing underscore names)
            // ──────────────────────────────────────────
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

            // ──────────────────────────────────────────
            // ADMIN – legacy broad permissions (backward compat)
            // ──────────────────────────────────────────
            'manage_users',
            'manage_roles',
            'manage_permissions',
            'manage_settings',

            // ──────────────────────────────────────────
            // ADMIN – CRUD granular permissions
            // ──────────────────────────────────────────
            'users.create',
            'users.read',
            'users.update',
            'users.delete',
            'users.manage',

            'roles.create',
            'roles.read',
            'roles.update',
            'roles.delete',
            'roles.manage',

            'permissions.create',
            'permissions.read',
            'permissions.update',
            'permissions.delete',

            'settings.update',

            // ──────────────────────────────────────────
            // FRONT DESK
            // ──────────────────────────────────────────
            'check_in_guest',
            'check_out_guest',
            'manage_rooms',
            'guests.manage',

            // ──────────────────────────────────────────
            // HR / STAFF – legacy
            // ──────────────────────────────────────────
            'view_employees',
            'manage_employees',
            'approve_leaves',

            // ──────────────────────────────────────────
            // HR / STAFF – CRUD
            // ──────────────────────────────────────────
            'employees.create',
            'employees.read',
            'employees.update',
            'employees.delete',
            'leaves.create',
            'leaves.read',
            'leaves.update',
            'leaves.approve',
            'leaves.manage',
            'leaves.apply-for-others',

            // ──────────────────────────────────────────
            // TASKS – CRUD
            // ──────────────────────────────────────────
            'tasks.create',
            'tasks.read',
            'tasks.update',
            'tasks.delete',
            'tasks.assign',

            // ──────────────────────────────────────────
            // INVENTORY – legacy
            // ──────────────────────────────────────────
            'view_inventory',
            'adjust_stock',
            'manage_suppliers',

            // ──────────────────────────────────────────
            // INVENTORY – CRUD
            // ──────────────────────────────────────────
            'inventory.create',
            'inventory.read',
            'inventory.update',
            'inventory.delete',
            'suppliers.create',
            'suppliers.read',
            'suppliers.update',
            'suppliers.delete',

            // ──────────────────────────────────────────
            // INVENTORY – granular action permissions
            // ──────────────────────────────────────────
            'inventory.restock',
            'inventory.transfer',
            'inventory.usage',
            'inventory.adjustments',
            'inventory.reports',
            'inventory.export',
            'inventory.scan',
            'purchase_orders.create',
            'purchase_orders.approve',
            'purchase_orders.cancel',
            'purchase_orders.receive',
            'stores.create',
            'stores.read',
            'stores.update',
            'stores.delete',
            'departments.create',
            'departments.read',
            'departments.update',
            'departments.delete',

            // ──────────────────────────────────────────
            // RESTAURANT – legacy
            // ──────────────────────────────────────────
            'take_orders',
            'manage_menu',

            // ──────────────────────────────────────────
            // RESTAURANT – CRUD
            // ──────────────────────────────────────────
            'orders.create',
            'orders.read',
            'orders.update',
            'orders.delete',
            'menu.create',
            'menu.read',
            'menu.update',
            'menu.delete',

            // ──────────────────────────────────────────
            // MAINTENANCE – legacy
            // ──────────────────────────────────────────
            'view_tasks',
            'assign_tasks',
            'log_maintenance',

            // ──────────────────────────────────────────
            // BANQUET – legacy
            // ──────────────────────────────────────────
            'manage_banquet',

            // ──────────────────────────────────────────
            // BANQUET – CRUD
            // ──────────────────────────────────────────
            'banquet.create',
            'banquet.read',
            'banquet.update',
            'banquet.delete',

            // ──────────────────────────────────────────
            // GYM
            // ──────────────────────────────────────────
            'gym.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        // ──────────────────────────────────────────
        // SUPER ADMIN – gets everything
        // ──────────────────────────────────────────
        $admin = Role::firstOrCreate([
            'name' => 'admin',
            'guard_name' => 'web',
        ]);
        $admin->syncPermissions($permissions);

        // ──────────────────────────────────────────
        // HR MANAGER
        // ──────────────────────────────────────────
        Role::firstOrCreate(['name' => 'hr_manager', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_staff_dashboard',
                'view_employees',
                'manage_employees',
                'approve_leaves',
                'employees.create',
                'employees.read',
                'employees.update',
                'employees.delete',
                'leaves.manage',
                'leaves.apply-for-others',
                'leaves.create',
                'leaves.read',
                'leaves.update',
            ]);

        // ──────────────────────────────────────────
        // REGULAR STAFF
        // ──────────────────────────────────────────
        Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_staff_dashboard',
                'access_tasks_dashboard',
                'tasks.create',
                'tasks.read',
                'leaves.create',
                'leaves.read',
            ]);

        // ──────────────────────────────────────────
        // GUEST
        // ──────────────────────────────────────────
        Role::firstOrCreate(['name' => 'guest', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_website_dashboard',
            ]);

        // ──────────────────────────────────────────
        // RECEPTIONIST
        // ──────────────────────────────────────────
        Role::firstOrCreate(['name' => 'receptionist', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_frontdesk_dashboard',
                'check_in_guest',
                'check_out_guest',
                'guests.manage',
                'access_tasks_dashboard',
                'view_tasks',
                'tasks.read',
            ]);

        // ──────────────────────────────────────────
        // RESTAURANT MANAGER
        // ──────────────────────────────────────────
        Role::firstOrCreate(['name' => 'restaurant_manager', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_restaurant_dashboard',
                'manage_menu',
                'take_orders',
                'menu.create',
                'menu.read',
                'menu.update',
                'menu.delete',
                'orders.create',
                'orders.read',
                'orders.update',
                'orders.delete',
                'access_inventory_dashboard',
                'view_inventory',
                'inventory.reports',
                'inventory.export',
            ]);

        // ──────────────────────────────────────────
        // WAITER
        // ──────────────────────────────────────────
        Role::firstOrCreate(['name' => 'waiter', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_restaurant_dashboard',
                'take_orders',
                'orders.create',
                'orders.read',
            ]);

        // ──────────────────────────────────────────
        // GYM SUPERVISOR
        // ──────────────────────────────────────────
        Role::firstOrCreate(['name' => 'gym_supervisor', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_gym_dashboard',
                'gym.manage',
            ]);

        // ──────────────────────────────────────────
        // STORE KEEPER
        // ──────────────────────────────────────────
        Role::firstOrCreate(['name' => 'store_keeper', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_inventory_dashboard',
                'view_inventory',
                'adjust_stock',
                'manage_suppliers',
                'inventory.create',
                'inventory.read',
                'inventory.update',
                'inventory.delete',
                'suppliers.create',
                'suppliers.read',
                'suppliers.update',
                'suppliers.delete',
                'inventory.restock',
                'inventory.transfer',
                'inventory.usage',
                'inventory.adjustments',
                'inventory.reports',
                'inventory.export',
                'inventory.scan',
                'purchase_orders.create',
                'purchase_orders.approve',
                'purchase_orders.cancel',
                'purchase_orders.receive',
                'stores.create',
                'stores.read',
                'stores.update',
                'stores.delete',
                'departments.create',
                'departments.read',
                'departments.update',
                'departments.delete',
            ]);

        // ──────────────────────────────────────────
        // MAINTENANCE ENGINEER
        // ──────────────────────────────────────────
        Role::firstOrCreate(['name' => 'maintenance_engineer', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_maintenance_dashboard',
                'log_maintenance',
                'access_tasks_dashboard',
                'view_tasks',
                'tasks.read',
                'tasks.update',
                'access_inventory_dashboard',
                'view_inventory',
                'inventory.reports',
            ]);

        // ──────────────────────────────────────────
        // EVENT MANAGER
        // ──────────────────────────────────────────
        Role::firstOrCreate(['name' => 'event_manager', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_banquet_dashboard',
                'access_restaurant_dashboard',
                'manage_banquet',
                'banquet.create',
                'banquet.read',
                'banquet.update',
                'banquet.delete',
            ]);

        // ──────────────────────────────────────────
        // WEBSITE ADMIN
        // ──────────────────────────────────────────
        Role::firstOrCreate(['name' => 'website_admin', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_website_dashboard',
                'manage_settings',
                'settings.update',
            ]);
    }
}
