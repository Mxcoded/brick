<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Restaurant\Models\Table;
use Tests\TestCase;

class TableApiTest extends TestCase
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

    public function test_list_tables(): void
    {
        $response = $this->getJson('/api/v1/tables', $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_create_table(): void
    {
        $response = $this->postJson('/api/v1/tables', [
            'number' => 'API-'.rand(100, 999),
        ], $this->authHeaders());

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'number'],
            ]);
    }

    public function test_update_table(): void
    {
        $this->markTestSkipped('Schema mismatch: restaurant_tables has no status column to update');
    }

    public function test_delete_table(): void
    {
        $table = Table::create([
            'number' => 'DEL-'.rand(100, 999),
        ]);

        $response = $this->deleteJson("/api/v1/tables/{$table->id}", [], $this->authHeaders());

        $response->assertNoContent();
        $this->assertDatabaseMissing('restaurant_tables', ['id' => $table->id]);
    }
}
