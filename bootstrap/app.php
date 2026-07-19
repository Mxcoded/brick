<?php

use App\Http\Middleware\DetectWebsiteProperty;
use App\Http\Middleware\EnsureApiPropertyAccess;
use App\Http\Middleware\LogUserActivity;
use App\Http\Middleware\RedirectIfAuthenticated;
use App\Http\Middleware\SetPropertyContext;
use App\Http\Middleware\TrackUserActivity;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Session\Middleware\AuthenticateSession;
use Modules\Inventory\Http\Middleware\ProcurementRole;
use Modules\Restaurant\Http\Middleware\RedirectToWaiterLogin;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;

return Application::configure(basePath: __DIR__.'/../')
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register the permission middleware
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
            'website.property' => DetectWebsiteProperty::class,
            'waiter-auth' => RedirectToWaiterLogin::class,
            'guest' => RedirectIfAuthenticated::class,
            'auth.session' => AuthenticateSession::class,
            'procurement.role' => ProcurementRole::class,
            'api.property' => EnsureApiPropertyAccess::class,
        ]);

        // Track user activity for login session monitoring
        $middleware->appendToGroup('web', TrackUserActivity::class);

        // Log page views and write actions (throttled)
        $middleware->appendToGroup('web', LogUserActivity::class);

        // Set the current property context from session/query parameter
        $middleware->appendToGroup('web', SetPropertyContext::class);

        // Exclude Hikvision webhook from CSRF (machine-to-machine)
        $middleware->validateCsrfTokens(except: [
            'staff/attendance/hikvision-webhook',
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    // ** ADD THIS ENTIRE BLOCK TO YOUR FILE **
    ->withSchedule(function (Schedule $schedule) {
        // Run the overstay process every day at 1:00 PM (13:00).
        $schedule->command('hotel:process-overstays')->dailyAt('18:00');

        // Auto-mark no-show bookings daily at midnight
        $schedule->command('hotel:mark-no-shows')->dailyAt('00:00');

        // Send pre-arrival reminders at 9:00 AM daily
        $schedule->command('hotel:send-pre-arrival-reminders')->dailyAt('09:00');

        // Send review requests at 10:00 AM daily
        $schedule->command('hotel:send-review-requests')->dailyAt('10:00');

        // Run re-engagement campaign every Monday at 11:00 AM
        $schedule->command('hotel:re-engagement-campaign')->weeklyOn(1, '11:00');

        // Prune activity logs older than 7 days
        $schedule->command('activity-logs:prune')->dailyAt('03:00');

        // Auto log out sessions idle for more than 3 hours
        $schedule->command('auth:logout-idle')->everyThirtyMinutes();
    })
    // ** END OF ADDED BLOCK **
    ->create();
