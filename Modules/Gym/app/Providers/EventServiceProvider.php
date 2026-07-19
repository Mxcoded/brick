<?php

namespace Modules\Gym\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Gym\Listeners\ActivateGymAccessOnShiftStart;
use Modules\Gym\Listeners\DeactivateGymAccessOnShiftEnd;
use Modules\Staff\Events\StaffShiftEnded;
use Modules\Staff\Events\StaffShiftStarted;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event handler mappings for the application.
     *
     * @var array<string, array<int, string>>
     */
    protected $listen = [
        StaffShiftStarted::class => [
            ActivateGymAccessOnShiftStart::class,
        ],
        StaffShiftEnded::class => [
            DeactivateGymAccessOnShiftEnd::class,
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
