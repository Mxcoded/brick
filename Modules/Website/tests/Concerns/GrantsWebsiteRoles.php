<?php

namespace Modules\Website\Tests\Concerns;

use Modules\Website\Support\WebsitePermission;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Grants a Website role exactly as the module config describes it.
 *
 * The suites hand out Website permissions directly, which was enough while every
 * screen sat behind `access_website_dashboard`. With the CRUD screens now served as
 * Filament resources with per-capability checks, restating the strings here would
 * mean two places to update whenever the matrix changes, so the tests read the
 * same source of truth the seeder does.
 */
trait GrantsWebsiteRoles
{
    /**
     * Create the taxonomy, then give the role whatever the config map says it
     * should have.
     */
    protected function grantWebsiteRole(string $roleName): Role
    {
        foreach (WebsitePermission::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $role->revokePermissionTo(WebsitePermission::all());
        $role->givePermissionTo(WebsitePermission::forRole($roleName));
        $role->givePermissionTo(WebsitePermission::DASHBOARD_ACCESS);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $role;
    }

    /**
     * Give one role an explicit set of capabilities, for the negative cases where
     * the point of the test is that a specific permission is absent.
     */
    protected function grantWebsitePermissions(string $roleName, array $permissions): Role
    {
        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $role->revokePermissionTo(WebsitePermission::all());
        $role->givePermissionTo($permissions);
        $role->givePermissionTo(WebsitePermission::DASHBOARD_ACCESS);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $role;
    }
}
