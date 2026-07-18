<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Modules\Frontdeskcrm\Models\Booking;
use Modules\Frontdeskcrm\Models\Registration;

class MarkNoShows extends Command
{
    protected $signature = 'hotel:mark-no-shows {--dry-run : Preview changes without updating}';

    protected $description = 'Auto-mark overdue website bookings as no-show (1+ day past check-in with no registration).';

    public function handle(): int
    {
        $today = now()->startOfDay();

        $bookedIds = Registration::whereNotNull('booking_id')
            ->pluck('booking_id')
            ->toArray();

        $overdueBookings = Booking::whereIn('status', ['pending', 'confirmed'])
            ->where('check_in_date', '<', $today)
            ->whereNotIn('id', $bookedIds)
            ->get();

        $count = 0;

        foreach ($overdueBookings as $booking) {
            $daysOverdue = $today->diffInDays($booking->check_in_date, false);

            if ($daysOverdue >= 1) {
                if ($this->option('dry-run')) {
                    $this->line("Would mark no-show: {$booking->booking_reference} ({$daysOverdue} days overdue)");
                } else {
                    $booking->update(['status' => 'no_show']);
                    Log::info("Auto no-show for booking {$booking->booking_reference} — {$daysOverdue} day(s) overdue.");
                }
                $count++;
            }
        }

        $action = $this->option('dry-run') ? 'Would mark' : 'Marked';

        $this->info("{$action} {$count} booking(s) as no-show.");

        return self::SUCCESS;
    }
}
