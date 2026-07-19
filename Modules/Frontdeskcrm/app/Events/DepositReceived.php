<?php

namespace Modules\Frontdeskcrm\Events;

use App\Events\BaseDomainEvent;
use Modules\Frontdeskcrm\Models\Registration;

class DepositReceived extends BaseDomainEvent
{
    public function __construct(
        public Registration $registration,
        public float $amount,
        ?int $propertyId = null,
        ?int $userId = null
    ) {
        parent::__construct($propertyId ?? $registration->property_id, $userId);
    }
}
