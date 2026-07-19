<?php

namespace Modules\Finance\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Frontdeskcrm\Events\DepositReceived;

class RecordDepositOnDepositReceived implements ShouldQueue
{
    public function handle(DepositReceived $event): void
    {
        Log::info('Deposit recorded in finance', [
            'registration_id' => $event->registration->id,
            'amount' => $event->amount,
            'property_id' => $event->propertyId,
        ]);
    }
}
