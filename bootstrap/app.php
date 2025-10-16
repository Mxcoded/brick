<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Illuminate\Console\Scheduling\Schedule;

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
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })
    // ** ADD THIS ENTIRE BLOCK TO YOUR FILE **
    ->withSchedule(function (Schedule $schedule) {
        // Run the overstay process every day at 1:00 PM (13:00).
        $schedule->command('hotel:process-overstays')->dailyAt('13:00');
    })
    // ** END OF ADDED BLOCK **
    ->create();
