<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MenuItemApiTest extends TestCase
{
    use DatabaseTransactions;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create([
            'password' => 'password',
        ]);
    }

    private function authHeaders(): array
    {
        $token = $this->user->createToken('test-token', ['read', 'write'])->plainTextToken;

        return [
            'Authorization' => 'Bearer '.$token,
        ];
    }

    public function test_list_menu_items(): void
    {
        $response = $this->getJson('/api/v1/menu-items', $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_list_available_menu_items(): void
    {
        $response = $this->getJson('/api/v1/menu-items?available_only=1', $this->authHeaders());

        $response->assertOk();
    }

    public function test_show_menu_item(): void
    {
        $item = DB::table('restaurant_menu_items')->first();

        if (! $item) {
            $this->markTestSkipped('No menu items available for testing.');
        }

        $response = $this->getJson("/api/v1/menu-items/{$item->id}", $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name', 'price']]);
    }

    public function test_create_menu_item(): void
    {
        $categoryId = DB::table('restaurant_menu_categories')
            ->value('id');

        if (! $categoryId) {
            $this->markTestSkipped('No categories available for testing.');
        }

        $response = $this->postJson('/api/v1/menu-items', [
            'name' => 'Api Test Dish',
            'price' => 3500,
            'restaurant_menu_categories_id' => $categoryId,
            'description' => 'Test dish created via API',
        ], $this->authHeaders());

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'name', 'price'],
            ]);
    }

    public function test_unauthenticated_menu_item_access_fails(): void
    {
        $response = $this->getJson('/api/v1/menu-items');

        $response->assertUnauthorized();
    }
}
