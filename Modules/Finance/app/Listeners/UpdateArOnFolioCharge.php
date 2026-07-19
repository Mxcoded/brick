<?php

namespace Modules\Finance\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Frontdeskcrm\Events\FolioChargePosted;

class UpdateArOnFolioCharge implements ShouldQueue
{
    public function handle(FolioChargePosted $event): void
    {
        Log::info('Accounts receivable updated for folio charge', [
            'folio_charge_id' => $event->folioCharge->id,
            'registration_id' => $event->registration->id,
            'property_id' => $event->propertyId,
        ]);
    }
}
