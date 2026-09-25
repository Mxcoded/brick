<?php

namespace Modules\Admin\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use App\Support\ThemeSettings;
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
        $this->assertSame('sapphire-regal', cache()->get('app.theme'));
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

    public function test_theme_selector_renders_all_theme_cards_with_live_preview()
    {
        Livewire::test(Appearance::class)
            ->assertSee('Gold Legacy')
            ->assertSee('Platinum Noir')
            ->assertSee('Sapphire Regal')
            ->assertSee('Live preview')
            ->assertSee('theme-option-gold-legacy')
            ->assertSee('theme-option-platinum-noir')
            ->assertSee('theme-option-sapphire-regal');
    }

    public function test_unsaved_theme_change_banner_shown_when_theme_changed()
    {
        $saved = ThemeSettings::theme();
        $other = $saved === 'sapphire-regal' ? 'platinum-noir' : 'sapphire-regal';

        Livewire::test(Appearance::class)
            ->fillForm(['theme' => $other])
            ->assertSee('Unsaved theme change');
    }

    public function test_save_rejects_invalid_theme()
    {
        $before = Settings::where('key', 'theme')->value('value');

        Livewire::test(Appearance::class)
            ->fillForm(['theme' => 'not-a-theme'])
            ->call('save')
            ->assertHasErrors();

        $this->assertSame($before, Settings::where('key', 'theme')->value('value'));
    }

    public function test_discard_preview_button_restores_saved_theme()
    {
        Settings::updateOrCreate(
            ['key' => 'theme'],
            ['value' => 'sapphire-regal', 'type' => 'string']
        );
        cache()->forget('app.theme');

        $saved = ThemeSettings::theme();
        $other = $saved === 'platinum-noir' ? 'gold-legacy' : 'platinum-noir';

        Livewire::test(Appearance::class)
            ->fillForm(['theme' => $other])
            ->assertSee('Unsaved theme change')
            ->fillForm(['theme' => $saved])
            ->assertDontSee('Unsaved theme change');
    }
}
