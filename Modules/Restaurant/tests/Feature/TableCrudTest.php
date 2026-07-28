<?php

namespace Modules\Restaurant\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Restaurant\Models\Table;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class TableCrudTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(ValidateCsrfToken::class);

        $role = Role::firstOrCreate(['name' => RoleEnum::WAITER->value, 'guard_name' => 'web']);
        $permission = Permission::firstOrCreate(['name' => 'access_restaurant_dashboard', 'guard_name' => 'web']);
        if (! $role->hasPermissionTo($permission)) {
            $role->givePermissionTo($permission);
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->admin = User::factory()->create([
            'type' => 'staff',
            'status' => 'active',
        ]);
        $this->admin->assignRole(RoleEnum::WAITER->value);
    }

    public function test_admin_can_view_tables_page(): void
    {
        $response = $this->actingAs($this->admin)->get('/restaurant-admin/tables');
        $response->assertOk();
    }

    public function test_admin_can_add_table(): void
    {
        $response = $this->actingAs($this->admin)->post('/restaurant-admin/tables/store', [
            'number' => 'TEST-UNIQUE-1',
            'capacity' => 6,
            'section' => 'Center',
        ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();
        $table = Table::where('number', 'TEST-UNIQUE-1')->first();
        $this->assertNotNull($table);
        $this->assertEquals(6, $table->capacity);
        $this->assertEquals('Center', $table->section);
    }

    public function test_admin_can_update_table(): void
    {
        $table = Table::create(['number' => 'TEST-UPDATE-ORIG', 'capacity' => 2, 'section' => 'Patio']);

        $response = $this->actingAs($this->admin)->post("/restaurant-admin/tables/{$table->id}/update", [
            'number' => 'TEST-UPDATE-NEW',
            'capacity' => 4,
            'section' => 'VIP',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('restaurant_tables', [
            'id' => $table->id,
            'number' => 'TEST-UPDATE-NEW',
            'capacity' => 4,
            'section' => 'VIP',
        ]);
    }

    public function test_admin_can_delete_table(): void
    {
        $table = Table::create(['number' => 'TEST-DELETE-ME']);

        $response = $this->actingAs($this->admin)->post("/restaurant-admin/tables/{$table->id}/delete");

        $response->assertRedirect();
        $this->assertDatabaseMissing('restaurant_tables', ['id' => $table->id]);
    }

    public function test_duplicate_table_number_rejected(): void
    {
        Table::create(['number' => 'TEST-DUPE-CHECK']);

        $response = $this->actingAs($this->admin)->post('/restaurant-admin/tables/store', [
            'number' => 'TEST-DUPE-CHECK',
        ]);

        $response->assertSessionHasErrors('number');
    }
}
