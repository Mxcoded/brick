<?php

namespace Modules\Restaurant\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Staff\Events\StaffShiftStarted;

class ActivatePosOnShiftStart implements ShouldQueue
{
    public function handle(StaffShiftStarted $event): void
    {
        Log::info('POS activated for staff shift', [
            'employee_id' => $event->employee->id,
            'property_id' => $event->propertyId,
        ]);
    }
}
