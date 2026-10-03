<?php

namespace App\Providers;

use App\Listeners\LogSuccessfulLogin;
use App\Listeners\LogSuccessfulLogout;
use App\Models\User;
use App\Support\PermissionAliases;
use App\Support\ThemeSettings;
use App\View\Compilers\AtomicBladeCompiler;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\RateLimiter;
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

        $this->registerRateLimiters();

        // Staff access is determined by the account `type` ('staff'), not a Spatie role.
        // Grant staff-account-type users the permissions the legacy `staff` role carried
        // so module route middleware (@can) and views keep working without a role.
        Gate::before(function ($user, $ability) {
            // Tasks is everybody's working tool: anyone on staff can open it,
            // raise work and keep it current. Delegation is the exception and is
            // granted per role through TaskPermission::DELEGATOR_ROLES, so
            // tasks.assign is deliberately absent from this blanket.
            //
            // This is checked before the alias resolution below, not after. The
            // Tasks permissions are aliased to the legacy view_tasks and
            // assign_tasks names, and that branch answers from a direct
            // permission lookup, so it would return false and stop the Gate
            // before a role-less staff account ever reached the blanket.
            if ($user instanceof User
                && is_string($ability)
                && $user->isStaff()
                && in_array($ability, [
                    'access_staff_dashboard',
                    'access_tasks_dashboard',
                    'tasks.create',
                    'tasks.read',
                    'tasks.update',
                    'tasks.delete',
                    'leaves.create',
                    'leaves.read',
                    'procurement.create_request',
                    'procurement.view_own_requests',
                ], true)
            ) {
                return true;
            }

            // Resolve legacy <-> granular permission synonyms first so either
            // spelling grants the same access; defer (null) when not aliased.
            if ($user instanceof User && is_string($ability)) {
                $synonyms = PermissionAliases::resolve($ability);
                if ($synonyms !== null) {
                    return $user->hasAnyPermission($synonyms);
                }
            }

            return null;
        });
    }

    /**
     * Named rate limiters for the public ordering and waiter terminal routes.
     *
     * Guest routes are keyed on IP and staff routes on the authenticated user
     * id, because those are the two identifiers that are stable across a
     * client's requests. A session id was tried first and rejected: it is
     * rewritten during request handling, so the counter never accumulated and
     * the limiter silently did nothing.
     *
     * The consequence of keying guests on IP is that the ceiling is shared
     * across everyone behind one address, which for a hotel means every guest
     * on the same connection. The guest ceilings are therefore set well above
     * what a busy property needs, with the real protection against a runaway
     * order coming from the per-line quantity cap and the subtotal clamp rather
     * than from the request rate.
     */
    private function registerRateLimiters(): void
    {
        RateLimiter::for('menu-cart', fn (Request $request) => Limit::perMinute(240)
            ->by('guest-cart|'.$request->ip()));

        // Placing an order creates a real kitchen ticket and consumes stock, so
        // this is far tighter than moving the cart around. The ceiling still has
        // to clear a property-wide rush on a shared address.
        RateLimiter::for('menu-order-submit', fn (Request $request) => Limit::perMinute(60)
            ->by('guest-submit|'.$request->ip()));

        // Customer-support lookup that returns real orders for a phone number.
        // Tight on purpose: this is the endpoint a scraper would hit.
        RateLimiter::for('menu-order-lookup', fn (Request $request) => Limit::perMinute(5)
            ->by('guest-lookup|'.$request->ip()));

        // Waiter terminal. Keyed on the user, so one busy server cannot exhaust
        // the allowance of another, and so a shared counter cannot be used to
        // hide a single operator's activity.
        RateLimiter::for('pos-cart', fn (Request $request) => Limit::perMinute(180)
            ->by($this->staffRateKey($request)));

        RateLimiter::for('pos-order-submit', fn (Request $request) => Limit::perMinute(60)
            ->by($this->staffRateKey($request)));

        // Money movement: cash on the terminal, card on a gateway, or a refund
        // reversing a ledger entry. A double-tap must not become two payments.
        RateLimiter::for('pos-payment', fn (Request $request) => Limit::perMinute(30)
            ->by($this->staffRateKey($request)));
    }

    private function staffRateKey(Request $request): string
    {
        return 'staff|'.($request->user()?->getAuthIdentifier() ?? $request->ip());
    }
}
