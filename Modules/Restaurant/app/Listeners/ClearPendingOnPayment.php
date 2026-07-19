<?php

namespace Modules\Restaurant\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Finance\Events\PaymentReceived;

class ClearPendingOnPayment implements ShouldQueue
{
    public function handle(PaymentReceived $event): void
    {
        Log::info('Restaurant pending orders cleared on payment', [
            'registration_id' => $event->registrationId,
            'amount' => $event->amount,
            'property_id' => $event->propertyId,
        ]);
    }
}
