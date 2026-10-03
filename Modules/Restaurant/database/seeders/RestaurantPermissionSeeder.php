<?php

namespace Modules\Restaurant\Database\Seeders;

use Illuminate\Database\Seeder;
use Modules\Restaurant\Support\RestaurantPermission;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

/**
 * Registers the Restaurant taxonomy and applies the config/restaurant.php role
 * map.
 *
 * Idempotent and additive, so it is safe to re-run. Grants accumulate rather
 * than being synced, because a role may legitimately carry permissions granted
 * outside this file; syncPermissions() would strip them. Revocations are the
 * exception, since a permission that should no longer apply to a role has to
 * actually go away, otherwise a demoted manager keeps their old reach.
 */
class RestaurantPermissionSeeder extends Seeder
{
    public function run(): void
    {
        foreach (RestaurantPermission::all() as $name) {
            Permission::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }

        $roles = (array) config('restaurant.roles', []);

        // Everything outside the map loses Restaurant grants. This is what
        // actually tightens the module: previously any role holding
        // access_restaurant_dashboard could reach every admin route, and
        // `waiter` in particular still carries that permission.
        foreach (Role::where('guard_name', 'web')->get() as $role) {
            if (array_key_exists($role->name, $roles)) {
                continue;
            }

            $this->revoke($role, RestaurantPermission::all());
        }

        foreach ($roles as $roleName => $grants) {
            $role = Role::findOrCreate($roleName, 'web');

            $this->revoke($role, RestaurantPermission::all());
            $role->givePermissionTo($this->permissionsFor($roleName, $grants));
        }

        RestaurantPermission::forgetCache();
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
            return RestaurantPermission::all();
        }

        // Intersect with the taxonomy so a stale entry in config cannot create
        // a permission that nothing checks.
        $names = array_values(array_intersect($grants, RestaurantPermission::all()));

        if ($names === []) {
            $this->command?->warn("Restaurant: role '{$roleName}' has no valid permissions in config/restaurant.php.");
        }

        // Every role the map names also gets the module gate. It is what admits
        // someone to the back office or the POS terminal at all, so a role that
        // lists real capabilities but not the gate would pass the suites and
        // then 403 in the browser: the test concern GrantsRestaurantRoles
        // already added the gate on top of the config list, so the two did not
        // agree and the waiter shipped without it.
        $names[] = RestaurantPermission::DASHBOARD_ACCESS;

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
