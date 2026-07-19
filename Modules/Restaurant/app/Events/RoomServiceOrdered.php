<?php

namespace Modules\Restaurant\Events;

use App\Events\BaseDomainEvent;
use Modules\Restaurant\Models\Order;

class RoomServiceOrdered extends BaseDomainEvent
{
    public function __construct(
        public Order $order,
        public int $registrationId,
        public int $roomUnitId,
        ?int $propertyId = null,
        ?int $userId = null
    ) {
        parent::__construct($propertyId ?? $order->property_id, $userId);
    }
}
