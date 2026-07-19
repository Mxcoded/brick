<?php

namespace Modules\Housekeeping\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Frontdeskcrm\Events\RegistrationCheckedIn;
use Modules\Housekeeping\Listeners\AlertDeliveryOnRoomService;
use Modules\Housekeeping\Listeners\SetRoomStatusOnCheckin;
use Modules\Restaurant\Events\RoomServiceOrdered;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        RegistrationCheckedIn::class => [
            SetRoomStatusOnCheckin::class,
        ],
        RoomServiceOrdered::class => [
            AlertDeliveryOnRoomService::class,
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
