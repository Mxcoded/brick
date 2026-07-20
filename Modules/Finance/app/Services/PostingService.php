<?php

namespace Modules\Finance\Services;

use Modules\Finance\Models\JournalEntry;
use Modules\Finance\Services\Contracts\LedgerServiceInterface;

class PostingService
{
    public function __construct(
        protected LedgerServiceInterface $ledger
    ) {}

    /**
     * Resolves a free-text payment method to a Cash or Bank GL account code.
     */
    public function assetForMethod(string $method): string
    {
        $kind = config("finance.payment_methods.$method", 'bank');
        $accounts = config('finance.accounts', []);

        return ($accounts[$kind] ?? null)
            ?? ($accounts['bank'] ?? '1100');
    }

    /**
     * Posts a sale / cash receipt: debit asset, credit revenue.
     * Idempotent per (reference_type, reference_id).
     */
    public function recordSale(
        string $module,
        float $amount,
        string $paymentMethod,
        string $referenceType,
        int $referenceId,
        ?string $date = null,
        ?string $description = null
    ): ?JournalEntry {
        if ($amount <= 0) {
            return null;
        }

        $assetCode = $this->assetForMethod($paymentMethod);
        $revenueCode = config("finance.accounts.revenue.$module", '4900');

        return $this->postIdempotent(
            "SALE-$referenceType-$referenceId",
            [
                ['account_code' => $assetCode, 'debit' => $amount],
                ['account_code' => $revenueCode, 'credit' => $amount],
            ],
            [
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'date' => $date,
                'description' => $description ?? "Sale: $module #$referenceId",
            ]
        );
    }

    /**
     * Posts an outflow: debit expense, credit asset.
     * Idempotent per (reference_type, reference_id).
     */
    public function recordExpense(
        string $module,
        float $amount,
        string $paymentMethod,
        string $referenceType,
        int $referenceId,
        ?string $date = null,
        ?string $description = null
    ): ?JournalEntry {
        if ($amount <= 0) {
            return null;
        }

        $assetCode = $this->assetForMethod($paymentMethod);
        $expenseCode = config("finance.accounts.expense.$module", '5900');

        return $this->postIdempotent(
            "EXP-$referenceType-$referenceId",
            [
                ['account_code' => $expenseCode, 'debit' => $amount],
                ['account_code' => $assetCode, 'credit' => $amount],
            ],
            [
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'date' => $date,
                'description' => $description ?? "Expense: $module #$referenceId",
            ]
        );
    }

    /**
     * Posts goods-received liability (accrual): debit inventory/expense, credit AP.
     * No cash side yet — supplier settlement is not modeled in Inventory.
     * Idempotent per (reference_type, reference_id).
     */
    public function recordApLiability(
        float $amount,
        string $expenseCode,
        string $referenceType,
        int $referenceId,
        ?string $date = null,
        ?string $description = null
    ): ?JournalEntry {
        if ($amount <= 0) {
            return null;
        }

        $apCode = config('finance.accounts.accounts_payable', '2000');

        return $this->postIdempotent(
            "AP-$referenceType-$referenceId",
            [
                ['account_code' => $expenseCode, 'debit' => $amount],
                ['account_code' => $apCode, 'credit' => $amount],
            ],
            [
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'date' => $date,
                'description' => $description ?? "AP liability: $referenceType #$referenceId",
            ]
        );
    }

    /**
     * Reverses a sale: debit revenue, credit asset.
     * Idempotent per (reference_type, reference_id).
     * Use the same reference_type/reference_id as the original sale to reverse it.
     */
    public function recordRefund(
        string $module,
        float $amount,
        string $paymentMethod,
        string $referenceType,
        int $referenceId,
        ?string $date = null,
        ?string $description = null
    ): ?JournalEntry {
        if ($amount <= 0) {
            return null;
        }

        $assetCode = $this->assetForMethod($paymentMethod);
        $revenueCode = config("finance.accounts.revenue.$module", '4900');

        return $this->postIdempotent(
            "REF-$referenceType-$referenceId",
            [
                ['account_code' => $revenueCode, 'debit' => $amount],
                ['account_code' => $assetCode, 'credit' => $amount],
            ],
            [
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'date' => $date,
                'description' => $description ?? "Refund: $module #$referenceId",
            ]
        );
    }

    /**
     * Posts settlement of a payable: debit AP, credit asset.
     */
    public function recordApPayment(
        float $amount,
        string $paymentMethod,
        string $referenceType,
        int $referenceId,
        ?string $date = null,
        ?string $description = null
    ): ?JournalEntry {
        if ($amount <= 0) {
            return null;
        }

        $assetCode = $this->assetForMethod($paymentMethod);
        $apCode = config('finance.accounts.accounts_payable', '2000');

        return $this->postIdempotent(
            "APP-$referenceType-$referenceId",
            [
                ['account_code' => $apCode, 'debit' => $amount],
                ['account_code' => $assetCode, 'credit' => $amount],
            ],
            [
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'date' => $date,
                'description' => $description ?? "AP payment: $referenceType #$referenceId",
            ]
        );
    }

    /**
     * Skips posting if an entry with this deterministic key already exists.
     */
    protected function postIdempotent(string $key, array $lines, array $meta): ?JournalEntry
    {
        if (JournalEntry::where('entry_number', $key)->exists()) {
            return null;
        }

        return $this->ledger->post($lines, array_merge($meta, ['entry_number' => $key]));
    }
}
