<?php

namespace Modules\Frontdeskcrm\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Restaurant\Events\RoomServiceOrdered;

class AddFolioChargeOnRoomService implements ShouldQueue
{
    public function handle(RoomServiceOrdered $event): void
    {
        Log::info('Folio charge added for room service order', [
            'order_id' => $event->order->id,
            'registration_id' => $event->registrationId,
            'property_id' => $event->propertyId,
        ]);
    }
}
