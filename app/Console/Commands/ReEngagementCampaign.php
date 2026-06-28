<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Services\GuestMessagingService;

class ReEngagementCampaign extends Command
{
    protected $signature = 'hotel:re-engagement-campaign';
    protected $description = 'Sends re-engagement offers to past guests who have not visited in 90+ days';

    public function handle(GuestMessagingService $messaging): int
    {
        $this->info('Running re-engagement campaign...');

        $ninetyDaysAgo = now()->subDays(90);

        $registrations = Registration::where('stay_status', 'checked_out')
            ->whereDate('check_out', '<=', $ninetyDaysAgo)
            ->whereHas('guest', fn($q) => $q->whereNotNull('email'))
            ->whereDoesntHave('messages', fn($q) => $q->where('status', 'sent'))
            ->get();

        if ($registrations->isEmpty()) {
            $this->info('No past guests eligible for re-engagement.');
            return self::SUCCESS;
        }

        $sent = 0;
        foreach ($registrations as $registration) {
            $result = $messaging->sendFromTemplate($registration, 're_engagement', 'email');
            if ($result) {
                $sent++;
            }
        }

        $this->info("Sent {$sent} re-engagement messages.");
        return self::SUCCESS;
    }
}
