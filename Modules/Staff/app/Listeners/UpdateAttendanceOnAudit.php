<?php

namespace Modules\Staff\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Frontdeskcrm\Events\NightAuditCompleted;

class UpdateAttendanceOnAudit implements ShouldQueue
{
    public function handle(NightAuditCompleted $event): void
    {
        Log::info('Staff attendance updated after night audit', [
            'audit_date' => $event->auditDate,
            'property_id' => $event->propertyId,
        ]);
    }
}
