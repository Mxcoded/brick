<?php

namespace Modules\Restaurant\Console;

use Illuminate\Console\Command;
use Modules\Finance\Services\PostingService;
use Modules\Restaurant\Models\Payment;

class ReconcileFinance extends Command
{
    protected $signature = 'restaurant:reconcile-finance {--dry-run : Preview without posting}';
    protected $description = 'Retry finance posting for restaurant payments that were not posted';

    public function handle(): int
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

            try {
                $entry = app(PostingService::class)
                    ->recordSale('restaurant', (float) $payment->amount, $payment->method, 'restaurant_payment', $payment->id);

                if ($entry) {
                    $payment->finance_posted = true;
                    $payment->save();
                    $this->info("  ✓ Posted (entry: {$entry->entry_number})");
                } else {
                    $this->warn("  — Skipped (duplicate key)");
                }
            } catch (\Throwable $e) {
                $this->error("  ✗ Failed: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
