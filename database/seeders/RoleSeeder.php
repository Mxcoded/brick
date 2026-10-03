<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Finance\Support\FinancePermission;
use Modules\Restaurant\Support\RestaurantPermission;
use Modules\Tasks\Support\TaskPermission;
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
            // ADMIN – log & audit trail viewers
            // ──────────────────────────────────────────
            'login_logs.read',
            'activity_logs.read',
            'audit_trails.read',
            'modules.manage',

            // ──────────────────────────────────────────
            // PAYMENT GATEWAYS – management (pluggable)
            // ──────────────────────────────────────────
            'payment_gateways.manage',

            // ──────────────────────────────────────────
            // FRONT DESK – CRUD
            // ──────────────────────────────────────────
            'guests.create',
            'guests.read',
            'guests.update',
            'guests.delete',
            'guests.manage',
            'guests.check_in',
            'guests.check_out',

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
            //
            // Granted to admin as part of the superset. Every other role's task
            // access is applied by applyTaskPermissions() at the end of this
            // seeder, so the matrix lives in one place.
            // ──────────────────────────────────────────
            TaskPermission::READ,
            TaskPermission::CREATE,
            TaskPermission::UPDATE,
            TaskPermission::DELETE,
            TaskPermission::ASSIGN,

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
            // MAINTENANCE – CRUD
            // ──────────────────────────────────────────
            'maintenance.create',
            'maintenance.read',
            'maintenance.update',
            'maintenance.delete',

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
            //
            // The legacy flat names stay listed so nothing that still checks
            // them directly breaks, but the granular CRUD permissions are merged
            // in below. Admin is the superuser, so it must hold every Finance
            // capability - including any added later.
            // ──────────────────────────────────────────
            'finance.view_coa',
            'finance.manage_coa',
            'finance.create_journal',
            'finance.post_journal',
            'finance.view_ledger',
            'finance.view_reports',
            'finance.manage_periods',

            // ──────────────────────────────────────────
            // CONTRACTS & AGREEMENTS
            // ──────────────────────────────────────────
            'access_contracts_dashboard',
            'contracts.read',
            'contracts.create',
            'contracts.update',
            'contracts.delete',
            'contracts.approve',
            'contracts.sign',
            'contracts.manage_templates',
            'contracts.view_audit',
        ];

        // Every Finance permission the module currently defines. Deriving this
        // from the module's registry means a new finance.* capability is granted
        // to the superuser without anyone remembering to add it here.
        $permissions = array_merge($permissions, FinancePermission::all());

        // Same for Restaurant: the back office is now split into per-resource,
        // per-action capabilities, so the superuser has to pick up the whole
        // taxonomy from the module registry rather than a hand-kept list.
        $permissions = array_merge($permissions, RestaurantPermission::all());

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
                'guests.check_in',
                'guests.check_out',
                'guests.create',
                'guests.read',
                'guests.update',
                'guests.delete',
            ]);

        // ──────────────────────────────────────────
        // RESTAURANT MANAGER
        //
        // Only the non-Restaurant rights live here. The module's own
        // capabilities are applied by applyRestaurantPermissions() from
        // config/restaurant.php, because a syncPermissions() call here would
        // strip them on every re-run of this seeder.
        // ──────────────────────────────────────────
        Role::firstOrCreate(['name' => 'restaurant_manager', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_inventory_dashboard',
                'inventory.reports',
                'inventory.export',
            ]);

        // ──────────────────────────────────────────
        // WAITER
        //
        // Restaurant grants are applied additively below. Dashboard access alone
        // used to be enough to reach every admin route, which is why waiter is
        // now given an explicit, narrow set instead.
        // ──────────────────────────────────────────
        Role::firstOrCreate(['name' => 'waiter', 'guard_name' => 'web'])
            ->syncPermissions([]);

        // ──────────────────────────────────────────
        // GYM SUPERVISOR
        // ──────────────────────────────────────────
        Role::firstOrCreate(['name' => 'gym_supervisor', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_gym_dashboard',
                'gym.create',
                'gym.update',
                'gym.delete',
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
                'banquet.create',
                'banquet.read',
                'banquet.update',
                'banquet.delete',
            ]);

        // ──────────────────────────────────────────
        // CONTRACTS MANAGER
        // ──────────────────────────────────────────
        Role::firstOrCreate(['name' => 'contracts_manager', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_contracts_dashboard',
                'contracts.read',
                'contracts.create',
                'contracts.update',
                'contracts.delete',
                'contracts.approve',
                'contracts.sign',
                'contracts.manage_templates',
                'contracts.view_audit',
            ]);

        // ──────────────────────────────────────────
        // WEBSITE ADMIN
        // ──────────────────────────────────────────
        Role::firstOrCreate(['name' => 'website_admin', 'guard_name' => 'web'])
            ->syncPermissions([
                'access_website_dashboard',
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

        // ──────────────────────────────────────────
        // PROCUREMENT CHAIN (line manager -> GGM)
        // ──────────────────────────────────────────
        Role::firstOrCreate(['name' => 'line_manager', 'guard_name' => 'web'])
            ->syncPermissions([
                'procurement.create_request',
                'procurement.view_own_requests',
            ]);

        Role::firstOrCreate(['name' => 'purchaser', 'guard_name' => 'web'])
            ->syncPermissions([
                'procurement.view_all_requests',
                'procurement.review_request',
                'procurement.reject_request',
                'procurement.flag_request',
                'procurement.attach_invoice',
            ]);

        Role::firstOrCreate(['name' => 'gm', 'guard_name' => 'web'])
            ->syncPermissions([
                'procurement.view_all_requests',
                'procurement.approve_request',
                'procurement.reject_request',
                'procurement.flag_request',
            ]);

        // Additive on purpose: the finance.* ledger permissions are owned by the
        // Finance module's FinancePermissionSeeder. syncPermissions() here would
        // replace the whole grant and strip ledger access from finance staff.
        Role::firstOrCreate(['name' => 'finance', 'guard_name' => 'web'])
            ->givePermissionTo([
                'procurement.view_all_requests',
                'procurement.approve_request',
                'procurement.reject_request',
            ]);

        // Senior accountant is the posting tier: it may draft, post and reverse
        // journals and lock periods, which the junior `finance` role may not.
        // The ledger grant itself lives in FinancePermissionSeeder (SENIOR_ONLY)
        // so the read-only / post-capable split is defined in exactly one place.
        // Created here so the role is assignable even before the Finance module
        // seeder has run.
        Role::firstOrCreate(['name' => 'senior_accountant', 'guard_name' => 'web']);

        Role::firstOrCreate(['name' => 'auditor', 'guard_name' => 'web'])
            ->syncPermissions([
                'procurement.view_all_requests',
                'procurement.audit_request',
                'procurement.reject_request',
            ]);

        Role::firstOrCreate(['name' => 'ggm', 'guard_name' => 'web'])
            ->syncPermissions([
                'procurement.view_all_requests',
                'procurement.approve_request',
                'procurement.reject_request',
                'procurement.convert_to_po',
            ]);

        $this->applyTaskPermissions();
        $this->applyRestaurantPermissions();
    }

    /**
     * Apply the Restaurant role matrix from config/restaurant.php.
     *
     * The module is no longer gated by `access_restaurant_dashboard` alone, so
     * the role lists above must not state Restaurant rights twice: the config
     * map is the single authority, and the taxonomy itself comes from
     * RestaurantPermission.
     *
     * Additive for the same reason as the Tasks matrix, and roles outside the
     * map have their Restaurant grants withdrawn, which is what actually
     * reduces waiter to a server rather than a full back-office operator.
     */
    protected function applyRestaurantPermissions(): void
    {
        $all = RestaurantPermission::all();

        foreach (Role::all() as $role) {
            // The superuser is granted the full taxonomy above.
            if ($role->name === 'admin') {
                continue;
            }

            $grants = RestaurantPermission::forRole($role->name);

            $held = array_values(array_intersect($role->permissions->pluck('name')->all(), $all));
            $toRevoke = array_values(array_diff($held, $grants));

            if ($toRevoke !== []) {
                $role->revokePermissionTo($toRevoke);
            }

            if ($grants !== []) {
                $role->givePermissionTo($grants);
            }
        }
    }

    /**
     * Apply the Tasks role matrix.
     *
     * Task access is deliberately not spelled out in the per-role lists above.
     * Tasks is a working tool, so the baseline (open, read, create, update)
     * applies to every member of staff through the isStaff() blanket in
     * AppServiceProvider, and only delegation needs a role to say so. Keeping
     * the matrix here means one list decides who can hand work to whom, rather
     * than the same four strings repeated down the file.
     *
     * Additive on purpose. The finance roles in particular are seeded elsewhere
     * by FinancePermissionSeeder, and a syncPermissions() here would strip the
     * ledger grants the line above this method was careful to preserve.
     */
    protected function applyTaskPermissions(): void
    {
        foreach (Role::all() as $role) {
            $extras = TaskPermission::extrasFor($role->name);

            if ($extras === []) {
                continue;
            }

            $role->givePermissionTo($extras);
        }

        // Drop assignment from anyone it was handed to outside the matrix, so
        // retiring a role from DELEGATOR_ROLES actually withdraws the ability
        // rather than leaving it behind from an earlier seed.
        $delegators = TaskPermission::DELEGATOR_ROLES;

        Role::all()
            ->reject(fn ($role) => in_array($role->name, $delegators, true))
            ->reject(fn ($role) => $role->name === 'admin')
            ->each(function ($role) {
                if ($role->hasPermissionTo(TaskPermission::ASSIGN)) {
                    $role->revokePermissionTo(TaskPermission::ASSIGN);
                }
            });
    }
}
