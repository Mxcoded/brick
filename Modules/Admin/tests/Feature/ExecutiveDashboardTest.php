<?php

namespace Modules\Admin\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Modules\Admin\Filament\Pages\Dashboard;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Tests\TestCase;

class ExecutiveDashboardTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $role = Role::firstOrCreate(['name' => RoleEnum::ADMIN->value, 'guard_name' => 'web']);
        $perm = Permission::firstOrCreate(['name' => 'access_admin_dashboard', 'guard_name' => 'web']);
        if (! $role->hasPermissionTo($perm)) {
            $role->givePermissionTo($perm);
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->admin = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->admin->assignRole(RoleEnum::ADMIN->value);
        $this->actingAs($this->admin);
    }

    public function test_executive_dashboard_loads_for_admin()
    {
        Livewire::test(Dashboard::class)
            ->assertOk()
            ->assertSee('Executive Dashboard')
            ->assertSee('Total Revenue')
            ->assertSee('YTD Revenue')
            ->assertSee('Revenue Trend')
            ->assertSee('Upcoming Banquet Events');
    }

    public function test_dashboard_lives_under_its_own_slug()
    {
        $this->assertSame(
            url('/admin/executive-dashboard'),
            route('filament.admin.pages.dashboard'),
        );
    }

    public function test_panel_root_is_the_neutral_home_page()
    {
        $this->assertSame(url('/admin'), route('filament.admin.pages..'));
    }

    public function test_legacy_admin_dashboard_route_has_been_removed()
    {
        $this->expectException(RouteNotFoundException::class);

        route('admin.dashboard');
    }

    public function test_home_redirects_admin_to_filament_dashboard()
    {
        $this->get('/home')
            ->assertRedirect(route('filament.admin.pages.dashboard'));
    }

    public function test_dashboard_requires_authentication()
    {
        auth()->logout();

        $this->get('/admin')
            ->assertRedirect('/admin/login');
    }
}
