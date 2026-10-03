<?php

namespace Modules\Frontdeskcrm\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Frontdeskcrm\Support\FrontdeskPermission;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Registers the Frontdesk CRM taxonomy and applies the frontdeskcrm.roles map.
 *
 * Idempotent and additive, so it is safe to re-run. Revocations are the point
 * rather than an afterthought: any role missing from the config map loses its
 * Frontdesk grants, because the module previously sat behind a single
 * `access_frontdesk_dashboard` check that anyone holding it passed.
 */
class FrontdeskPermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (FrontdeskPermission::allConfigured() as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        $roles = (array) config('frontdeskcrm.roles', []);

        foreach (Role::where('guard_name', 'web')->get() as $role) {
            if (array_key_exists($role->name, $roles)) {
                continue;
            }

            $this->revoke($role, FrontdeskPermission::all());
        }

        foreach ($roles as $roleName => $grants) {
            $role = Role::findOrCreate($roleName, 'web');

            $this->revoke($role, FrontdeskPermission::all());
            $role->givePermissionTo($this->permissionsFor($roleName, $grants));
        }

        FrontdeskPermission::forgetCache();
    }

    /**
     * Turn a config grant list into concrete permission names.
     *
     * Named permissionsFor rather than resolve: Illuminate\Database\Seeder
     * already declares `protected resolve($class)` for container resolution,
     * and shadowing it with a different signature makes the class unloadable.
     *
     * @param  array<int, string>  $grants
     * @return array<int, string>
     */
    private function permissionsFor(string $roleName, array $grants): array
    {
        if (in_array('*', $grants, true)) {
            return FrontdeskPermission::all();
        }

        // Intersect with the taxonomy so a stale entry in config cannot create
        // a permission that nothing checks.
        $names = array_values(array_intersect($grants, FrontdeskPermission::all()));

        if ($names === []) {
            $this->command?->warn("Frontdesk: role '{$roleName}' has no valid permissions in config.");
        }

        // Every role the map names also gets the module gate, so a role that
        // lists real capabilities but not the gate would pass the suites and
        // then 403 in the browser.
        $names[] = FrontdeskPermission::DASHBOARD_ACCESS;

        return array_values(array_unique($names));
    }

    /**
     * @param  array<int, string>  $permissions
     */
    private function revoke(Role $role, array $permissions): void
    {
        $toRevoke = array_values(array_intersect($role->permissions->pluck('name')->all(), $permissions));

        if ($toRevoke !== []) {
            $role->revokePermissionTo($toRevoke);
        }
    }
}
