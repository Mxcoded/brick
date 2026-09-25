<?php

namespace Modules\Admin\Tests\Feature;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class AppearanceRenderTest extends TestCase
{
    use DatabaseTransactions;

    private function actingAsAdmin(): User
    {
        $role = Role::firstOrCreate(['name' => RoleEnum::ADMIN->value, 'guard_name' => 'web']);
        $perm = Permission::firstOrCreate(['name' => 'manage_settings', 'guard_name' => 'web']);
        $role->givePermissionTo($perm);
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $admin = User::factory()->create(['type' => 'staff', 'status' => 'active']);
        $admin->assignRole(RoleEnum::ADMIN->value);

        return $admin;
    }

    public function test_page_renders_without_uncompiled_blade_directives(): void
    {
        $this->actingAs($this->actingAsAdmin())
            ->get('/admin/appearance')
            ->assertOk()
            ->assertDontSee('@js(')
            ->assertSee('window.brickspointThemePreview', false)
            ->assertSee('window.brickspointSavedTheme', false);
    }

    public function test_discard_preview_handler_uses_global_saved_values(): void
    {
        $this->actingAs($this->actingAsAdmin())
            ->get('/admin/appearance')
            ->assertOk()
            ->assertSee("x-on:click=\"\$wire.set('data.theme', window.brickspointSavedTheme, false);", false)
            ->assertSee('document.documentElement.classList.toggle(\'dark\', window.brickspointSavedThemeIsDark);', false);
    }

    public function test_theme_card_swatches_are_rendered(): void
    {
        $this->actingAs($this->actingAsAdmin())
            ->get('/admin/appearance')
            ->assertOk()
            ->assertSee('theme-option-gold-legacy', false)
            ->assertSee('theme-option-platinum-noir', false)
            ->assertSee('theme-option-sapphire-regal', false);
    }
}
