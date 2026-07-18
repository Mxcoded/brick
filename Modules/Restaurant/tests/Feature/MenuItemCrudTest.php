<?php

namespace Modules\Restaurant\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Modules\Restaurant\Database\Seeders\CategorySeeder;
use Modules\Restaurant\Database\Seeders\MenuItemSeeder;
use Modules\Restaurant\Models\MenuCategory;
use Modules\Restaurant\Models\MenuItem;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class MenuItemCrudTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    private int $propertyCount;

    protected function setUp(): void
    {
        parent::setUp();

        $role = Role::firstOrCreate(['name' => RoleEnum::ADMIN->value, 'guard_name' => 'web']);
        $permission = Permission::firstOrCreate(['name' => 'access_restaurant_dashboard', 'guard_name' => 'web']);
        if (! $role->hasPermissionTo($permission)) {
            $role->givePermissionTo($permission);
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->admin = User::factory()->create([
            'type' => 'staff',
            'status' => 'active',
        ]);
        $this->admin->assignRole(RoleEnum::ADMIN->value);

        $this->propertyCount = DB::table('properties')->where('is_active', true)->count();

        $this->seedCategoriesWithIds();
    }

    private function seedCategoriesWithIds(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        DB::table('restaurant_menu_items')->delete();
        DB::table('restaurant_menu_categories')->delete();
        DB::statement('SET FOREIGN_KEY_CHECKS=1');

        (new CategorySeeder)->run();
    }

    // ─── Seeder Tests ────────────────────────────────────────────────

    public function test_category_seeder_creates_categories_for_each_property(): void
    {
        $expected = 4 * $this->propertyCount;

        $this->assertDatabaseHas('restaurant_menu_categories', ['name' => 'Appetizers']);
        $this->assertDatabaseHas('restaurant_menu_categories', ['name' => 'Main Courses']);
        $this->assertDatabaseHas('restaurant_menu_categories', ['name' => 'Desserts']);
        $this->assertDatabaseHas('restaurant_menu_categories', ['name' => 'Beverages']);

        $this->assertEquals($expected, MenuCategory::withoutGlobalScopes()->count());
    }

    public function test_menu_item_seeder_creates_expected_items(): void
    {
        $before = MenuItem::withoutGlobalScopes()->count();
        (new MenuItemSeeder)->run();
        $after = MenuItem::withoutGlobalScopes()->count();

        // 22 items × number of active properties
        $expected = 22 * $this->propertyCount;
        $this->assertEquals($expected, $after - $before, "MenuItemSeeder should create exactly {$expected} items");
    }

    public function test_seeded_items_have_correct_prices(): void
    {
        (new MenuItemSeeder)->run();

        $jollof = MenuItem::withoutGlobalScopes()->where('name', 'Jollof Rice & Chicken')->first();
        $this->assertNotNull($jollof);
        $this->assertEquals(3500.00, (float) $jollof->price);

        $coke = MenuItem::withoutGlobalScopes()->where('name', 'Coke')->first();
        $this->assertNotNull($coke);
        $this->assertEquals(500.00, (float) $coke->price);

        $iceCream = MenuItem::withoutGlobalScopes()->where('name', 'Ice Cream')->first();
        $this->assertNotNull($iceCream);
        $this->assertEquals(1500.00, (float) $iceCream->price);
    }

    public function test_seeded_items_belong_to_correct_categories(): void
    {
        (new MenuItemSeeder)->run();

        $appetizersId = MenuCategory::withoutGlobalScopes()->where('name', 'Appetizers')->first()->id;
        $mainCoursesId = MenuCategory::withoutGlobalScopes()->where('name', 'Main Courses')->first()->id;
        $beveragesId = MenuCategory::withoutGlobalScopes()->where('name', 'Beverages')->first()->id;

        $springRolls = MenuItem::withoutGlobalScopes()->where('name', 'Spring Rolls')->first();
        $this->assertEquals($appetizersId, $springRolls->restaurant_menu_categories_id);

        $grilledFish = MenuItem::withoutGlobalScopes()->where('name', 'Grilled Fish')->first();
        $this->assertEquals($mainCoursesId, $grilledFish->restaurant_menu_categories_id);

        $chapman = MenuItem::withoutGlobalScopes()->where('name', 'Chapman')->first();
        $this->assertEquals($beveragesId, $chapman->restaurant_menu_categories_id);
    }

    public function test_seeder_is_idempotent(): void
    {
        (new MenuItemSeeder)->run();
        $countAfterFirst = MenuItem::withoutGlobalScopes()->count();

        (new MenuItemSeeder)->run();
        $countAfterSecond = MenuItem::withoutGlobalScopes()->count();

        $this->assertEquals($countAfterFirst, $countAfterSecond, 'Running seeder twice should not duplicate items');
    }

    public function test_seeded_items_are_available_by_default(): void
    {
        (new MenuItemSeeder)->run();

        $unavailable = MenuItem::withoutGlobalScopes()->where('is_available', false)->count();
        $this->assertEquals(0, $unavailable, 'All seeded items should be available');
    }

    // ─── Controller: Create ──────────────────────────────────────────

    public function test_add_menu_item_via_controller(): void
    {
        $categoryId = MenuCategory::withoutGlobalScopes()->where('name', 'Main Courses')->first()->id;

        $response = $this->actingAs($this->admin)->post(route('restaurant.admin.add-item'), [
            'restaurant_menu_categories_id' => $categoryId,
            'name' => 'Test Suya',
            'description' => 'Spicy grilled beef suya.',
            'price' => 2000.00,
            'is_available' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('restaurant_menu_items', [
            'name' => 'Test Suya',
            'price' => 2000.00,
            'restaurant_menu_categories_id' => $categoryId,
            'is_available' => true,
        ]);
    }

    public function test_add_menu_item_requires_name(): void
    {
        $categoryId = MenuCategory::withoutGlobalScopes()->where('name', 'Beverages')->first()->id;

        $response = $this->actingAs($this->admin)->post(route('restaurant.admin.add-item'), [
            'restaurant_menu_categories_id' => $categoryId,
            'price' => 500.00,
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_add_menu_item_requires_price(): void
    {
        $categoryId = MenuCategory::withoutGlobalScopes()->where('name', 'Beverages')->first()->id;

        $response = $this->actingAs($this->admin)->post(route('restaurant.admin.add-item'), [
            'restaurant_menu_categories_id' => $categoryId,
            'name' => 'Water',
        ]);

        $response->assertSessionHasErrors('price');
    }

    public function test_add_menu_item_requires_valid_category(): void
    {
        $response = $this->actingAs($this->admin)->post(route('restaurant.admin.add-item'), [
            'restaurant_menu_categories_id' => 9999,
            'name' => 'Ghost Item',
            'price' => 100.00,
        ]);

        $response->assertSessionHasErrors('restaurant_menu_categories_id');
    }

    public function test_add_menu_item_with_default_availability(): void
    {
        $categoryId = MenuCategory::withoutGlobalScopes()->where('name', 'Desserts')->first()->id;

        $this->actingAs($this->admin)->post(route('restaurant.admin.add-item'), [
            'restaurant_menu_categories_id' => $categoryId,
            'name' => 'Puff Puff',
            'price' => 800.00,
        ]);

        $item = MenuItem::withoutGlobalScopes()->where('name', 'Puff Puff')->first();
        $this->assertNotNull($item);
        $this->assertTrue((bool) $item->is_available, 'Item should be available by default');
    }

    // ─── Controller: Update ──────────────────────────────────────────

    public function test_update_menu_item(): void
    {
        (new MenuItemSeeder)->run();

        $item = MenuItem::withoutGlobalScopes()->where('name', 'Coke')->first();
        $categoryId = $item->restaurant_menu_categories_id;

        $response = $this->actingAs($this->admin)->post(route('restaurant.admin.update-item', $item->id), [
            'restaurant_menu_categories_id' => $categoryId,
            'name' => 'Coke Zero',
            'description' => 'Chilled Coca-Cola Zero 330ml.',
            'price' => 600.00,
            'is_available' => true,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('restaurant_menu_items', [
            'id' => $item->id,
            'name' => 'Coke Zero',
            'price' => 600.00,
        ]);
    }

    public function test_update_menu_item_changes_price(): void
    {
        (new MenuItemSeeder)->run();

        $item = MenuItem::withoutGlobalScopes()->where('name', 'Water')->first();
        $originalPrice = $item->price;

        $this->actingAs($this->admin)->post(route('restaurant.admin.update-item', $item->id), [
            'restaurant_menu_categories_id' => $item->restaurant_menu_categories_id,
            'name' => 'Water',
            'price' => 500.00,
            'is_available' => true,
        ]);

        $item->refresh();
        $this->assertNotEquals($originalPrice, $item->price);
        $this->assertEquals(500.00, (float) $item->price);
    }

    // ─── Controller: Delete / Restore ────────────────────────────────

    public function test_delete_menu_item_soft_deletes(): void
    {
        (new MenuItemSeeder)->run();

        $item = MenuItem::withoutGlobalScopes()->where('name', 'Samosas')->first();

        $response = $this->actingAs($this->admin)->post(route('restaurant.admin.delete-item', $item->id));

        $response->assertRedirect();
        $this->assertSoftDeleted('restaurant_menu_items', ['id' => $item->id]);
    }

    public function test_restore_menu_item(): void
    {
        (new MenuItemSeeder)->run();

        $item = MenuItem::withoutGlobalScopes()->where('name', 'Fruit Salad')->first();
        $item->delete();
        $this->assertSoftDeleted('restaurant_menu_items', ['id' => $item->id]);

        $response = $this->actingAs($this->admin)->post(route('restaurant.admin.restore-item', $item->id));

        $response->assertRedirect();
        $this->assertDatabaseHas('restaurant_menu_items', ['id' => $item->id, 'deleted_at' => null]);
    }

    // ─── Controller: Auth ────────────────────────────────────────────

    public function test_unauthenticated_user_cannot_add_item(): void
    {
        $categoryId = MenuCategory::withoutGlobalScopes()->where('name', 'Beverages')->first()->id;

        $response = $this->postJson(route('restaurant.admin.add-item'), [
            'restaurant_menu_categories_id' => $categoryId,
            'name' => 'Intruder Juice',
            'price' => 100.00,
        ]);

        $response->assertStatus(401);
    }

    // ─── Model Tests ─────────────────────────────────────────────────

    public function test_menu_item_belongs_to_category(): void
    {
        (new MenuItemSeeder)->run();

        $item = MenuItem::withoutGlobalScopes()->where('name', 'Jollof Rice & Chicken')->first();
        $this->assertNotNull($item->category);
        $this->assertEquals('Main Courses', $item->category->name);
    }

    public function test_menu_category_has_many_items(): void
    {
        (new MenuItemSeeder)->run();

        $beverages = MenuCategory::withoutGlobalScopes()->where('name', 'Beverages')->first();
        $items = $beverages->menuItems()->withoutGlobalScopes()->get();

        $this->assertGreaterThanOrEqual(7, $items->count(), 'Beverages should have at least 7 items');
        foreach ($items as $item) {
            $this->assertEquals($beverages->id, $item->restaurant_menu_categories_id);
        }
    }

    public function test_menu_item_default_availability_on_create(): void
    {
        $item = MenuItem::withoutGlobalScopes()->create([
            'name' => 'Instant Noodles',
            'restaurant_menu_categories_id' => MenuCategory::withoutGlobalScopes()->first()->id,
            'price' => 800.00,
        ]);

        // Database column may be null; controller sets is_available via $request->boolean()
        $this->assertNull($item->is_available, 'Model create() does not auto-set is_available');
    }
}
