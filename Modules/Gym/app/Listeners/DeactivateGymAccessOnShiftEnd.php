<?php

namespace Modules\Gym\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Staff\Events\StaffShiftEnded;

class DeactivateGymAccessOnShiftEnd implements ShouldQueue
{
    public function handle(StaffShiftEnded $event): void
    {
        Log::info('Gym access deactivated for staff shift', [
            'employee_id' => $event->employee->id,
            'property_id' => $event->propertyId,
        ]);
    }
}
