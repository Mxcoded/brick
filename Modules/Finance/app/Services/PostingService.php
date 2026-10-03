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
     * The lines for a gross receipt: debit the asset the money arrived in,
     * credit each component to the account that owns it.
     *
     * The tax and service charge lines are omitted when they are zero rather
     * than posted as 0.00, so a module that has neither does not need those
     * accounts to exist in its chart, and a zero-value line never shows up in a
     * trial balance as noise.
     *
     * Each component is rounded to the currency and the net is derived from
     * them, never the other way round, so the entry balances to the cent even
     * when the stored figures do not add up exactly.
     *
     * @return array<int, array{account_code: string, debit?: float, credit?: float}>
     */
    protected function saleLines(
        string $module,
        float $gross,
        string $assetCode,
        float $vat = 0.0,
        float $serviceCharge = 0.0,
        ?string $revenueCode = null
    ): array {
        $vat = max(0.0, round($vat, 2));
        $serviceCharge = max(0.0, round($serviceCharge, 2));

        // Never let the splits exceed what was actually collected. A caller that
        // passes the full order's VAT against a partial payment would otherwise
        // post credits with no debit to match.
        $vat = min($vat, $gross);
        $serviceCharge = min($serviceCharge, max(0.0, $gross - $vat));
        $net = round($gross - $vat - $serviceCharge, 2);

        $lines = [['account_code' => $assetCode, 'debit' => $gross]];

        if ($net > 0) {
            $lines[] = ['account_code' => $revenueCode ?: $this->revenueCodeFor($module), 'credit' => $net];
        }
        if ($vat > 0) {
            $lines[] = ['account_code' => config('finance.accounts.tax_payable', '2200'), 'credit' => $vat];
        }
        if ($serviceCharge > 0) {
            $lines[] = ['account_code' => config('finance.accounts.service_charge_revenue', '4400'), 'credit' => $serviceCharge];
        }

        return $lines;
    }

    /**
     * Posts a sale / cash receipt: debit asset, credit revenue.
     *
     * $vat and $serviceCharge are the non-revenue parts of $amount. Pass them
     * and the gross is split across revenue, tax payable and service charge
     * revenue; leave them null and the whole amount stays in revenue, which is
     * what a module with neither component wants.
     *
     * $revenueCode credits a specific income account instead of the module's
     * own. This is how a module with several outlets books each to its own
     * revenue line; null keeps the module default.
     *
     * $assetCode debits a specific bank or till account instead of the one the
     * payment method implies. This is how a module with several physical
     * terminals books each into its own bank; null keeps the method mapping, so
     * cash still lands in the till and card still lands in the bank.
     *
     * Idempotent per (reference_type, reference_id). Entries posted before this
     * split existed keep their two-line shape: the key is derived from the same
     * fields, so an already-posted payment is never re-cut and no duplicate
     * arises from the change.
     */
    public function recordSale(
        string $module,
        float $amount,
        string $paymentMethod,
        string $referenceType,
        int $referenceId,
        ?string $date = null,
        ?string $description = null,
        ?float $vat = null,
        ?float $serviceCharge = null,
        ?string $revenueCode = null,
        ?string $assetCode = null
    ): ?JournalEntry {
        if ($amount <= 0) {
            return null;
        }

        $lines = $this->saleLines(
            $module,
            $amount,
            $assetCode ?: $this->assetForMethod($paymentMethod),
            $vat ?? 0.0,
            $serviceCharge ?? 0.0,
            $revenueCode,
        );

        return $this->postIdempotent(
            "SALE-$referenceType-$referenceId",
            $lines,
            [
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'date' => $date,
                'description' => $description ?? "Sale: $module #$referenceId",
            ]
        );
    }

    /**
     * Reverses a sale: debit each account the sale credited, credit the asset.
     *
     * Idempotent per (reference_type, reference_id) unless $idempotencyKey is
     * supplied. Pass a key that includes the refund's own id so that several
     * partial refunds against one booking each post their own reversal instead
     * of the first one swallowing the rest.
     *
     * $vat and $serviceCharge must be the same components the sale was posted
     * with, or the reversal will not land back in the accounts it came out of.
     * $revenueCode and $assetCode must likewise be the accounts the sale used:
     * crediting the right revenue into the wrong bank is just as wrong as
     * crediting the wrong revenue, and it is harder to spot because both sides
     * still balance.
     */
    public function recordRefund(
        string $module,
        float $amount,
        string $paymentMethod,
        string $referenceType,
        int $referenceId,
        ?string $date = null,
        ?string $description = null,
        ?string $idempotencyKey = null,
        ?float $vat = null,
        ?float $serviceCharge = null,
        ?string $revenueCode = null,
        ?string $assetCode = null
    ): ?JournalEntry {
        if ($amount <= 0) {
            return null;
        }

        // A sale's credits are the refund's debits. saleLines() returns the
        // credit side, so flipping it here keeps the rounding identical in both
        // directions instead of recomputing it and drifting by a cent.
        $lines = array_map(
            fn (array $line): array => [
                'account_code' => $line['account_code'],
                'debit' => $line['credit'] ?? 0,
                'credit' => $line['debit'] ?? 0,
            ],
            $this->saleLines(
                $module,
                $amount,
                $assetCode ?: $this->assetForMethod($paymentMethod),
                $vat ?? 0.0,
                $serviceCharge ?? 0.0,
                $revenueCode,
            )
        );

        return $this->postIdempotent(
            $idempotencyKey ?? "REF-$referenceType-$referenceId",
            $lines,
            [
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'date' => $date,
                'description' => $description ?? "Refund: $module #$referenceId",
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
     *
     * Idempotent per (reference_type, reference_id) unless $idempotencyKey is
     * supplied. Callers that can raise the same reference more than once (a
     * purchase order received in several deliveries) MUST pass a key that
     * includes the per-occurrence identifier, otherwise every occurrence after
     * the first is silently dropped.
     */
    public function recordApLiability(
        float $amount,
        string $expenseCode,
        string $referenceType,
        int $referenceId,
        ?string $date = null,
        ?string $description = null,
        ?string $idempotencyKey = null
    ): ?JournalEntry {
        if ($amount <= 0) {
            return null;
        }

        $apCode = config('finance.accounts.accounts_payable', '2000');

        return $this->postIdempotent(
            $idempotencyKey ?? "AP-$referenceType-$referenceId",
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
     * The receivable control account that every module's AR falls into.
     */
    public function receivableCode(): string
    {
        return config('finance.accounts.accounts_receivable', '1200');
    }

    /**
     * The revenue account a module's sales are credited to.
     */
    public function revenueCodeFor(string $module): string
    {
        return config("finance.accounts.revenue.$module", '4900');
    }

    /**
     * Raises a charge on credit: debit AR, credit revenue.
     *
     * This is the single entry point for anything billed before it is paid,
     * which today means a city ledger account carrying a guest's folio or a
     * group's booking. It posts no cash side: the money is still owed.
     */
    public function recordReceivableCharge(
        float $amount,
        string $revenueCode,
        string $referenceType,
        int $referenceId,
        ?string $date = null,
        ?string $description = null,
        ?string $idempotencyKey = null
    ): ?JournalEntry {
        if ($amount <= 0) {
            return null;
        }

        return $this->postIdempotent(
            $idempotencyKey ?? "ARC-$referenceType-$referenceId",
            [
                ['account_code' => $this->receivableCode(), 'debit' => $amount],
                ['account_code' => $revenueCode, 'credit' => $amount],
            ],
            [
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'date' => $date,
                'description' => $description ?? "On account: $referenceType #$referenceId",
            ]
        );
    }

    /**
     * Records money received against an outstanding charge: debit the asset it
     * arrived in, credit AR.
     */
    public function recordReceivablePayment(
        float $amount,
        string $paymentMethod,
        string $referenceType,
        int $referenceId,
        ?string $date = null,
        ?string $description = null,
        ?string $idempotencyKey = null
    ): ?JournalEntry {
        if ($amount <= 0) {
            return null;
        }

        return $this->postIdempotent(
            $idempotencyKey ?? "ARP-$referenceType-$referenceId",
            [
                ['account_code' => $this->assetForMethod($paymentMethod), 'debit' => $amount],
                ['account_code' => $this->receivableCode(), 'credit' => $amount],
            ],
            [
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'date' => $date,
                'description' => $description ?? "On account receipt: $referenceType #$referenceId",
            ]
        );
    }

    /**
     * Cancels a charge previously raised on credit: debit revenue, credit AR.
     */
    public function recordReceivableCreditNote(
        float $amount,
        string $revenueCode,
        string $referenceType,
        int $referenceId,
        ?string $date = null,
        ?string $description = null,
        ?string $idempotencyKey = null
    ): ?JournalEntry {
        if ($amount <= 0) {
            return null;
        }

        return $this->postIdempotent(
            $idempotencyKey ?? "ACN-$referenceType-$referenceId",
            [
                ['account_code' => $revenueCode, 'debit' => $amount],
                ['account_code' => $this->receivableCode(), 'credit' => $amount],
            ],
            [
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'date' => $date,
                'description' => $description ?? "Credit note: $referenceType #$referenceId",
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
