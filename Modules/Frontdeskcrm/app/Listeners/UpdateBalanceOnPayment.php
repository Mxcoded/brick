<?php

namespace Modules\Frontdeskcrm\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Finance\Events\PaymentReceived;

class UpdateBalanceOnPayment implements ShouldQueue
{
    public function handle(PaymentReceived $event): void
    {
        Log::info('Registration balance updated on payment', [
            'registration_id' => $event->registrationId,
            'amount' => $event->amount,
            'property_id' => $event->propertyId,
        ]);
    }
}
