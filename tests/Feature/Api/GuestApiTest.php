<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class GuestApiTest extends TestCase
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

    public function test_list_guests(): void
    {
        $response = $this->getJson('/api/v1/guests', $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_create_guest(): void
    {
        $response = $this->postJson('/api/v1/guests', [
            'full_name' => 'Api Test Guest',
            'email' => 'apitestguest@example.com',
            'contact_number' => '+2348012345678',
            'nationality' => 'Nigerian',
        ], $this->authHeaders());

        $response->assertCreated()
            ->assertJsonStructure([
                'message',
                'data' => ['id', 'full_name'],
            ]);
    }

    public function test_create_guest_requires_full_name(): void
    {
        $response = $this->postJson('/api/v1/guests', [
            'email' => 'incomplete@example.com',
        ], $this->authHeaders());

        $response->assertUnprocessable()
            ->assertJsonValidationErrors(['full_name']);
    }

    public function test_show_guest(): void
    {
        $guest = DB::table('guests')->first();

        if (! $guest) {
            $this->markTestSkipped('No guests available for testing.');
        }

        $response = $this->getJson("/api/v1/guests/{$guest->id}", $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'full_name']]);
    }

    public function test_search_guests(): void
    {
        $response = $this->getJson('/api/v1/guests?search=test', $this->authHeaders());

        $response->assertOk();
    }

    public function test_unauthenticated_guest_access_fails(): void
    {
        $response = $this->getJson('/api/v1/guests');

        $response->assertUnauthorized();
    }
}
