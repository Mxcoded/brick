<?php

namespace Modules\Frontdeskcrm\Observers;

use Illuminate\Support\Facades\Log;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Services\GuestMessagingService;
use Modules\Frontdeskcrm\Services\PreArrivalService;

class RegistrationObserver
{
    public function created(Registration $registration): void
    {
        if ($registration->stay_status === 'reserved') {
            $this->generateTokenAndSendPreArrival($registration);
        }
    }

    public function updated(Registration $registration): void
    {
        if ($registration->wasChanged('stay_status') && $registration->stay_status === 'reserved' && ! $registration->pre_arrival_token) {
            $this->generateTokenAndSendPreArrival($registration);
        }
    }

    private function generateTokenAndSendPreArrival(Registration $registration): void
    {
        try {
            $preArrival = app(PreArrivalService::class);
            $preArrival->generateToken($registration);

            $messaging = app(GuestMessagingService::class);
            $messaging->sendFromTemplate($registration, 'pre_arrival_reminder', 'email');
        } catch (\Exception $e) {
            Log::error('Failed to auto-send pre-arrival', [
                'registration_id' => $registration->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
