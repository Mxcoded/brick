<?php

namespace Modules\Housekeeping\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Restaurant\Events\RoomServiceOrdered;

class AlertDeliveryOnRoomService implements ShouldQueue
{
    public function handle(RoomServiceOrdered $event): void
    {
        Log::info('Housekeeping alerted for room service delivery', [
            'order_id' => $event->order->id,
            'room_unit_id' => $event->roomUnitId,
            'property_id' => $event->propertyId,
        ]);
    }
}
