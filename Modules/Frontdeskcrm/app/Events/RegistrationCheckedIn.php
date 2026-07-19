<?php

namespace Modules\Frontdeskcrm\Events;

use App\Events\BaseDomainEvent;
use Modules\Frontdeskcrm\Models\Registration;

class RegistrationCheckedIn extends BaseDomainEvent
{
    public function __construct(
        public Registration $registration,
        ?int $propertyId = null,
        ?int $userId = null
    ) {
        parent::__construct($propertyId ?? $registration->property_id, $userId);
    }
}
