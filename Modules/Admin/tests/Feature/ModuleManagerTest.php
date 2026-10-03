<?php

namespace Modules\Admin\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Artisan;
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

    /**
     * Raw copy of modules_statuses.json taken before the test body runs.
     *
     * Toggling a module writes this file directly. If the toggle (or the
     * provider reload) throws partway through, the module is left disabled
     * on disk, which poisons every later test in the same run (routes
     * disappear, Filament form helpers no-op). Restoring the raw bytes in
     * tearDown guarantees the file is put back even when the test errors.
     */
    private string $modulesStatusesSnapshot = '';

    protected function setUp(): void
    {
        parent::setUp();

        $this->modulesStatusesSnapshot = (string) file_get_contents(base_path('modules_statuses.json'));

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $role = Role::firstOrCreate(['name' => RoleEnum::ADMIN->value, 'guard_name' => 'web']);
        $perms = [
            'access_admin_dashboard',
            'modules.manage',
        ];
        foreach ($perms as $permName) {
            $perm = Permission::firstOrCreate(['name' => $permName, 'guard_name' => 'web']);
            if (! $role->hasPermissionTo($perm)) {
                $role->givePermissionTo($perm);
            }
        }
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $this->admin = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $this->admin->assignRole(RoleEnum::ADMIN->value);
        $this->actingAs($this->admin);
    }

    protected function tearDown(): void
    {
        $this->restoreModuleStatuses();

        parent::tearDown();
    }

    /**
     * Put modules_statuses.json back byte for byte, retrying briefly because
     * Windows can briefly deny the write when another process holds the file.
     */
    private function restoreModuleStatuses(): void
    {
        if ($this->modulesStatusesSnapshot === '') {
            return;
        }

        $path = base_path('modules_statuses.json');

        for ($attempt = 0; $attempt < 5; $attempt++) {
            if (@file_put_contents($path, $this->modulesStatusesSnapshot) !== false) {
                break;
            }

            usleep(100_000);
        }

        try {
            Artisan::call('optimize:clear');
        } catch (\Throwable) {
            // A failed cache clear must not mask the underlying test result.
        }
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
            // Restore the raw file instead of calling enable()/disable(): the
            // provider reload those methods trigger is what throws when the
            // module has just been switched off.
            $this->restoreModuleStatuses();
        }
    }

    public function test_module_manager_requires_authentication()
    {
        auth()->logout();

        $this->get('/admin/modules')
            ->assertRedirect('/admin/login');
    }

    public function test_module_manager_requires_modules_manage_permission()
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

        $user->givePermissionTo('modules.manage');
        $this->get('/admin/modules')->assertOk();
    }
}
