<?php

namespace Modules\Finance\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Frontdeskcrm\Events\NightAuditCompleted;

class GenerateDailyReportsOnAudit implements ShouldQueue
{
    public function handle(NightAuditCompleted $event): void
    {
        Log::info('Daily reports generated after night audit', [
            'audit_date' => $event->auditDate,
            'summary' => $event->summary,
            'property_id' => $event->propertyId,
        ]);
    }
}
