<?php

namespace Modules\Finance\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Log;
use Modules\Frontdeskcrm\Events\RegistrationCheckedOut;

class CloseFolioOnCheckout implements ShouldQueue
{
    public function handle(RegistrationCheckedOut $event): void
    {
        Log::info('Folio closed on checkout', [
            'registration_id' => $event->registration->id,
            'property_id' => $event->propertyId,
        ]);
    }
}
