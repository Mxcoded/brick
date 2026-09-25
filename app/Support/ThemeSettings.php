<?php

namespace App\Support;

use Filament\Support\Colors\Color;
use Modules\Website\Models\Settings;

class ThemeSettings
{
    public const THEMES = [
        'gold-legacy' => [
            'label' => 'Gold Legacy',
            'accent' => '#C8A165',
            'dark' => false,
        ],
        'platinum-noir' => [
            'label' => 'Platinum Noir',
            'accent' => '#E8E8E8',
            'dark' => true,
        ],
        'sapphire-regal' => [
            'label' => 'Sapphire Regal',
            'accent' => '#1B3A5C',
            'dark' => false,
        ],
    ];

    public static function theme(): string
    {
        return cache()->remember('app.theme', 3600, function (): string {
            return Settings::where('key', 'theme')->value('value') ?? 'gold-legacy';
        });
    }

    public static function logo(): ?string
    {
        $logo = cache()->remember('app.logo', 3600, function (): ?string {
            return Settings::where('key', 'logo')->value('value');
        });

        return filled($logo) ? $logo : null;
    }

    public static function isDark(?string $theme = null): bool
    {
        return (bool) (static::THEMES[static::resolveTheme($theme)]['dark'] ?? false);
    }

    public static function accent(?string $theme = null): string
    {
        return static::THEMES[static::resolveTheme($theme)]['accent'] ?? static::THEMES['gold-legacy']['accent'];
    }

    public static function primaryPalette(?string $theme = null): array
    {
        return Color::generatePalette(static::accent($theme));
    }

    protected static function resolveTheme(?string $theme): string
    {
        $theme ??= static::theme();

        return isset(static::THEMES[$theme]) ? $theme : 'gold-legacy';
    }
}
