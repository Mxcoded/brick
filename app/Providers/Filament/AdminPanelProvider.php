<?php

namespace App\Providers\Filament;

use App\Support\ThemeSettings;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->default()
            ->id('admin')
            ->path('admin')
            ->login()
            ->authGuard('web')
            ->colors([
                'primary' => ThemeSettings::accent(),
            ])
            ->brandLogo(fn (): ?string => filled(ThemeSettings::logo())
                ? Storage::disk('public')->url(ThemeSettings::logo())
                : null)
            ->discoverResources(
                in: module_path('Admin', 'app/Filament/Resources'),
                for: 'Modules\\Admin\\Filament\\Resources',
            )
            ->discoverResources(
                in: module_path('Frontdeskcrm', 'app/Filament/Resources'),
                for: 'Modules\\Frontdeskcrm\\Filament\\Resources',
            )
            ->discoverResources(
                in: module_path('Restaurant', 'app/Filament/Resources'),
                for: 'Modules\\Restaurant\\Filament\\Resources',
            )
            ->discoverResources(
                in: module_path('Finance', 'app/Filament/Resources'),
                for: 'Modules\\Finance\\Filament\\Resources',
            )
            ->discoverResources(
                in: module_path('Tasks', 'app/Filament/Resources'),
                for: 'Modules\\Tasks\\Filament\\Resources',
            )
            ->discoverPages(
                in: module_path('Admin', 'app/Filament/Pages'),
                for: 'Modules\\Admin\\Filament\\Pages',
            )
            ->discoverWidgets(
                in: module_path('Admin', 'app/Filament/Widgets'),
                for: 'Modules\\Admin\\Filament\\Widgets',
            )
            ->widgets([
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }

    public function boot(): void
    {
        FilamentView::registerRenderHook(
            PanelsRenderHook::HEAD_END,
            fn (): string => view('admin::filament.theme')->render(),
        );

        FilamentView::registerRenderHook(
            PanelsRenderHook::STYLES_AFTER,
            fn (): string => <<<'HTML'
                <script src="https://cdn.tailwindcss.com"></script>
                <script>
                    tailwind.config = {
                        darkMode: 'class',
                        corePlugins: { preflight: false },
                        theme: {
                            extend: {
                                colors: {
                                    primary: {
                                        50: 'oklch(var(--primary-50) / <alpha-value>)',
                                        100: 'oklch(var(--primary-100) / <alpha-value>)',
                                        200: 'oklch(var(--primary-200) / <alpha-value>)',
                                        300: 'oklch(var(--primary-300) / <alpha-value>)',
                                        400: 'oklch(var(--primary-400) / <alpha-value>)',
                                        500: 'oklch(var(--primary-500) / <alpha-value>)',
                                        600: 'oklch(var(--primary-600) / <alpha-value>)',
                                        700: 'oklch(var(--primary-700) / <alpha-value>)',
                                        800: 'oklch(var(--primary-800) / <alpha-value>)',
                                        900: 'oklch(var(--primary-900) / <alpha-value>)',
                                        950: 'oklch(var(--primary-950) / <alpha-value>)',
                                    },
                                },
                                fontFamily: {
                                    sans: ['Figtree', 'ui-sans-serif', 'system-ui'],
                                },
                            },
                        },
                    };
                </script>
                HTML,
        );
    }
}
