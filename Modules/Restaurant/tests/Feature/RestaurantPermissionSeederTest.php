<?php

namespace Modules\Restaurant\Tests\Feature;

use App\Enums\RoleEnum;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Restaurant\Database\Seeders\RestaurantPermissionSeeder;
use Modules\Restaurant\Support\RestaurantPermission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * RestaurantPermissionSeeder revokes Restaurant grants from every role that is
 * missing from config/restaurant.php, which is what actually tightened the
 * module. That made the config table load-bearing for administrators too: with
 * `admin` absent, seeding the module on its own silently revoked all 46
 * Restaurant permissions from every admin, despite RoleSeeder granting them
 * moments earlier.
 *
 * These tests seed the module in isolation, the way `php artisan module:seed
 * Restaurant` does, and assert the resulting matrix.
 */
class RestaurantPermissionSeederTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RestaurantPermissionSeeder::class);
    }

    public function test_every_defined_permission_exists_in_the_guard(): void
    {
        foreach (RestaurantPermission::all() as $name) {
            $this->assertDatabaseHas('permissions', [
                'name' => $name,
                'guard_name' => 'web',
            ]);
        }
    }

    public function test_admin_keeps_every_restaurant_permission(): void
    {
        $admin = Role::findByName(RoleEnum::ADMIN->value, 'web');

        $this->assertTrue(
            $admin->hasPermissionTo(RestaurantPermission::SETTINGS_UPDATE),
            'Seeding the module on its own must not strip Restaurant access from an administrator.'
        );

        // Not just one permission: the whole taxonomy has to survive.
        $missing = array_values(array_filter(
            RestaurantPermission::all(),
            fn (string $name): bool => ! $admin->hasPermissionTo($name)
        ));

        $this->assertSame([], $missing, 'Administrator is missing: '.implode(', ', $missing));
    }

    public function test_role_map_is_the_single_source_of_grants(): void
    {
        $map = (array) config('restaurant.roles');

        foreach ($map as $roleName => $grants) {
            $role = Role::findByName($roleName, 'web');

            $expected = in_array('*', $grants, true)
                ? RestaurantPermission::all()
                : array_values(array_intersect($grants, RestaurantPermission::all()));

            $missing = array_values(array_filter(
                $expected,
                fn (string $name): bool => ! $role->hasPermissionTo($name)
            ));

            $this->assertSame([], $missing, "Role '{$roleName}' is missing: ".implode(', ', $missing));
        }
    }

    public function test_roles_outside_the_map_hold_no_restaurant_permissions(): void
    {
        $map = (array) config('restaurant.roles');

        foreach (Role::where('guard_name', 'web')->get() as $role) {
            if (array_key_exists($role->name, $map)) {
                continue;
            }

            $leaked = array_values(array_filter(
                RestaurantPermission::all(),
                fn (string $name): bool => $role->hasPermissionTo($name)
            ));

            $this->assertSame(
                [],
                $leaked,
                "Role '{$role->name}' is outside the config map but holds: ".implode(', ', $leaked)
            );
        }
    }

    public function test_seeding_twice_is_stable(): void
    {
        $before = Role::findByName(RoleEnum::ADMIN->value, 'web')
            ->permissions->pluck('name')
            ->intersect(RestaurantPermission::all())
            ->sort()
            ->values()
            ->all();

        $this->seed(RestaurantPermissionSeeder::class);

        $after = Role::findByName(RoleEnum::ADMIN->value, 'web')
            ->permissions->pluck('name')
            ->intersect(RestaurantPermission::all())
            ->sort()
            ->values()
            ->all();

        $this->assertSame($before, $after);
    }

    public function test_waiter_does_not_gain_back_offoffice_access_from_the_map(): void
    {
        $waiter = Role::findByName(RoleEnum::WAITER->value, 'web');

        foreach ([
            RestaurantPermission::MENU_ITEMS_UPDATE,
            RestaurantPermission::TABLES_CREATE,
            RestaurantPermission::STOCK_UPDATE,
            RestaurantPermission::CUSTOMERS_READ,
            RestaurantPermission::SETTINGS_UPDATE,
            RestaurantPermission::PAYMENTS_REFUND,
            RestaurantPermission::REPORTS_SALES_EXPORT,
        ] as $permission) {
            $this->assertFalse(
                $waiter->hasPermissionTo($permission),
                "Waiter must not hold '{$permission}'."
            );
        }
    }

    /**
     * Group oversight reads and exports; it does not operate.
     *
     * A GGM covers several properties, so a blanket grant would multiply any
     * mistaken write by the number of hotels in the group. This asserts the
     * *absence* of every write capability rather than the presence of a list,
     * so adding a new write permission to the taxonomy cannot quietly hand it
     * to group management.
     */
    public function test_group_oversight_is_read_only(): void
    {
        $map = (array) config('restaurant.roles');
        $grants = $map['ggm'] ?? [];

        $this->assertNotSame(['*'], $grants, 'Group oversight must not be a blanket grant.');

        // Of everything the taxonomy defines, what has this role been handed
        // that is not a read, an export or dashboard access?
        $writes = array_values(array_filter(
            RestaurantPermission::all(),
            fn (string $name): bool => in_array($name, $grants, true)
                && ! str_ends_with($name, '.read')
                && ! str_ends_with($name, '.export')
                && $name !== RestaurantPermission::DASHBOARD_ACCESS
        ));

        $this->assertSame([], $writes, 'Group oversight must not hold: '.implode(', ', $writes));
    }

    public function test_property_general_management_keeps_full_access(): void
    {
        $gm = Role::findByName('gm', 'web');

        // The GM authorises refunds and sets local tax, so this one is blanket.
        foreach ([
            RestaurantPermission::PAYMENTS_REFUND,
            RestaurantPermission::ORDERS_VOID,
            RestaurantPermission::SETTINGS_UPDATE,
            RestaurantPermission::STOCK_DELETE,
        ] as $permission) {
            $this->assertTrue(
                $gm->hasPermissionTo($permission),
                "The property GM must hold '{$permission}'."
            );
        }
    }

    /**
     * A module may only know what its capabilities are, never who holds them.
     *
     * The rule is about *authorization decisions*, so that is what this checks:
     * code must not ask "is this person a waiter/gm/admin?" to decide access.
     * The two patterns it looks for are the two ways that actually happens —
     * Spatie's role predicates, and a role string compared against a role
     * column or held as a role field.
     *
     * Earlier wording of this test scanned for role names anywhere in the
     * module and failed on `WaiterAuthController`, on `GUEST` inside a
     * permission label and on the word appearing in the waiter's own route
     * paths. Those are names, not decisions. A module is allowed to know it has
     * a waiter terminal; it is not allowed to grant capability by role.
     */
    public function test_no_role_name_is_used_to_decide_access(): void
    {
        $offenders = [];

        foreach ($this->modulePhpFiles() as $file) {
            $contents = (string) file_get_contents($file);

            // Drop comments, docblocks and string literals: only executable
            // code can make an authorization decision.
            $code = (string) preg_replace(
                [
                    '#/\*.*?\*/#s',
                    '#//[^\n]*#',
                    '#^\s*\*.*$#m',
                    '#\'(?:[^\'\\\\]|\\\\.)*\'#s',
                    '#"(?:[^"\\\\]|\\\\.)*"#s',
                ],
                '',
                $contents
            );

            // Spatie role predicates.
            if (preg_match('/->\s*(hasRole|hasAnyRole|hasAllRoles|hasExactRoles)\s*\(/', $code, $m)) {
                $offenders[] = $this->rel($file).' → '.$m[1].'()';
            }

            // Comparing identity: $user->role === 'gm', role_id lookups, etc.
            if (preg_match('/\b(role|role_id|roles)\b\s*(===|==|!==|!=|\bin\s*\(|\.contains\()/i', $code, $m)) {
                $offenders[] = $this->rel($file).' → compares '.$m[1];
            }
        }

        $this->assertSame(
            [],
            $offenders,
            "Module logic must not branch on role:\n".implode("\n", $offenders)
        );
    }

    private function rel(string $file): string
    {
        return str_replace(base_path().DIRECTORY_SEPARATOR, '', $file);
    }

    /**
     * @return array<int, string>
     */
    private function modulePhpFiles(): array
    {
        $files = [];

        foreach (['app', 'database'] as $directory) {
            $iterator = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator(
                    module_path('Restaurant', $directory),
                    \FilesystemIterator::SKIP_DOTS
                )
            );

            /** @var \SplFileInfo $file */
            foreach ($iterator as $file) {
                // Seeders are configuration applied by role name on purpose.
                if ($file->getExtension() === 'php'
                    && ! str_contains($file->getPathname(), DIRECTORY_SEPARATOR.'seeders'.DIRECTORY_SEPARATOR)) {
                    $files[] = $file->getPathname();
                }
            }
        }

        return $files;
    }
}
