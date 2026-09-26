<?php

namespace Modules\Admin\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class FilamentAdminPanelTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->admin = User::firstOrCreate(
            ['email' => 'filament-test@brickspoint.test'],
            [
                'name' => 'Filament Test',
                'password' => bcrypt('password'),
                'type' => 'staff',
                'status' => 'active',
            ],
        );

        $this->admin->assignRole(RoleEnum::ADMIN->value);
    }

    public function test_login_page_renders(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_dashboard_requires_auth(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }

    public function test_admin_can_access_dashboard(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin')
            ->assertOk();
    }

    public function test_admin_can_access_users_resource(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/users')
            ->assertOk();
    }

    public function test_admin_can_access_roles_resource(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/roles')
            ->assertOk();
    }

    public function test_admin_can_access_permissions_resource(): void
    {
        $this->actingAs($this->admin)
            ->get('/admin/permissions')
            ->assertOk();
    }
}
