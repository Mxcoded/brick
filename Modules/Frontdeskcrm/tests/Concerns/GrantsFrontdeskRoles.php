<?php

namespace Modules\Frontdeskcrm\Tests\Concerns;

use Modules\Frontdeskcrm\Support\FrontdeskPermission;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Grants a Frontdesk role exactly as the module config describes it.
 *
 * The suites used to hand out `access_frontdesk_dashboard` by hand, which was
 * enough while every route sat behind that single gate. Now that the PMS screens
 * are served as Filament resources with per-capability checks, restating the
 * strings here would mean two places to update whenever the matrix changes, so
 * the tests read the same source of truth the seeder does.
 */
trait GrantsFrontdeskRoles
{
    /**
     * Create the taxonomy, then give the role whatever the config map says it
     * should have.
     */
    protected function grantFrontdeskRole(string $roleName): Role
    {
        foreach (FrontdeskPermission::all() as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $grants = FrontdeskPermission::forRole($roleName);

        $role->revokePermissionTo(FrontdeskPermission::all());
        $role->givePermissionTo($grants);
        $role->givePermissionTo(FrontdeskPermission::DASHBOARD_ACCESS);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $role;
    }

    /**
     * Give one role an explicit set of capabilities, for the negative cases where
     * the point of the test is that a specific permission is absent.
     */
    protected function grantFrontdeskPermissions(string $roleName, array $permissions): Role
    {
        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'guard_name' => 'web']);
        }

        $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        $role->revokePermissionTo(FrontdeskPermission::all());
        $role->givePermissionTo($permissions);
        $role->givePermissionTo(FrontdeskPermission::DASHBOARD_ACCESS);

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $role;
    }
}
