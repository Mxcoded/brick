<?php

namespace Tests\Feature\Api;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class RoomApiTest extends TestCase
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

    public function test_list_rooms(): void
    {
        $response = $this->getJson('/api/v1/rooms', $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);
    }

    public function test_show_room(): void
    {
        $room = Room::first();

        if (! $room) {
            $this->markTestSkipped('No rooms available for testing.');
        }

        $response = $this->getJson("/api/v1/rooms/{$room->id}", $this->authHeaders());

        $response->assertOk()
            ->assertJsonStructure(['data' => ['id', 'name']]);
    }
}
