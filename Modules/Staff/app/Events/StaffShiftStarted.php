<?php

namespace Modules\Staff\Events;

use App\Events\BaseDomainEvent;
use Modules\Staff\Models\Employee;

class StaffShiftStarted extends BaseDomainEvent
{
    public function __construct(
        public Employee $employee,
        public int $shiftAssignmentId,
        ?int $propertyId = null,
        ?int $userId = null
    ) {
        parent::__construct($propertyId ?? $employee->property_id, $userId);
    }
}
