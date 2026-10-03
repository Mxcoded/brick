<?php

namespace Modules\Restaurant\Console;

use Illuminate\Console\Command;
use Modules\Restaurant\Models\Payment;
use Modules\Restaurant\Services\OrderPaymentService;

class ReconcileFinance extends Command
{
    protected $signature = 'restaurant:reconcile-finance {--dry-run : Preview without posting}';

    protected $description = 'Retry finance posting for restaurant payments that were not posted';

    public function handle(OrderPaymentService $payments): int
    {
        $unposted = Payment::where('status', 'completed')
            ->where('finance_posted', false)
            ->get();

        if ($unposted->isEmpty()) {
            $this->info('All completed payments are posted to Finance.');

            return self::SUCCESS;
        }

        $this->warn("Found {$unposted->count()} payment(s) missing finance entries.");

        foreach ($unposted as $payment) {
            $this->line("  Payment #{$payment->id} — Order #{$payment->restaurant_order_id} — ₦{$payment->amount} ({$payment->method})");

            if ($this->option('dry-run')) {
                continue;
            }

            // Posting goes back through the service rather than reaching for
            // PostingService directly, so a fix to the source or reference slug
            // applies to the repair path too. A false return means the ledger
            // refused or threw; the payment stays unflagged for the next run.
            if (! $payments->postSale($payment)) {
                $this->error('  ✗ Failed to post');
            }
        }

        return self::SUCCESS;
    }
}
