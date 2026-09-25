<?php

namespace Modules\Admin\Tests\Feature;

use App\Support\ThemeSettings;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Modules\Website\Models\Settings;
use Tests\TestCase;

class ThemeAcrossFilamentTest extends TestCase
{
    use DatabaseTransactions;

    private function setTheme(string $theme): void
    {
        Settings::updateOrCreate(
            ['key' => 'theme'],
            ['value' => $theme, 'type' => 'string'],
        );
        cache()->forget('app.theme');
    }

    public function test_login_page_injects_default_theme()
    {
        $this->setTheme('gold-legacy');

        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('--primary-500:', false)
            ->assertSee("const theme = 'gold-legacy';", false);
    }

    public function test_saving_appearance_updates_filament_theme()
    {
        $this->setTheme('sapphire-regal');

        $this->get('/admin/login')
            ->assertOk()
            ->assertSee("const theme = 'sapphire-regal';", false);
    }

    public function test_platinum_noir_forces_dark_mode()
    {
        $this->setTheme('platinum-noir');

        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('platinum-noir\u0022]', false);
    }

    public function test_brand_logo_renders_when_uploaded()
    {
        Settings::updateOrCreate(
            ['key' => 'logo'],
            ['value' => 'settings/logo.png', 'type' => 'image'],
        );
        cache()->forget('app.logo');

        $this->get('/admin/login')
            ->assertOk()
            ->assertSee('storage/settings/logo.png', false);
    }

    public function test_legacy_base_layout_uses_active_theme()
    {
        $this->setTheme('sapphire-regal');

        $html = view('layouts.base', ['title' => 'Test'])->render();

        $this->assertStringContainsString('data-theme="sapphire-regal"', $html);
        $this->assertSame('sapphire-regal', ThemeSettings::theme());
    }
}
