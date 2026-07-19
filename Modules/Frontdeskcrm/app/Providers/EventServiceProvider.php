<?php

namespace Modules\Frontdeskcrm\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Finance\Events\PaymentReceived;
use Modules\Frontdeskcrm\Events\DepositReceived;
use Modules\Frontdeskcrm\Events\FolioChargePosted;
use Modules\Frontdeskcrm\Events\NightAuditCompleted;
use Modules\Frontdeskcrm\Events\RegistrationCheckedIn;
use Modules\Frontdeskcrm\Events\RegistrationCheckedOut;
use Modules\Frontdeskcrm\Listeners\AddFolioChargeOnRoomService;
use Modules\Frontdeskcrm\Listeners\UpdateBalanceOnPayment;
use Modules\Frontdeskcrm\Listeners\UpdateRoomServiceMenuOnMenuItemUpdate;
use Modules\Restaurant\Events\MenuItemUpdated;
use Modules\Restaurant\Events\RoomServiceOrdered;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        RegistrationCheckedIn::class => [],
        RegistrationCheckedOut::class => [],
        DepositReceived::class => [],
        FolioChargePosted::class => [],
        NightAuditCompleted::class => [],
        PaymentReceived::class => [
            UpdateBalanceOnPayment::class,
        ],
        RoomServiceOrdered::class => [
            AddFolioChargeOnRoomService::class,
        ],
        MenuItemUpdated::class => [
            UpdateRoomServiceMenuOnMenuItemUpdate::class,
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
