<?php

namespace Modules\Website\Console\Commands;

use Illuminate\Console\Command;
use Modules\Website\Models\Booking;

class FixConfirmedBookingBalances extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bookings:fix-balances 
                            {--dry-run : Show what would be fixed without making changes}
                            {--status=confirmed : Filter by booking status (confirmed, checked_in, checked_out, or all)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fix confirmed bookings that have payment_status=paid but amount_paid does not match total_amount';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $statusFilter = $this->option('status');

        $this->info('');
        $this->info('=================================================');
        $this->info('  Brickspoint Booking Balance Fix Tool');
        $this->info('=================================================');
        $this->info('');

        if ($dryRun) {
            $this->warn('Running in DRY RUN mode - no changes will be made');
            $this->info('');
        }

        // Build query for bookings that need fixing
        $query = Booking::where('payment_status', 'paid')
            ->whereColumn('amount_paid', '<', 'total_amount');

        // Apply status filter
        if ($statusFilter !== 'all') {
            $statuses = explode(',', $statusFilter);
            $query->whereIn('status', $statuses);
        }

        $bookingsToFix = $query->get();

        if ($bookingsToFix->isEmpty()) {
            $this->info('✓ No bookings found that need fixing.');
            $this->info('');
            return Command::SUCCESS;
        }

        $this->info("Found {$bookingsToFix->count()} booking(s) that need fixing:");
        $this->info('');

        // Display table of bookings to fix
        $tableData = [];
        foreach ($bookingsToFix as $booking) {
            $balanceDue = $booking->total_amount - $booking->amount_paid;
            $tableData[] = [
                $booking->booking_reference,
                $booking->guest_name,
                $booking->status,
                $booking->payment_status,
                number_format($booking->total_amount, 2),
                number_format($booking->amount_paid, 2),
                number_format($balanceDue, 2),
            ];
        }

        $this->table(
            ['Reference', 'Guest', 'Status', 'Payment Status', 'Total', 'Paid', 'Balance Due'],
            $tableData
        );

        $this->info('');

        if ($dryRun) {
            $this->warn("DRY RUN: Would update {$bookingsToFix->count()} booking(s)");
            $this->info('Run without --dry-run to apply changes.');
            return Command::SUCCESS;
        }

        // Confirm before making changes
        if (!$this->confirm('Do you want to fix these bookings? (Set amount_paid = total_amount)')) {
            $this->info('Operation cancelled.');
            return Command::SUCCESS;
        }

        // Fix the bookings
        $fixedCount = 0;
        $errors = [];

        $this->output->progressStart($bookingsToFix->count());

        foreach ($bookingsToFix as $booking) {
            try {
                $oldAmountPaid = $booking->amount_paid;
                $booking->update([
                    'amount_paid' => $booking->total_amount,
                ]);
                
                $fixedCount++;
                
                // Log the fix
                \Log::info('Booking balance fixed', [
                    'booking_reference' => $booking->booking_reference,
                    'old_amount_paid' => $oldAmountPaid,
                    'new_amount_paid' => $booking->total_amount,
                    'total_amount' => $booking->total_amount,
                ]);
            } catch (\Exception $e) {
                $errors[] = [
                    'reference' => $booking->booking_reference,
                    'error' => $e->getMessage(),
                ];
            }

            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
        $this->info('');

        // Summary
        $this->info('=================================================');
        $this->info('  Summary');
        $this->info('=================================================');
        $this->info("✓ Fixed: {$fixedCount} booking(s)");

        if (!empty($errors)) {
            $this->error("✗ Errors: " . count($errors) . " booking(s)");
            $this->info('');
            $this->error('Failed bookings:');
            foreach ($errors as $error) {
                $this->error("  - {$error['reference']}: {$error['error']}");
            }
        }

        $this->info('');
        $this->info('Done!');

        return empty($errors) ? Command::SUCCESS : Command::FAILURE;
    }
}
