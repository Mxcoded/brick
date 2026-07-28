<?php

namespace Modules\Frontdeskcrm\Services;

use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Services\Contracts\LedgerServiceInterface;
use Modules\Frontdeskcrm\Models\CityLedgerAccount;
use Modules\Frontdeskcrm\Models\CityLedgerTransaction;
use Modules\Frontdeskcrm\Models\Invoice;

class CityLedgerService
{
    public function __construct(
        protected LedgerServiceInterface $ledger
    ) {}

    public function createAccount(array $data, ?int $userId = null): CityLedgerAccount
    {
        $data['code'] = $data['code'] ?? CityLedgerAccount::generateCode();
        $data['created_by'] = $data['created_by'] ?? $userId;

        return CityLedgerAccount::create($data);
    }

    public function updateAccount(CityLedgerAccount $account, array $data): CityLedgerAccount
    {
        $account->update($data);

        return $account->fresh();
    }

    /**
     * Post a charge to a city ledger account.
     * Dr Accounts Receivable (1200), Cr Revenue (4000).
     */
    public function postCharge(
        CityLedgerAccount $account,
        float $amount,
        string $description,
        ?string $reference = null,
        ?int $registrationId = null,
        ?int $invoiceId = null,
        ?int $userId = null,
        ?string $date = null
    ): CityLedgerTransaction {
        $date = $date ?? now()->toDateString();

        $transaction = CityLedgerTransaction::create([
            'city_ledger_account_id' => $account->id,
            'registration_id' => $registrationId,
            'invoice_id' => $invoiceId,
            'transaction_type' => 'charge',
            'amount' => $amount,
            'description' => $description,
            'transaction_date' => $date,
            'reference' => $reference,
            'created_by' => $userId,
        ]);

        $account->increment('balance', $amount);

        $this->postJournal($account, 'CLCHARGE', $transaction->id, $amount, $date, $description);

        return $transaction;
    }

    /**
     * Record a payment from the corporate account.
     * Dr Cash/Bank (by method), Cr Accounts Receivable (1200).
     */
    public function recordPayment(
        CityLedgerAccount $account,
        float $amount,
        string $paymentMethod,
        string $description,
        ?string $reference = null,
        ?int $userId = null,
        ?string $date = null
    ): CityLedgerTransaction {
        $date = $date ?? now()->toDateString();

        $transaction = CityLedgerTransaction::create([
            'city_ledger_account_id' => $account->id,
            'transaction_type' => 'payment',
            'amount' => $amount,
            'description' => $description,
            'transaction_date' => $date,
            'reference' => $reference,
            'created_by' => $userId,
        ]);

        $account->decrement('balance', $amount);

        $assetCode = $this->assetForMethod($paymentMethod);
        $arCode = config('finance.accounts.accounts_receivable', '1200');

        $key = 'CLPAY-'.$transaction->id;
        if (! JournalEntry::where('entry_number', $key)->exists()) {
            $this->ledger->post(
                [
                    ['account_code' => $assetCode, 'debit' => $amount],
                    ['account_code' => $arCode, 'credit' => $amount],
                ],
                [
                    'entry_number' => $key,
                    'reference_type' => 'city_ledger_payment',
                    'reference_id' => $transaction->id,
                    'date' => $date,
                    'description' => $description,
                ]
            );
        }

        return $transaction;
    }

    /**
     * Create a credit note / adjustment for a city ledger account.
     * Dr Revenue (4000) / Cr Accounts Receivable (1200) — reverses the AR.
     */
    public function createCreditNote(
        CityLedgerAccount $account,
        float $amount,
        string $description,
        ?string $reference = null,
        ?int $userId = null,
        ?string $date = null
    ): CityLedgerTransaction {
        $date = $date ?? now()->toDateString();

        $transaction = CityLedgerTransaction::create([
            'city_ledger_account_id' => $account->id,
            'transaction_type' => 'credit_note',
            'amount' => $amount,
            'description' => $description,
            'transaction_date' => $date,
            'reference' => $reference,
            'created_by' => $userId,
        ]);

        $account->decrement('balance', $amount);

        $arCode = config('finance.accounts.accounts_receivable', '1200');
        $revenueCode = config('finance.accounts.revenue.frontdesk', '4000');

        $key = 'CLCN-'.$transaction->id;
        if (! JournalEntry::where('entry_number', $key)->exists()) {
            $this->ledger->post(
                [
                    ['account_code' => $revenueCode, 'debit' => $amount],
                    ['account_code' => $arCode, 'credit' => $amount],
                ],
                [
                    'entry_number' => $key,
                    'reference_type' => 'city_ledger_credit_note',
                    'reference_id' => $transaction->id,
                    'date' => $date,
                    'description' => $description,
                ]
            );
        }

        return $transaction;
    }

    /**
     * Generate aging report buckets.
     */
    public function getAgingReport(): array
    {
        $accounts = CityLedgerAccount::where('status', 'active')
            ->where('balance', '>', 0)
            ->get();

        $report = [];
        $now = now();

        foreach ($accounts as $account) {
            $aging = [
                'current' => 0,
                '1_30' => 0,
                '31_60' => 0,
                '61_90' => 0,
                '90_plus' => 0,
            ];

            $invoices = Invoice::where('city_ledger_account_id', $account->id)
                ->whereIn('status', ['issued', 'partial'])
                ->get();

            if ($invoices->isNotEmpty()) {
                foreach ($invoices as $invoice) {
                    $dueDate = $invoice->due_date ?? $invoice->issue_date;
                    $daysOverdue = $dueDate ? $now->diffInDays($dueDate, false) : 0;
                    $outstanding = $invoice->total - $invoice->paid_amount;

                    $this->addToAgingBucket($aging, $daysOverdue, $outstanding);
                }
            } else {
                $latestCharge = CityLedgerTransaction::where('city_ledger_account_id', $account->id)
                    ->where('transaction_type', 'charge')
                    ->latest('transaction_date')
                    ->first();

                $daysSinceCharge = $latestCharge
                    ? $now->diffInDays($latestCharge->transaction_date)
                    : 0;

                $this->addToAgingBucket($aging, $daysSinceCharge, $account->balance);
            }

            $totalOutstanding = array_sum($aging);

            if ($totalOutstanding > 0) {
                $report[] = [
                    'account' => $account,
                    'aging' => $aging,
                    'total_outstanding' => $totalOutstanding,
                ];
            }
        }

        return $report;
    }

    protected function addToAgingBucket(array &$aging, int $daysOverdue, float $amount): void
    {
        if ($daysOverdue <= 0) {
            $aging['current'] += $amount;
        } elseif ($daysOverdue <= 30) {
            $aging['1_30'] += $amount;
        } elseif ($daysOverdue <= 60) {
            $aging['31_60'] += $amount;
        } elseif ($daysOverdue <= 90) {
            $aging['61_90'] += $amount;
        } else {
            $aging['90_plus'] += $amount;
        }
    }

    public function linkInvoiceToAccount(Invoice $invoice, CityLedgerAccount $account): void
    {
        $invoice->update(['city_ledger_account_id' => $account->id]);
    }

    protected function postJournal(
        CityLedgerAccount $account,
        string $prefix,
        int $transactionId,
        float $amount,
        string $date,
        string $description
    ): void {
        $arCode = config('finance.accounts.accounts_receivable', '1200');
        $revenueCode = config('finance.accounts.revenue.frontdesk', '4000');

        $key = $prefix.'-'.$transactionId;
        if (! JournalEntry::where('entry_number', $key)->exists()) {
            $this->ledger->post(
                [
                    ['account_code' => $arCode, 'debit' => $amount],
                    ['account_code' => $revenueCode, 'credit' => $amount],
                ],
                [
                    'entry_number' => $key,
                    'reference_type' => 'city_ledger_transaction',
                    'reference_id' => $transactionId,
                    'date' => $date,
                    'description' => $description,
                ]
            );
        }
    }

    protected function assetForMethod(string $method): string
    {
        $kind = config("finance.payment_methods.$method", 'bank');
        $accounts = config('finance.accounts', []);

        return ($accounts[$kind] ?? null)
            ?? ($accounts['bank'] ?? '1100');
    }
}
