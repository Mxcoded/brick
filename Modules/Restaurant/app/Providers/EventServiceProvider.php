<?php

namespace Modules\Restaurant\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Modules\Finance\Events\PaymentReceived;
use Modules\Frontdeskcrm\Events\RegistrationCheckedIn;
use Modules\Frontdeskcrm\Events\RegistrationCheckedOut;
use Modules\Restaurant\Listeners\ActivatePosOnShiftStart;
use Modules\Restaurant\Listeners\ActivateRoomServiceForRegistration;
use Modules\Restaurant\Listeners\ClearPendingOnPayment;
use Modules\Restaurant\Listeners\DeactivatePosOnShiftEnd;
use Modules\Restaurant\Listeners\DisableRoomChargeOnCheckout;
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
        RegistrationCheckedIn::class => [
            ActivateRoomServiceForRegistration::class,
        ],
        RegistrationCheckedOut::class => [
            DisableRoomChargeOnCheckout::class,
        ],
        PaymentReceived::class => [
            ClearPendingOnPayment::class,
        ],
        StaffShiftStarted::class => [
            ActivatePosOnShiftStart::class,
        ],
        StaffShiftEnded::class => [
            DeactivatePosOnShiftEnd::class,
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
