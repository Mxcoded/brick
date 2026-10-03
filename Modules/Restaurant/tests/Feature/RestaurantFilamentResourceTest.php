<?php

namespace Modules\Restaurant\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Modules\Restaurant\Filament\Pages\RestaurantReports;
use Modules\Restaurant\Filament\Pages\RestaurantSettings;
use Modules\Restaurant\Filament\Resources\Customers\CustomerResource;
use Modules\Restaurant\Filament\Resources\Customers\Pages\ListCustomers;
use Modules\Restaurant\Filament\Resources\DiningTables\DiningTableResource;
use Modules\Restaurant\Filament\Resources\DiningTables\Pages\ListDiningTables;
use Modules\Restaurant\Filament\Resources\MenuCategories\MenuCategoryResource;
use Modules\Restaurant\Filament\Resources\MenuCategories\Pages\ListMenuCategories;
use Modules\Restaurant\Filament\Resources\MenuItems\MenuItemResource;
use Modules\Restaurant\Filament\Resources\MenuItems\Pages\ListMenuItems;
use Modules\Restaurant\Filament\Resources\MenuItems\RelationManagers\RecipesRelationManager;
use Modules\Restaurant\Filament\Resources\Orders\OrderResource;
use Modules\Restaurant\Filament\Resources\Orders\Pages\ListOrders;
use Modules\Restaurant\Filament\Resources\StockItems\Pages\ListStockItems;
use Modules\Restaurant\Filament\Resources\StockItems\StockItemResource;
use Modules\Restaurant\Models\Customer;
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Models\MenuItem;
use Modules\Restaurant\Models\Order;
use Modules\Restaurant\Models\StockItem;
use Modules\Restaurant\Models\Table;
use Modules\Restaurant\Support\OrderStatus;
use Modules\Restaurant\Support\RestaurantPermission;
use Modules\Restaurant\Tests\Concerns\GrantsRestaurantRoles;
use PHPUnit\Framework\Attributes\Test;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * The Filament back office.
 *
 * RestaurantRoleMatrixTest covers the legacy /restaurant-admin routes, which is
 * where the original over-permission lived. These tests cover the replacement:
 * that each screen is reachable by the roles that should reach it, that the ones
 * that should not are refused rather than merely hidden, and that the screens
 * which look financial cannot be used to re-price or delete history.
 *
 * The distinction between hidden and refused matters. A button the viewer cannot
 * press is a usability problem; a route that returns the page anyway is a
 * security problem, so the authorization assertions here target the static
 * can* methods and the rendered pages rather than the presence of buttons.
 */
class RestaurantFilamentResourceTest extends TestCase
{
    use DatabaseTransactions, GrantsRestaurantRoles;

    protected function setUp(): void
    {
        parent::setUp();

        $this->grantRestaurantRole(RoleEnum::RESTAURANT_MANAGER->value);
        $this->grantRestaurantRole(RoleEnum::WAITER->value);
    }

    // ── Reachability ───────────────────────────────────────────────────

    #[Test]
    public function a_manager_reaches_every_back_office_screen(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::RESTAURANT_MANAGER->value));

        foreach ([
            MenuCategoryResource::class,
            MenuItemResource::class,
            DiningTableResource::class,
            OrderResource::class,
            StockItemResource::class,
            CustomerResource::class,
        ] as $resource) {
            $this->assertTrue($resource::canAccess(), $resource.' should be reachable by a manager');
        }

        $this->assertTrue(RestaurantSettings::canAccess());
        $this->assertTrue(RestaurantReports::canAccess());
    }

    #[Test]
    public function a_waiter_is_refused_the_manager_screens(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::WAITER->value));

        // Waiters hold reads on menu and tables, so they can open these.
        $this->assertTrue(MenuCategoryResource::canViewAny());
        $this->assertTrue(MenuItemResource::canViewAny());
        $this->assertTrue(DiningTableResource::canViewAny());
        $this->assertTrue(OrderResource::canViewAny());

        // And nothing that changes the venue.
        $this->assertFalse(MenuItemResource::canCreate());
        $this->assertFalse(MenuItemResource::canEdit(new MenuItem));
        $this->assertFalse(MenuItemResource::canDelete(new MenuItem));
        $this->assertFalse(DiningTableResource::canCreate());
        $this->assertFalse(DiningTableResource::canEdit(new Table));
        $this->assertFalse(CustomerResource::canViewAny());
        $this->assertFalse(CustomerResource::canCreate());
        $this->assertFalse(StockItemResource::canViewAny());
        $this->assertFalse(RestaurantSettings::canAccess());
        $this->assertFalse(RestaurantReports::canAccess());
    }

    #[Test]
    public function a_waiter_cannot_refund_or_void_through_the_order_screen(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::WAITER->value));

        $this->assertFalse(RestaurantPermission::allows(RestaurantPermission::PAYMENTS_REFUND));
        $this->assertFalse(RestaurantPermission::allows(RestaurantPermission::ORDERS_VOID));
    }

    #[Test]
    public function the_pages_render_for_a_manager(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::RESTAURANT_MANAGER->value));

        foreach ([
            ListMenuCategories::class,
            ListMenuItems::class,
            ListDiningTables::class,
            ListOrders::class,
            ListStockItems::class,
            ListCustomers::class,
        ] as $page) {
            Livewire::test($page)->assertOk();
        }
    }

    #[Test]
    public function the_menu_item_list_renders_its_row_actions(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::RESTAURANT_MANAGER->value));

        $item = MenuItem::create([
            'restaurant_menu_categories_id' => MenuCategory::create(['name' => 'Starters '.uniqid()])->id,
            'name' => 'Soup',
            'price' => 1200,
        ]);

        // Row action URLs are resolved per record, so an empty list page never
        // evaluates them and cannot catch a broken one.
        Livewire::test(ListMenuItems::class)
            ->assertOk()
            ->assertSee('Recipe');
    }

    #[Test]
    public function the_recipe_action_deep_links_to_a_real_tab(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::RESTAURANT_MANAGER->value));

        $item = MenuItem::create([
            'restaurant_menu_categories_id' => MenuCategory::create(['name' => 'Mains '.uniqid()])->id,
            'name' => 'Stew',
            'price' => 2500,
        ]);

        $url = MenuItemResource::getUrl('edit', [
            'record' => $item,
            'activeRelationManager' => 'recipe',
        ]);

        $this->assertStringContainsString('activeRelationManager=recipe', $url);

        // And the tab the action targets is the one the resource actually registers.
        $this->assertSame(['recipe' => RecipesRelationManager::class], MenuItemResource::getRelations());

        $this->get($url)
            ->assertOk()
            ->assertSee('Recipe');
    }

    // ── Orders are not a CRUD screen ───────────────────────────────────

    #[Test]
    public function orders_cannot_be_created_or_deleted_in_the_back_office(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::RESTAURANT_MANAGER->value));

        // The POS and the online checkout own cart construction and pricing, so
        // the back office must not be able to invent or remove an order.
        $this->assertFalse(OrderResource::canCreate());
        $this->assertFalse(OrderResource::canEdit(new Order));
        $this->assertFalse(OrderResource::canDelete(new Order));
    }

    #[Test]
    public function order_status_transitions_are_guarded(): void
    {
        $pending = new Order(['status' => OrderStatus::PENDING]);

        $this->assertTrue(OrderStatus::canAccept($pending));
        $this->assertTrue(OrderStatus::canReject($pending));
        // Nothing to void before it is accepted.
        $this->assertFalse(OrderStatus::canVoid($pending));

        $accepted = new Order(['status' => OrderStatus::ACCEPTED]);
        $this->assertTrue(OrderStatus::canVoid($accepted));

        // And a rejected order is closed to all three.
        $rejected = new Order(['status' => OrderStatus::REJECTED]);
        $this->assertFalse(OrderStatus::canAccept($rejected));
        $this->assertFalse(OrderStatus::canReject($rejected));
        $this->assertFalse(OrderStatus::canVoid($rejected));
        $this->assertTrue(OrderStatus::isClosed($rejected));
    }

    // ── Stock integrity ────────────────────────────────────────────────

    #[Test]
    public function a_table_that_has_served_orders_cannot_be_deleted(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::RESTAURANT_MANAGER->value));

        $table = Table::create(['number' => 'FIL-'.uniqid(), 'capacity' => 4]);
        $this->assertTrue(DiningTableResource::canDelete($table));

        Order::create([
            'type' => 'table',
            'source_id' => $table->id,
            'status' => OrderStatus::PENDING,
            'subtotal' => 1000,
            'grand_total' => 1000,
        ]);

        $this->assertFalse(
            DiningTableResource::canDelete($table->fresh()),
            'Deleting a table would orphan the orders that reference it.'
        );
    }

    #[Test]
    public function an_ingredient_used_by_a_recipe_cannot_be_deleted(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::RESTAURANT_MANAGER->value));

        $stock = StockItem::create([
            'name' => 'Tomatoes '.uniqid(),
            'unit' => 'kg',
            'stock_quantity' => 10,
            'min_stock_level' => 2,
        ]);
        $this->assertTrue(StockItemResource::canDelete($stock));

        $item = MenuItem::create([
            'restaurant_menu_categories_id' => MenuCategory::create(['name' => 'Mains '.uniqid()])->id,
            'name' => 'Stew',
            'price' => 2500,
        ]);

        $item->recipeItems()->create([
            'restaurant_stock_item_id' => $stock->id,
            'quantity' => 0.5,
        ]);

        $this->assertFalse(
            StockItemResource::canDelete($stock->fresh()),
            'Deleting an ingredient would leave the recipe pointing at a trashed row.'
        );
    }

    #[Test]
    public function customers_cannot_be_deleted_at_all(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::RESTAURANT_MANAGER->value));

        // Guest history is referenced by orders and Finance, and the taxonomy
        // deliberately has no customers.delete.
        $this->assertFalse(CustomerResource::canDelete(new Customer));
    }

    // ── Soft deletes ───────────────────────────────────────────────────

    #[Test]
    public function menu_items_include_trashed_rows_so_they_can_be_restored(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::RESTAURANT_MANAGER->value));

        $item = MenuItem::create([
            'restaurant_menu_categories_id' => MenuCategory::create(['name' => 'Sides '.uniqid()])->id,
            'name' => 'Trashed dish',
            'price' => 500,
        ]);
        $item->delete();

        $this->assertTrue(
            MenuItemResource::getEloquentQuery()->whereKey($item->id)->exists(),
            'Trashed menu items must remain visible in the admin list.'
        );
        $this->assertTrue(MenuItemResource::canRestore($item));
    }

    #[Test]
    public function a_waiter_cannot_restore_a_deleted_menu_item(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::WAITER->value));

        $this->assertFalse(
            RestaurantPermission::allows(RestaurantPermission::MENU_ITEMS_RESTORE)
        );
    }

    // ── Settings ───────────────────────────────────────────────────────

    #[Test]
    public function settings_are_editable_only_with_the_settings_permission(): void
    {
        $manager = $this->staffWith(RoleEnum::RESTAURANT_MANAGER->value);
        $this->actingAs($manager);
        $this->assertTrue(RestaurantSettings::canAccess());

        $waiter = $this->staffWith(RoleEnum::WAITER->value);
        $this->actingAs($waiter);
        $this->assertFalse(RestaurantSettings::canAccess());
    }

    // ── Reports ────────────────────────────────────────────────────────

    #[Test]
    public function reports_need_a_reporting_permission_to_appear(): void
    {
        $this->actingAs($this->staffWith(RoleEnum::WAITER->value));
        $this->assertFalse(RestaurantReports::canAccess());

        $this->actingAs($this->staffWith(RoleEnum::RESTAURANT_MANAGER->value));
        $this->assertTrue(RestaurantReports::canAccess());
    }

    private function staffWith(string $roleName): User
    {
        $user = User::factory()->create();

        $role = Role::findOrCreate($roleName, 'web');
        $user->assignRole($role);

        return $user->fresh();
    }
}
