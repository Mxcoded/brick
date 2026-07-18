<?php

namespace Modules\Website\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Modules\Website\Emails\PostStayFollowUp;
use Modules\Website\Models\Booking;

class SendPostStayFollowUp extends Command
{
    protected $signature = 'website:send-post-stay-followup';

    protected $description = 'Send post-stay follow-up emails for recently completed bookings';

    public function handle()
    {
        $yesterday = Carbon::yesterday();

        $bookings = Booking::where('status', 'completed')
            ->whereDate('updated_at', $yesterday)
            ->whereNull('follow_up_sent_at')
            ->get();

        if ($bookings->isEmpty()) {
            $this->info('No completed bookings to follow up on.');

            return 0;
        }

        $sent = 0;
        foreach ($bookings as $booking) {
            try {
                Mail::to($booking->guest_email)->queue(new PostStayFollowUp($booking));
                $booking->update(['follow_up_sent_at' => now()]);
                $sent++;
                $this->line("Sent follow-up for {$booking->booking_reference} ({$booking->guest_email})");
            } catch (\Exception $e) {
                Log::error("Post-stay follow-up failed for {$booking->booking_reference}: {$e->getMessage()}");
                $this->error("Failed for {$booking->booking_reference}: {$e->getMessage()}");
            }
        }

        $this->info("Sent {$sent} follow-up email(s).");

        return 0;
    }
}
