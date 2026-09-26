<?php

namespace Modules\Admin\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use Modules\Admin\Filament\Pages\ModuleManager;
use Nwidart\Modules\Facades\Module;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class ModuleManagerTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $role = Role::firstOrCreate(['name' => RoleEnum::ADMIN->value, 'guard_name' => 'web']);
        $perm = Permission::firstOrCreate(['name' => 'access_admin_dashboard', 'guard_name' => 'web']);
        $settings = Permission::firstOrCreate(['name' => 'settings.update', 'guard_name' => 'web']);
        if (! $role->hasPermissionTo($perm)) {
            $role->givePermissionTo($perm);
        }
        if (! $role->hasPermissionTo($settings)) {
            $role->givePermissionTo($settings);
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->admin = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->admin->assignRole(RoleEnum::ADMIN->value);
        $this->actingAs($this->admin);
    }

    public function test_module_manager_page_lists_modules_with_status()
    {
        Livewire::test(ModuleManager::class)
            ->assertOk()
            ->assertSee('Module Manager')
            ->assertSee('Modules Installed')
            ->assertSee('Restaurant');
    }

    public function test_admin_module_shows_required_and_cannot_be_disabled()
    {
        Livewire::test(ModuleManager::class)
            ->assertOk()
            ->assertSee('Required');

        Livewire::test(ModuleManager::class)
            ->call('toggleModule', 'Admin')
            ->assertHasNoErrors();

        $this->assertTrue(Module::find('Admin')->isEnabled());
    }

    public function test_toggle_module_disables_and_enables()
    {
        $module = Module::find('Website');
        $wasEnabled = $module->isEnabled();

        try {
            Livewire::test(ModuleManager::class)
                ->call('toggleModule', 'Website')
                ->assertHasNoErrors();

            $this->assertNotSame($wasEnabled, Module::find('Website')->isEnabled());

            Livewire::test(ModuleManager::class)
                ->call('toggleModule', 'Website')
                ->assertHasNoErrors();

            $this->assertSame($wasEnabled, Module::find('Website')->isEnabled());
        } finally {
            $module = Module::find('Website');
            if ($wasEnabled) {
                $module->enable();
            } else {
                $module->disable();
            }
        }
    }

    public function test_module_manager_requires_authentication()
    {
        auth()->logout();

        $this->get('/admin/modules')
            ->assertRedirect('/admin/login');
    }

    public function test_module_manager_requires_settings_update_permission()
    {
        $role = Role::firstOrCreate(['name' => 'staff', 'guard_name' => 'web']);
        $perm = Permission::firstOrCreate(['name' => 'access_admin_dashboard', 'guard_name' => 'web']);
        if (! $role->hasPermissionTo($perm)) {
            $role->givePermissionTo($perm);
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $user = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $user->assignRole('staff');
        $this->actingAs($user);

        $this->get('/admin/modules')->assertForbidden();
    }
}
