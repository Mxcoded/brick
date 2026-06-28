<?php

namespace Modules\Frontdeskcrm\Tests;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

abstract class ModuleTestCase extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class);

        config(['services.numverify.key' => null, 'services.abstract_api.key' => null]);

        Factory::guessFactoryNamesUsing(function (string $modelName) {
            $basename = class_basename($modelName);

            if (str_starts_with($modelName, 'Modules\\Frontdeskcrm\\')) {
                return 'Modules\\Frontdeskcrm\\Database\\Factories\\'.$basename.'Factory';
            }

            return 'Database\\Factories\\'.$basename.'Factory';
        });
    }

    protected function createAuthenticatedUser(): User
    {
        $user = User::factory()->create();

        if (! Permission::where('name', 'access_frontdesk_dashboard')->exists()) {
            Permission::create(['name' => 'access_frontdesk_dashboard']);
        }

        $user->givePermissionTo('access_frontdesk_dashboard');
        $this->actingAs($user);

        return $user;
    }
}
