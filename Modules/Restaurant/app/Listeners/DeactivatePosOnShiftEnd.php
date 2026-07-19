<?php

namespace Modules\Restaurant\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Staff\Events\StaffShiftEnded;

class DeactivatePosOnShiftEnd implements ShouldQueue
{
    public function handle(StaffShiftEnded $event): void
    {
        Log::info('POS deactivated for staff shift', [
            'employee_id' => $event->employee->id,
            'property_id' => $event->propertyId,
        ]);
    }
}
