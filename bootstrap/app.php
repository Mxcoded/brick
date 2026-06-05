<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Illuminate\Console\Scheduling\Schedule;
use App\Http\Middleware\TrackUserActivity;
use App\Http\Middleware\LogUserActivity;

return Application::configure(basePath: __DIR__ . '/../')
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // Register the permission middleware
        $middleware->alias([
            'role' => RoleMiddleware::class,
            'permission' => PermissionMiddleware::class,
        ]);

        // Track user activity for login session monitoring
        $middleware->appendToGroup('web', TrackUserActivity::class);

        // Log page views and write actions (throttled)
        $middleware->appendToGroup('web', LogUserActivity::class);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    // ** ADD THIS ENTIRE BLOCK TO YOUR FILE **
    ->withSchedule(function (Schedule $schedule) {
        // Run the overstay process every day at 1:00 PM (13:00).
        $schedule->command('hotel:process-overstays')->dailyAt('18:00');

        // Prune activity logs older than 90 days
        $schedule->call(function () {
            \App\Models\UserActivityLog::where('created_at', '<', now()->subDays(90))->delete();
        })->dailyAt('03:00');
    })
    // ** END OF ADDED BLOCK **
    ->create();
