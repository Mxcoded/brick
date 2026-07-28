<?php

namespace Modules\Restaurant\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Restaurant\Events\OrderPaid;
use Modules\Restaurant\Events\OrderRefunded;
use Modules\Restaurant\Events\PaymentRecorded;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        OrderPaid::class => [],
        OrderRefunded::class => [],
        PaymentRecorded::class => [],
    ];

    /**
     * Indicates if events should be discovered.
     *
     * @var bool
     */
    protected static $shouldDiscoverEvents = true;

    /**
     * Configure the proper event listeners for email verification.
     */
    protected function configureEmailVerification(): void
    {
        //
    }
}
