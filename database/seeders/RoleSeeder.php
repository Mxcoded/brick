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
            // PAYMENT GATEWAYS – management (pluggable)
            // ──────────────────────────────────────────
            'manage_payment_gateways',

            // ──────────────────────────────────────────
            // FRONT DESK – legacy
            // ──────────────────────────────────────────
            'check_in_guest',
            'check_out_guest',

            // ──────────────────────────────────────────
            // FRONT DESK – CRUD
            // ──────────────────────────────────────────
            'guests.create',
            'guests.read',
            'guests.update',
            'guests.delete',
            'guests.manage',

            // ──────────────────────────────────────────
            // HR / STAFF – legacy (kept for backward compat)
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
            // MAINTENANCE – legacy (backward compat)
            // ──────────────────────────────────────────
            'view_tasks',
            'assign_tasks',
            'log_maintenance',

            // ──────────────────────────────────────────
            // MAINTENANCE – CRUD
            // ──────────────────────────────────────────
            'maintenance.create',
            'maintenance.read',
            'maintenance.update',
            'maintenance.delete',

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
            'gym.create',
            'gym.update',
            'gym.delete',

            // ──────────────────────────────────────────
            // WEBSITE – dashboard access & CRUD (granular)
            // ──────────────────────────────────────────
            'website.dashboard.read',
            'website.dashboard.update',
            'website.bookings.create',
            'website.bookings.read',
            'website.bookings.update',
            'website.bookings.delete',
            'website.room-types.create',
            'website.room-types.read',
            'website.room-types.update',
            'website.room-types.delete',
            'website.amenities.create',
            'website.amenities.read',
            'website.amenities.update',
            'website.amenities.delete',
            'website.addons.create',
            'website.addons.read',
            'website.addons.update',
            'website.addons.delete',
            'website.settings.create',
            'website.settings.read',
            'website.settings.update',
            'website.settings.delete',
            'website.dining.create',
            'website.dining.read',
            'website.dining.update',
            'website.dining.delete',
            'website.meeting.create',
            'website.meeting.read',
            'website.meeting.update',
            'website.meeting.delete',
            'website.facilities.create',
            'website.facilities.read',
            'website.facilities.update',
            'website.facilities.delete',
            'website.offers.create',
            'website.offers.read',
            'website.offers.update',
            'website.offers.delete',
            'website.inventory.create',
            'website.inventory.read',
            'website.inventory.update',
            'website.inventory.delete',
            'website.contact-messages.read',
            'website.contact-messages.update',
            'website.contact-messages.delete',
            'website.newsletter.create',
            'website.newsletter.read',
            'website.newsletter.update',
            'website.newsletter.delete',
            'website.subscribers.create',
            'website.subscribers.read',
            'website.subscribers.update',
            'website.subscribers.delete',
            'website.testimonials.create',
            'website.testimonials.read',
            'website.testimonials.update',
            'website.testimonials.delete',

            // ──────────────────────────────────────────
            // PROCUREMENT – permissions (centralized)
            // ──────────────────────────────────────────
            'procurement.create_request',
            'procurement.view_own_requests',
            'procurement.view_all_requests',
            'procurement.review_request',
            'procurement.approve_request',
            'procurement.reject_request',
            'procurement.flag_request',
            'procurement.attach_invoice',
            'procurement.audit_request',
            'procurement.convert_to_po',

            // ──────────────────────────────────────────
            // FINANCE – double-entry ledger
            // ──────────────────────────────────────────
            'finance.view_coa',
            'finance.manage_coa',
            'finance.post_journal',
            'finance.view_ledger',
            'finance.view_reports',
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
                'employees.create',
                'employees.read',
                'employees.update',
                'employees.delete',
                'access_tasks_dashboard',
                'tasks.create',
                'tasks.read',
                'leaves.approve',
                'leaves.manage',
                'leaves.apply-for-others',
                'leaves.create',
                'leaves.read',
                'leaves.update',
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
                'guests.create',
                'guests.read',
                'guests.update',
                'guests.delete',
                'access_tasks_dashboard',
                'tasks.create',
                'tasks.read',
            ]);

        // ──────────────────────────────────────────
        // RESTAURANT MANAGER
        // ──────────────────────────────────────────
        Role::firstOrCreate(['name' => 'restaurant_manager', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_restaurant_dashboard',
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
                'inventory.reports',
                'inventory.export',
                'access_tasks_dashboard',
                'tasks.create',
                'tasks.read',
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
                'access_tasks_dashboard',
                'tasks.create',
                'tasks.read',
            ]);

        // ──────────────────────────────────────────
        // GYM SUPERVISOR
        // ──────────────────────────────────────────
        Role::firstOrCreate(['name' => 'gym_supervisor', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_gym_dashboard',
                'gym.create',
                'gym.update',
                'gym.delete',
                'access_tasks_dashboard',
                'tasks.create',
                'tasks.read',
            ]);

        // ──────────────────────────────────────────
        // STORE KEEPER
        // ──────────────────────────────────────────
        Role::firstOrCreate(['name' => 'store_keeper', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_inventory_dashboard',
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
                'maintenance.create',
                'maintenance.read',
                'maintenance.update',
                'maintenance.delete',
                'access_tasks_dashboard',
                'tasks.read',
                'tasks.update',
                'access_inventory_dashboard',
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
                'access_tasks_dashboard',
                'tasks.create',
                'tasks.read',
            ]);

        // ──────────────────────────────────────────
        // WEBSITE ADMIN
        // ──────────────────────────────────────────
        Role::firstOrCreate(['name' => 'website_admin', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_website_dashboard',
                'manage_settings',
                'settings.update',
                'website.dashboard.read',
                'website.dashboard.update',
                'website.bookings.create',
                'website.bookings.read',
                'website.bookings.update',
                'website.bookings.delete',
                'website.room-types.create',
                'website.room-types.read',
                'website.room-types.update',
                'website.room-types.delete',
                'website.amenities.create',
                'website.amenities.read',
                'website.amenities.update',
                'website.amenities.delete',
                'website.addons.create',
                'website.addons.read',
                'website.addons.update',
                'website.addons.delete',
                'website.settings.create',
                'website.settings.read',
                'website.settings.update',
                'website.settings.delete',
                'website.dining.create',
                'website.dining.read',
                'website.dining.update',
                'website.dining.delete',
                'website.meeting.create',
                'website.meeting.read',
                'website.meeting.update',
                'website.meeting.delete',
                'website.facilities.create',
                'website.facilities.read',
                'website.facilities.update',
                'website.facilities.delete',
                'website.offers.create',
                'website.offers.read',
                'website.offers.update',
                'website.offers.delete',
                'website.inventory.create',
                'website.inventory.read',
                'website.inventory.update',
                'website.inventory.delete',
                'website.contact-messages.read',
                'website.contact-messages.update',
                'website.contact-messages.delete',
                'website.newsletter.create',
                'website.newsletter.read',
                'website.newsletter.update',
                'website.newsletter.delete',
                'website.subscribers.create',
                'website.subscribers.read',
                'website.subscribers.update',
                'website.subscribers.delete',
                'website.testimonials.create',
                'website.testimonials.read',
                'website.testimonials.update',
                'website.testimonials.delete',
            ]);
    }
}
