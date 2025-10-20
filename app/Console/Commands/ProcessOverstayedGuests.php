<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Modules\Frontdeskcrm\Models\Registration;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;

class ProcessOverstayedGuests extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'hotel:process-overstays';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Finds and extends stays for any checked-in guests whose checkout date has passed.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to process overstayed guests...');
        Log::info('Scheduled Task: Starting ProcessOverstayedGuests...');

        // Find all registrations that are still 'checked_in' but whose
        // check_out date was yesterday or any day before.
        $overstayedRegistrations = Registration::where('stay_status', 'checked_in')
            ->whereDate('check_out', '<', Carbon::today())
            ->get();

        if ($overstayedRegistrations->isEmpty()) {
            $this->info('No overstayed guests found.');
            Log::info('Scheduled Task: No overstayed guests found.');
            return self::SUCCESS;
        }

        $this->info("Found {$overstayedRegistrations->count()} overstayed registration(s). Recalculating their stay...");

        foreach ($overstayedRegistrations as $registration) {
            $oldCheckoutDate = $registration->check_out->format('Y-m-d');

            // **THE FIX**: Instead of just adding one day, we calculate the correct state.
            // The new checkout date should always be tomorrow, as they are still an active guest.
            $newCheckoutDate = Carbon::tomorrow();

            // Recalculate the total number of nights from the original check-in date.
            $newNumberOfNights = $registration->check_in->diffInDays($newCheckoutDate);

            // Recalculate the total bill based on the new total nights.
            $newTotalAmount = $registration->room_rate * $newNumberOfNights;

            $registration->update([
                'check_out' => $newCheckoutDate,
                'no_of_nights' => $newNumberOfNights,
                'total_amount' => $newTotalAmount,
            ]);
            if ($registration->parent_registration_id) {
                $leadRegistration = Registration::find($registration->parent_registration_id);
                if ($leadRegistration) {
                    // Recalculate the lead's total by summing its own bill + all children's bills
                    $newLeadTotal = $leadRegistration->children()->sum('total_amount')
                        + ($leadRegistration->room_rate * $leadRegistration->no_of_nights);

                    $leadRegistration->update(['total_amount' => $newLeadTotal]);
                    Log::info("Updated Group Lead #{$leadRegistration->id} total to {$newLeadTotal}.");
                }
            }
            $logMessage = "Extended stay for Registration #{$registration->id} (Guest: {$registration->full_name}). Old checkout: {$oldCheckoutDate}, New checkout: {$newCheckoutDate->format('Y-m-d')}. Bill updated.";
            $this->info($logMessage);
            Log::info($logMessage);
        }

        $this->info('Successfully processed all overstayed guests.');
        Log::info('Scheduled Task: ProcessOverstayedGuests finished.');
        return self::SUCCESS;
    }
}
