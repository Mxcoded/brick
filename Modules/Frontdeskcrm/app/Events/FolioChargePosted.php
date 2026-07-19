<?php

namespace Modules\Frontdeskcrm\Events;

use App\Events\BaseDomainEvent;
use Modules\Frontdeskcrm\Models\FolioCharge;
use Modules\Frontdeskcrm\Models\Registration;

class FolioChargePosted extends BaseDomainEvent
{
    public function __construct(
        public FolioCharge $folioCharge,
        public Registration $registration,
        ?int $propertyId = null,
        ?int $userId = null
    ) {
        parent::__construct($propertyId ?? $registration->property_id, $userId);
    }
}
