<?php

namespace App\Providers;

use App\View\Compilers\AtomicBladeCompiler;
use App\Listeners\LogSuccessfulLogin;
use App\Listeners\LogSuccessfulLogout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Modules\Website\Models\Settings;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Schema::defaultStringLength(191);

        Paginator::useBootstrapFive();

        // Share active theme and logo with base layout
        View::composer('layouts.base', function ($view) {
            $theme = cache()->remember('app.theme', 3600, function () {
                return Settings::where('key', 'theme')->value('value') ?? 'gold-legacy';
            });
            $logo = cache()->remember('app.logo', 3600, function () {
                return Settings::where('key', 'logo')->value('value');
            });
            $view->with('theme', $theme)->with('logoSetting', $logo);
        });

        // Register login tracking listeners
        Event::listen(Login::class, LogSuccessfulLogin::class);
        Event::listen(Logout::class, LogSuccessfulLogout::class);

        // Use atomic writes for Blade compiled views to prevent "unexpected end of file"
        // errors when concurrent requests trigger recompilation simultaneously.
        $this->app->extend('blade.compiler', function ($bladeCompiler, $app) {
            return new AtomicBladeCompiler(
                $app['files'],
                $app['config']['view.compiled'],
                $app['config']->get('view.relative_hash', false) ? $app->basePath() : '',
                $app['config']->get('view.cache', true),
                $app['config']->get('view.compiled_extension', 'php'),
            );
        });
    }
}
