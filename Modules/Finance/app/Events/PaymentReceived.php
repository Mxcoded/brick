<?php

namespace Modules\Finance\Events;

use App\Events\BaseDomainEvent;

class PaymentReceived extends BaseDomainEvent
{
    public function __construct(
        public int $registrationId,
        public float $amount,
        public string $method,
        ?int $propertyId = null,
        ?int $userId = null
    ) {
        parent::__construct($propertyId, $userId);
    }
}
