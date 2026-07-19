<?php

namespace Modules\Gym\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Staff\Events\StaffShiftStarted;

class ActivateGymAccessOnShiftStart implements ShouldQueue
{
    public function handle(StaffShiftStarted $event): void
    {
        Log::info('Gym access activated for staff shift', [
            'employee_id' => $event->employee->id,
            'property_id' => $event->propertyId,
        ]);
    }
}
