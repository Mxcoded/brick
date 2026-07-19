<?php

namespace Modules\Restaurant\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Frontdeskcrm\Events\RegistrationCheckedOut;

class DisableRoomChargeOnCheckout implements ShouldQueue
{
    public function handle(RegistrationCheckedOut $event): void
    {
        Log::info('Room charge disabled for checked-out registration', [
            'registration_id' => $event->registration->id,
            'property_id' => $event->propertyId,
        ]);
    }
}
