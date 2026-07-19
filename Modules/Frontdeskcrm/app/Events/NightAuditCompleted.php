<?php

namespace Modules\Frontdeskcrm\Events;

use App\Events\BaseDomainEvent;

class NightAuditCompleted extends BaseDomainEvent
{
    public function __construct(
        public string $auditDate,
        public array $summary,
        ?int $propertyId = null,
        ?int $userId = null
    ) {
        parent::__construct($propertyId, $userId);
    }
}
