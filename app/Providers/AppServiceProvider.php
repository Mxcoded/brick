<?php

namespace App\Providers;

use App\View\Compilers\AtomicBladeCompiler;
use App\Listeners\LogSuccessfulLogin;
use App\Listeners\LogSuccessfulLogout;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

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

        // Login rate limiter: 5 attempts per minute per email+IP combination.
        // Lockout increases progressively with each batch of failures (max 15 min).
        RateLimiter::for('login', function ($request) {
            $email = (string) $request->input('email');
            $key = 'login:' . $email . '|' . $request->ip();

            $attempts = RateLimiter::attempts($key);
            $decayMinutes = min(1 + (intdiv($attempts, 5) * 2), 15);

            return Limit::perMinute(5)->by($key)->decayMinutes($decayMinutes);
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
