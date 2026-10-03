<?php

namespace Modules\Restaurant\Tests\Concerns;

use Modules\Restaurant\Support\RestaurantPermission;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Grants a Restaurant role exactly as the config map describes it.
 *
 * The suites used to hand out `access_restaurant_dashboard` by hand, which was
 * enough for every route at the time. Now that the back office is split into
 * per-capability permissions, restating the strings here would mean two places
 * to update whenever the matrix changes, so the tests read the same source of
 * truth the seeder does.
 */
trait GrantsRestaurantRoles
{
    /**
     * Create the taxonomy, then give the role whatever config/restaurant.php
     * says it should have.
     */
    protected function grantRestaurantRole(string $roleName): Role
    {
        foreach (RestaurantPermission::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $grants = RestaurantPermission::forRole($roleName);

        $role->revokePermissionTo(RestaurantPermission::all());
        $role->givePermissionTo($grants);
        $role->givePermissionTo(RestaurantPermission::DASHBOARD_ACCESS);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $role;
    }

    /**
     * Give one user an explicit set of capabilities, for the negative cases where
     * the point of the test is that a specific permission is absent.
     */
    protected function grantRestaurantPermissions(string $roleName, array $permissions): Role
    {
        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $role->revokePermissionTo(RestaurantPermission::all());
        $role->givePermissionTo($permissions);
        $role->givePermissionTo(RestaurantPermission::DASHBOARD_ACCESS);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $role;
    }
}
