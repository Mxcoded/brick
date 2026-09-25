<?php

namespace Modules\Admin\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Modules\Admin\Filament\Pages\Appearance;
use Modules\Website\Models\Settings;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AppearanceTest extends TestCase
{
    use DatabaseTransactions;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutMiddleware([ValidateCsrfToken::class]);

        $role = Role::firstOrCreate(['name' => RoleEnum::ADMIN->value, 'guard_name' => 'web']);
        $perm = Permission::firstOrCreate(['name' => 'access_admin_dashboard', 'guard_name' => 'web']);
        $settings = Permission::firstOrCreate(['name' => 'manage_settings', 'guard_name' => 'web']);
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

    public function test_appearance_page_loads_with_theme_form()
    {
        Livewire::test(Appearance::class)
            ->assertOk()
            ->assertSee('Appearance')
            ->assertSee('Colour theme')
            ->assertSee('Gold Legacy');
    }

    public function test_save_persists_selected_theme()
    {
        Livewire::test(Appearance::class)
            ->fillForm(['theme' => 'sapphire-regal'])
            ->call('save')
            ->assertHasNoErrors();

        $this->assertSame('sapphire-regal', Settings::where('key', 'theme')->value('value'));
        $this->assertFalse(cache()->has('app.theme'));
    }

    public function test_remove_logo_deletes_setting_and_file()
    {
        Storage::disk('public')->put('settings/test-logo.png', 'fake');
        Settings::create(['key' => 'logo', 'value' => 'settings/test-logo.png', 'type' => 'image']);

        Livewire::test(Appearance::class)
            ->call('removeLogo')
            ->assertHasNoErrors();

        $this->assertNull(Settings::where('key', 'logo')->value('value'));
        $this->assertFalse(Storage::disk('public')->exists('settings/test-logo.png'));
        $this->assertFalse(cache()->has('app.logo'));
    }

    public function test_appearance_requires_authentication()
    {
        auth()->logout();

        $this->get('/admin/appearance')
            ->assertRedirect('/admin/login');
    }

    public function test_appearance_requires_manage_settings_permission()
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

        $this->get('/admin/appearance')->assertForbidden();
    }
}
