<?php

namespace Modules\Housekeeping\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Frontdeskcrm\Events\RegistrationCheckedIn;

class SetRoomStatusOnCheckin implements ShouldQueue
{
    public function handle(RegistrationCheckedIn $event): void
    {
        Log::info('Room status updated on check-in', [
            'registration_id' => $event->registration->id,
            'property_id' => $event->propertyId,
        ]);
    }
}
