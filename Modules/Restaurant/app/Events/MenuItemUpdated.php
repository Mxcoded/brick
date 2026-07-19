<?php

namespace Modules\Restaurant\Events;

use App\Events\BaseDomainEvent;
use Modules\Restaurant\Models\MenuItem;

class MenuItemUpdated extends BaseDomainEvent
{
    public function __construct(
        public MenuItem $menuItem,
        public array $changedAttributes,
        ?int $propertyId = null,
        ?int $userId = null
    ) {
        parent::__construct($propertyId ?? $menuItem->property_id, $userId);
    }
}
