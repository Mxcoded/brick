<?php

namespace Modules\Restaurant\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Frontdeskcrm\Events\RegistrationCheckedIn;

class ActivateRoomServiceForRegistration implements ShouldQueue
{
    public function handle(RegistrationCheckedIn $event): void
    {
        Log::info('Room service activated for registration', [
            'registration_id' => $event->registration->id,
            'property_id' => $event->propertyId,
        ]);
    }
}
