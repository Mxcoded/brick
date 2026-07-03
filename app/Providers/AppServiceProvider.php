<?php

namespace App\Providers;

use Illuminate\Support\Facades\Gate;
use App\Enums\RoleEnum;
use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;

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
        // Implicitly grant "Super Admin" role all permissions
        Gate::before(function ($user, $ability) {
            return $user->hasRole(RoleEnum::SUPER_ADMIN->value) ? true : null;
        });
     Paginator::useBootstrapFive();
    }
}
