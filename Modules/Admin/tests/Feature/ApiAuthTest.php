<?php

namespace Modules\Admin\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ApiAuthTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        Role::firstOrCreate(['name' => RoleEnum::ADMIN->value, 'guard_name' => 'web']);
    }

    public function test_api_rejects_requests_without_token(): void
    {
        $response = $this->getJson('/api/v1/admin');

        $response->assertStatus(401);
    }

    public function test_api_rejects_invalid_token(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer not-a-real-token')
            ->getJson('/api/v1/admin');

        $response->assertStatus(401);
    }

    public function test_api_accepts_valid_sanctum_token(): void
    {
        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $user->assignRole(RoleEnum::ADMIN->value);

        $token = $user->createToken('test-device')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer '.$token)
            ->getJson('/api/v1/admin');

        // A non-401 status proves the sanctum guard accepted the bearer token.
        $this->assertNotEquals(401, $response->getStatusCode());
    }
}
