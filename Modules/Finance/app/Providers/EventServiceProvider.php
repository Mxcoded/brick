<?php

namespace Modules\Finance\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Finance\Listeners\CloseFolioOnCheckout;
use Modules\Finance\Listeners\GenerateDailyReportsOnAudit;
use Modules\Finance\Listeners\RecordDepositOnDepositReceived;
use Modules\Finance\Listeners\RecordRevenueOnOrderPaid;
use Modules\Finance\Listeners\UpdateArOnFolioCharge;
use Modules\Frontdeskcrm\Events\DepositReceived;
use Modules\Frontdeskcrm\Events\FolioChargePosted;
use Modules\Frontdeskcrm\Events\NightAuditCompleted;
use Modules\Frontdeskcrm\Events\RegistrationCheckedOut;
use Modules\Restaurant\Events\OrderPaid;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        RegistrationCheckedOut::class => [
            CloseFolioOnCheckout::class,
        ],
        DepositReceived::class => [
            RecordDepositOnDepositReceived::class,
        ],
        FolioChargePosted::class => [
            UpdateArOnFolioCharge::class,
        ],
        NightAuditCompleted::class => [
            GenerateDailyReportsOnAudit::class,
        ],
        OrderPaid::class => [
            RecordRevenueOnOrderPaid::class,
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
