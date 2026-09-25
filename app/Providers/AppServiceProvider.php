<?php

namespace App\Providers;

use App\Listeners\LogSuccessfulLogin;
use App\Listeners\LogSuccessfulLogout;
use App\Models\User;
use App\Support\ThemeSettings;
use App\View\Compilers\AtomicBladeCompiler;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Use atomic writes for Blade compiled views to prevent "unexpected end of file"
        // errors when concurrent requests trigger recompilation simultaneously.
        // Registered in register() (not boot()) so package providers (e.g. Livewire's
        // tag precompiler) register on this instance rather than a discarded one.
        // The original compiler is preserved so registrations made on it before the
        // swap survive — otherwise Laravel's built-in `dynamic-component` alias (and
        // any earlier package directives/aliases) would be silently dropped and
        // `x-dynamic-component` would fail to compile.
        $this->app->extend('blade.compiler', function ($bladeCompiler, $app) {
            $atomic = new AtomicBladeCompiler(
                $app['files'],
                $app['config']['view.compiled'],
                $app['config']->get('view.relative_hash', false) ? $app->basePath() : '',
                $app['config']->get('view.cache', true),
                $app['config']->get('view.compiled_extension', 'php'),
            );

            foreach ($bladeCompiler->getClassComponentAliases() as $alias => $class) {
                $atomic->component($class, $alias);
            }

            foreach ($bladeCompiler->getClassComponentNamespaces() as $prefix => $namespace) {
                $atomic->componentNamespace($namespace, $prefix);
            }

            foreach ($bladeCompiler->getAnonymousComponentPaths() as $componentPath) {
                $atomic->anonymousComponentPath(
                    $componentPath['path'],
                    $componentPath['prefix'],
                    $componentPath['types'],
                );
            }

            foreach ($bladeCompiler->getCustomDirectives() as $name => $handler) {
                $atomic->directive($name, $handler);
            }

            return $atomic;
        });
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
            $view->with('theme', ThemeSettings::theme())
                ->with('logoSetting', ThemeSettings::logo());
        });

        // Register login tracking listeners
        Event::listen(Login::class, LogSuccessfulLogin::class);
        Event::listen(Logout::class, LogSuccessfulLogout::class);

        // Staff access is determined by the account `type` ('staff'), not a Spatie role.
        // Grant staff-account-type users the permissions the legacy `staff` role carried
        // so module route middleware (@can) and views keep working without a role.
        Gate::before(function ($user, $ability) {
            if ($user instanceof User
                && $user->isStaff()
                && in_array($ability, [
                    'access_staff_dashboard',
                    'access_tasks_dashboard',
                    'tasks.create',
                    'tasks.read',
                    'tasks.delete',
                    'leaves.create',
                    'leaves.read',
                    'procurement.create_request',
                    'procurement.view_own_requests',
                ], true)
            ) {
                return true;
            }

            return null;
        });
    }
}
