<?php

namespace Modules\Staff\Console; // <-- This namespace is now correct for your module

use Illuminate\Console\Command;
use Modules\Staff\Models\LeaveRequest;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class BackfillLeaveDaysCount extends Command
{
    /**
     * The name and signature of the console command.
     * We'll prefix it with 'staff:' to keep it organized.
     *
     * @var string
     */
    protected $signature = 'staff:backfill-leave-days-count';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and fill the days_count for old leave requests in the Staff module.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to backfill leave days count for the Staff module...');

        $requestsToUpdate = LeaveRequest::whereNull('days_count')->get();

        if ($requestsToUpdate->isEmpty()) {
            $this->info('No records needed updating. All leave requests have a days_count.');
            return 0;
        }

        $progressBar = $this->output->createProgressBar($requestsToUpdate->count());
        $progressBar->start();

        foreach ($requestsToUpdate as $request) {
            $startDate = Carbon::parse($request->start_date);
            $endDate = Carbon::parse($request->end_date);
            $year = $startDate->year;
            $publicHolidays = config("holidays.{$year}", []);
            $leaveDaysCount = 0;

            $period = CarbonPeriod::create($startDate, $endDate);

            foreach ($period as $date) {
                if ($date->isWeekend() || in_array($date->format('m-d'), $publicHolidays)) {
                    continue;
                }
                $leaveDaysCount++;
            }

            $request->days_count = $leaveDaysCount;
            $request->saveQuietly();

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->info("\nSuccessfully updated {$requestsToUpdate->count()} records.");

        return 0;
    }
}
