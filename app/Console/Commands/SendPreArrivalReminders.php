<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Services\GuestMessagingService;

class SendPreArrivalReminders extends Command
{
    protected $signature = 'hotel:send-pre-arrival-reminders';

    protected $description = 'Sends pre-arrival email reminders to guests with upcoming reservations';

    public function handle(GuestMessagingService $messaging): int
    {
        $this->info('Sending pre-arrival reminders...');

        $registrations = Registration::whereNull('pre_arrival_completed_at')
            ->whereIn('stay_status', ['reserved'])
            ->whereHas('guest', fn ($q) => $q->whereNotNull('email'))
            ->where('check_in', '>=', now()->subDay())
            ->where('check_in', '<=', now()->addDays(3))
            ->get();

        if ($registrations->isEmpty()) {
            $this->info('No pending pre-arrivals found.');

            return self::SUCCESS;
        }

        $sent = 0;
        foreach ($registrations as $registration) {
            $result = $messaging->sendFromTemplate($registration, 'pre_arrival_reminder', 'email');
            if ($result) {
                $sent++;
            }
        }

        $this->info("Sent {$sent} of {$registrations->count()} pre-arrival reminders.");

        return self::SUCCESS;
    }
}
