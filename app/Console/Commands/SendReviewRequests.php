<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Services\GuestMessagingService;

class SendReviewRequests extends Command
{
    protected $signature = 'hotel:send-review-requests';
    protected $description = 'Sends post-stay review request emails to recently checked-out guests';

    public function handle(GuestMessagingService $messaging): int
    {
        $this->info('Sending review requests...');

        $registrations = Registration::where('stay_status', 'checked_out')
            ->whereHas('guest', fn($q) => $q->whereNotNull('email'))
            ->whereDate('check_out', now()->subDay())
            ->whereDoesntHave('messages', fn($q) => $q->where('status', 'sent'))
            ->get();

        if ($registrations->isEmpty()) {
            $this->info('No recently checked-out guests found.');
            return self::SUCCESS;
        }

        $sent = 0;
        foreach ($registrations as $registration) {
            $result = $messaging->sendFromTemplate($registration, 'review_request', 'email');
            if ($result) {
                $sent++;
            }
        }

        $this->info("Sent {$sent} review requests.");
        return self::SUCCESS;
    }
}
