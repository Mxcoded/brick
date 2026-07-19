<?php

namespace Modules\Staff\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Frontdeskcrm\Events\NightAuditCompleted;
use Modules\Restaurant\Events\OrderPaid;
use Modules\Staff\Listeners\CalculateCommissionOnOrderPaid;
use Modules\Staff\Listeners\UpdateAttendanceOnAudit;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        OrderPaid::class => [
            CalculateCommissionOnOrderPaid::class,
        ],
        NightAuditCompleted::class => [
            UpdateAttendanceOnAudit::class,
        ],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = false;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void
    {
        //
    }
}
