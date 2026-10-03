<?php

namespace Modules\Restaurant\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Restaurant\Support\RestaurantPermission;
use Modules\Restaurant\Tests\Concerns\GrantsRestaurantRoles;
use Tests\TestCase;

/**
 * The back office used to sit behind `access_restaurant_dashboard` alone, which
 * meant a waiter could delete menu items, redraw the floor plan, adjust stock,
 * browse every customer and export the sales report. These tests pin the split
 * in place, and are the reason a future change to the config map that widens
 * waiter will fail here rather than quietly shipping.
 */
class RestaurantRoleMatrixTest extends TestCase
{
    use DatabaseTransactions, GrantsRestaurantRoles;

    private User $user;

    private User $waiter;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = $this->staffWith(RoleEnum::RESTAURANT_MANAGER->value);
        $this->waiter = $this->staffWith(RoleEnum::WAITER->value);

        $this->grantRestaurantRole(RoleEnum::RESTAURANT_MANAGER->value);
        $this->grantRestaurantRole(RoleEnum::WAITER->value);
    }

    private function staffWith(string $roleName): User
    {
        $user = User::factory()->create([
            'type' => 'staff',
            'status' => 'active',
        ]);
        $user->assignRole($roleName);

        return $user;
    }

    public function test_manager_holds_the_whole_taxonomy(): void
    {
        foreach (RestaurantPermission::all() as $permission) {
            $this->assertTrue(
                $this->user->can($permission),
                "restaurant_manager should hold {$permission}"
            );
        }
    }

    public function test_waiter_can_still_serve_a_table(): void
    {
        // The POS is the waiter's actual job, so the operational capabilities
        // have to survive the split.
        foreach ([
            RestaurantPermission::DASHBOARD_ACCESS,
            RestaurantPermission::ORDERS_READ,
            RestaurantPermission::ORDERS_CREATE,
            RestaurantPermission::ORDERS_UPDATE,
            RestaurantPermission::ORDERS_ACCEPT,
            RestaurantPermission::ORDERS_REJECT,
            RestaurantPermission::PAYMENTS_READ,
            RestaurantPermission::PAYMENTS_CREATE,
            RestaurantPermission::KITCHEN_READ,
            RestaurantPermission::KITCHEN_UPDATE,
            RestaurantPermission::TABLES_READ,
            RestaurantPermission::MENU_ITEMS_READ,
        ] as $permission) {
            $this->assertTrue(
                $this->waiter->can($permission),
                "waiter should hold {$permission}"
            );
        }
    }

    public function test_waiter_cannot_reach_the_back_office(): void
    {
        // This is the regression that motivated the work: every one of these was
        // reachable by anyone holding dashboard access.
        $forbidden = [
            RestaurantPermission::MENU_CATEGORIES_CREATE,
            RestaurantPermission::MENU_CATEGORIES_UPDATE,
            RestaurantPermission::MENU_CATEGORIES_DELETE,
            RestaurantPermission::MENU_ITEMS_CREATE,
            RestaurantPermission::MENU_ITEMS_UPDATE,
            RestaurantPermission::MENU_ITEMS_DELETE,
            RestaurantPermission::TABLES_CREATE,
            RestaurantPermission::TABLES_UPDATE,
            RestaurantPermission::TABLES_DELETE,
            RestaurantPermission::STOCK_READ,
            RestaurantPermission::STOCK_CREATE,
            RestaurantPermission::STOCK_UPDATE,
            RestaurantPermission::STOCK_DELETE,
            RestaurantPermission::STOCK_MOVEMENTS,
            RestaurantPermission::RECIPES_CREATE,
            RestaurantPermission::RECIPES_DELETE,
            RestaurantPermission::CUSTOMERS_READ,
            RestaurantPermission::CUSTOMERS_CREATE,
            RestaurantPermission::CUSTOMERS_UPDATE,
            RestaurantPermission::SETTINGS_READ,
            RestaurantPermission::SETTINGS_UPDATE,
            RestaurantPermission::REPORTS_SALES_READ,
            RestaurantPermission::REPORTS_SALES_EXPORT,
            RestaurantPermission::REPORTS_POPULAR_ITEMS_READ,
            RestaurantPermission::REPORTS_WAITER_PERFORMANCE_READ,
        ];

        foreach ($forbidden as $permission) {
            $this->assertFalse(
                $this->waiter->can($permission),
                "waiter must not hold {$permission}"
            );
        }
    }

    public function test_waiter_cannot_refund_or_void(): void
    {
        // Both move money or rewrite an order's financial state.
        $this->assertFalse($this->waiter->can(RestaurantPermission::PAYMENTS_REFUND));
        $this->assertFalse($this->waiter->can(RestaurantPermission::ORDERS_VOID));
        $this->assertFalse($this->waiter->can(RestaurantPermission::ORDERS_DELETE));

        $this->assertTrue($this->user->can(RestaurantPermission::PAYMENTS_REFUND));
        $this->assertTrue($this->user->can(RestaurantPermission::ORDERS_VOID));
    }

    public function test_waiter_is_refused_the_back_office_routes(): void
    {
        // Route-level proof, so a missing `can:` on a route fails here even if the
        // permission matrix itself is still correct.
        $this->actingAs($this->waiter)->get('/restaurant-admin/stock')->assertForbidden();
        $this->actingAs($this->waiter)->get('/restaurant-admin/customers')->assertForbidden();
        $this->actingAs($this->waiter)->get('/restaurant-admin/settings')->assertForbidden();
        $this->actingAs($this->waiter)->get('/restaurant-admin/reports/sales')->assertForbidden();
        $this->actingAs($this->waiter)->get('/restaurant-admin/reports/sales/export')->assertForbidden();
        $this->actingAs($this->waiter)->get('/restaurant-admin/tables')->assertOk();
        $this->actingAs($this->waiter)->get('/restaurant-admin/kitchen')->assertOk();

        $this->actingAs($this->waiter)
            ->post('/restaurant-admin/tables/store', ['number' => 'NOPE', 'capacity' => 2])
            ->assertForbidden();

        $this->assertDatabaseMissing('restaurant_tables', ['number' => 'NOPE']);
    }

    public function test_manager_reaches_the_back_office_routes(): void
    {
        $this->actingAs($this->user)->get('/restaurant-admin/stock')->assertOk();
        $this->actingAs($this->user)->get('/restaurant-admin/customers')->assertOk();
        $this->actingAs($this->user)->get('/restaurant-admin/settings')->assertOk();
    }

    public function test_a_role_outside_the_map_keeps_nothing(): void
    {
        // Whatever holds dashboard access, the back office stays shut.
        $this->grantRestaurantPermissions('front_desk_agent', [
            RestaurantPermission::DASHBOARD_ACCESS,
        ]);

        $agent = $this->staffWith('front_desk_agent');

        $this->assertTrue($agent->can(RestaurantPermission::DASHBOARD_ACCESS));
        $this->assertFalse($agent->can(RestaurantPermission::TABLES_READ));
        $this->assertFalse($agent->can(RestaurantPermission::SETTINGS_READ));
        $this->assertFalse($agent->can(RestaurantPermission::STOCK_READ));
    }

    public function test_every_configured_grant_is_a_real_permission(): void
    {
        // Guards against a typo in config/restaurant.php that silently grants
        // nothing and reads like it works.
        foreach (array_keys((array) config('restaurant.roles', [])) as $roleName) {
            foreach (RestaurantPermission::forRole($roleName) as $permission) {
                $this->assertContains(
                    $permission,
                    RestaurantPermission::all(),
                    "config grants {$roleName} the unknown permission {$permission}"
                );
            }
        }
    }

    public function test_config_only_names_permissions_the_taxonomy_defines(): void
    {
        foreach (RestaurantPermission::configuredNames() as $name) {
            $this->assertContains(
                $name,
                RestaurantPermission::all(),
                "config references {$name}, which the taxonomy does not define"
            );
        }
    }

    public function test_export_permissions_are_declared_in_config(): void
    {
        // Exports are the capability most likely to be granted by accident, so
        // they are asserted against the config list rather than assumed.
        $exports = RestaurantPermission::exportPermissions();

        $this->assertContains(RestaurantPermission::REPORTS_SALES_EXPORT, $exports);
        $this->assertContains(RestaurantPermission::REPORTS_POPULAR_ITEMS_EXPORT, $exports);

        $this->assertFalse($this->waiter->can(RestaurantPermission::REPORTS_SALES_EXPORT));
        $this->assertTrue($this->user->can(RestaurantPermission::REPORTS_SALES_EXPORT));
    }

    public function test_store_keeper_gets_stock_without_the_rest_of_the_back_office(): void
    {
        $this->grantRestaurantRole(RoleEnum::STORE_KEEPER->value);
        $keeper = $this->staffWith(RoleEnum::STORE_KEEPER->value);

        foreach ([
            RestaurantPermission::STOCK_READ,
            RestaurantPermission::STOCK_CREATE,
            RestaurantPermission::STOCK_UPDATE,
            RestaurantPermission::STOCK_DELETE,
            RestaurantPermission::STOCK_MOVEMENTS,
            RestaurantPermission::RECIPES_CREATE,
        ] as $permission) {
            $this->assertTrue($keeper->can($permission), "store_keeper should hold {$permission}");
        }

        $this->assertFalse($keeper->can(RestaurantPermission::SETTINGS_UPDATE));
        $this->assertFalse($keeper->can(RestaurantPermission::CUSTOMERS_READ));
        $this->assertFalse($keeper->can(RestaurantPermission::ORDERS_CREATE));
    }
}
