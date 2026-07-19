<?php

namespace Tests\Feature\Api;

use App\Models\RoomUnit;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RoomUnitTest extends TestCase
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

    public function test_list_room_units(): void
    {
        $response = $this->getJson('/api/v1/room-units', $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_update_room_unit_status(): void
    {
        $unit = RoomUnit::first();

        if (! $unit) {
            $this->markTestSkipped('No room units available for testing.');
        }

        $response = $this->postJson("/api/v1/room-units/{$unit->id}/status", [
            'status' => 'dirty',
        ], $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure([
                'message',
                'data' => ['status'],
            ]);

        $this->assertEquals('dirty', $response->json('data.status'));
    }
}
