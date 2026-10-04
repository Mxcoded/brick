<?php

namespace Modules\Website\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Website\Support\WebsitePermission;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Registers the Website permission taxonomy and applies the website.roles map.
 *
 * Idempotent and additive, so it is safe to re-run.
 *
 * Revocations are the point rather than an afterthought. The legacy admin screens
 * only ever checked `website.{resource}.{action}` inline, so a role could hold a
 * half-finished set of Website grants nobody could enumerate. Applying the map
 * means every role's Website access is the list in config and nothing else.
 */
class WebsitePermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (WebsitePermission::allConfigured() as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        $roles = (array) config('website.roles', []);

        foreach (Role::where('guard_name', 'web')->get() as $role) {
            if (array_key_exists($role->name, $roles)) {
                continue;
            }

            $this->revoke($role, WebsitePermission::all());
        }

        foreach ($roles as $roleName => $grants) {
            $role = Role::findOrCreate($roleName, 'web');

            $this->revoke($role, WebsitePermission::all());
            $role->givePermissionTo($this->permissionsFor($roleName, $grants));
        }

        WebsitePermission::forgetCache();
    }

    /**
     * Turn a config grant list into concrete permission names.
     *
     * @param  array<int, string>  $grants
     * @return array<int, string>
     */
    private function permissionsFor(string $roleName, array $grants): array
    {
        if (in_array('*', $grants, true)) {
            return WebsitePermission::all();
        }

        // Intersect with the taxonomy so a stale entry in config cannot create a
        // permission that nothing checks.
        $names = array_values(array_intersect($grants, WebsitePermission::all()));

        if ($names === []) {
            $this->command?->warn("Website: role '{$roleName}' has no valid permissions in config.");
        }

        // Every role the map names also gets the module gate, so a role that
        // lists real capabilities but not the gate would pass the suites and then
        // be redirected out of the panel in the browser.
        $names[] = WebsitePermission::DASHBOARD_ACCESS;

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
