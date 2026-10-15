<?php

namespace Modules\Frontdeskcrm\Services;

use Illuminate\Support\Facades\DB;
use InvalidArgumentException;
use Modules\Finance\Services\PostingService;
use Modules\Frontdeskcrm\Models\CityLedgerAccount;
use Modules\Frontdeskcrm\Models\CityLedgerTransaction;
use Modules\Frontdeskcrm\Models\Invoice;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationPayment;

/**
 * Corporate accounts that owe the hotel money.
 *
 * A city ledger account can carry both kinds of folio in the hotel: an
 * individual guest's registration, and a group's booking invoiced to a company.
 * Both are *on account*, so none of them move cash at charge time - they debit
 * AR and credit revenue, and the account's own balance tracks what is owed.
 *
 * Every journal posting goes through the central `PostingService` rather than
 * building entries here, so idempotency, account mapping and reversal stay
 * defined in exactly one place no matter which module raised the charge.
 */
class CityLedgerService
{
    public function __construct(
        protected PostingService $posting,
        protected FolioService $folioService,
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
     * Find or create the city ledger account a stay should be billed to.
     *
     * The desk already holds the guest's or company's details on the stay, so
     * the panel offers to lift them straight onto a new account instead of
     * making someone retype them. A name (and email, when we have one) that
     * already exists on an account is reused rather than duplicated, so a
     * returning corporate guest keeps a single running balance.
     *
     * @param  array<string, mixed>  $data
     * @return array{account: CityLedgerAccount, created: bool}
     */
    public function findOrCreateFromRegistration(Registration $registration, array $data, ?int $userId = null): array
    {
        $name = trim((string) ($data['name'] ?? '')) ?: $this->defaultAccountNameFor($registration);
        $email = filled($data['email'] ?? null) ? trim((string) $data['email']) : null;

        $existing = CityLedgerAccount::query()
            ->where('name', $name)
            ->when($email, fn ($query) => $query->where('email', $email))
            ->first();

        if ($existing) {
            return ['account' => $existing, 'created' => false];
        }

        $data['name'] = $name;
        $data['email'] = $email;

        return ['account' => $this->createAccount($data, $userId), 'created' => true];
    }

    /**
     * The company trading under the stay's name, falling back to the guest.
     */
    protected function defaultAccountNameFor(Registration $registration): string
    {
        $company = trim((string) $registration->company_name);

        if ($company !== '') {
            return $company;
        }

        $name = trim(($registration->title ?? '').' '.($registration->full_name ?? ''));

        return $name !== '' ? $name : 'Guest #'.$registration->id;
    }

    /**
     * Post a charge to a city ledger account.
     * Dr Accounts Receivable, Cr Revenue.
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
        $this->guardPositiveAmount($amount);
        $date = $date ?? now()->toDateString();

        return DB::transaction(function () use ($account, $amount, $description, $reference, $registrationId, $invoiceId, $userId, $date) {
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

            $this->posting->recordReceivableCharge(
                amount: $amount,
                revenueCode: $this->posting->revenueCodeFor('frontdesk'),
                referenceType: 'city_ledger_transaction',
                referenceId: $transaction->id,
                date: $date,
                description: $description,
                idempotencyKey: 'CLCHARGE-'.$transaction->id,
                party: $account,
            );

            return $transaction;
        });
    }

    /**
     * Bill an individual guest's stay to their company's city ledger account.
     */
    public function chargeRegistration(
        CityLedgerAccount $account,
        Registration $registration,
        float $amount,
        ?string $description = null,
        ?int $userId = null,
        ?string $date = null
    ): CityLedgerTransaction {
        return $this->postCharge(
            account: $account,
            amount: $amount,
            description: $description ?: 'Guest folio: '.$this->registrationLabel($registration),
            reference: 'REG-'.$registration->id,
            registrationId: $registration->id,
            userId: $userId,
            date: $date,
        );
    }

    /**
     * Move a guest's unpaid folio balance onto a city ledger account.
     *
     * The stay does not settle for cash now. The hotel books an accounts
     * receivable charge on the account for exactly the folio's outstanding, and
     * offsets the guest's folio with a matching zero-cash payment, so the room
     * charges stay itemised on the folio (which now reads a zero balance) while
     * the named account owes the transferred amount. Repeating the call is
     * refused so the balance can never be posted twice.
     */
    public function postOutstandingToLedger(
        CityLedgerAccount $account,
        Registration $registration,
        ?int $userId = null,
        ?string $date = null
    ): CityLedgerTransaction {
        $outstanding = $this->folioService->getOutstandingBalance($registration);

        if ($registration->city_ledger_transferred_at !== null) {
            throw new InvalidArgumentException('This stay has already been transferred to a city ledger account.');
        }

        if ($outstanding <= 0) {
            throw new InvalidArgumentException('There is no outstanding balance to transfer.');
        }

        $date = $date ?? now()->toDateString();

        return DB::transaction(function () use ($account, $registration, $outstanding, $userId, $date) {
            $transaction = $this->postCharge(
                account: $account,
                amount: $outstanding,
                description: 'Guest folio transfer: '.$this->registrationLabel($registration),
                reference: 'REG-'.$registration->id,
                registrationId: $registration->id,
                userId: $userId,
                date: $date,
            );

            RegistrationPayment::create([
                'registration_id' => $registration->id,
                'amount' => $outstanding,
                'payment_method' => 'city_ledger',
                'reference' => 'CL-'.$account->code.'-'.$transaction->id,
                'notes' => 'Folio balance moved to city ledger account '.$account->name,
                'received_by' => $userId,
                'payment_date' => $date,
            ]);

            $registration->update([
                'city_ledger_account_id' => $account->id,
                'city_ledger_transferred_at' => now(),
            ]);

            return $transaction;
        });
    }

    /**
     * Bill a group's booking to their company's city ledger account.
     */
    public function chargeGroupBooking(
        CityLedgerAccount $account,
        Invoice $invoice,
        float $amount,
        ?string $description = null,
        ?int $userId = null,
        ?string $date = null
    ): CityLedgerTransaction {
        return $this->postCharge(
            account: $account,
            amount: $amount,
            description: $description ?: 'Group booking: '.($invoice->invoice_number ?: $invoice->id),
            reference: 'INV-'.$invoice->id,
            invoiceId: $invoice->id,
            userId: $userId,
            date: $date,
        );
    }

    /**
     * Bill a whole booking group (a block of rooms under one group reference) to
     * a company's city ledger account in a single on-account charge.
     *
     * Hotels normally invoice the company once for the block rather than per
     * room, so the group reference identifies the charge on the account.
     */
    public function chargeBookingGroup(
        CityLedgerAccount $account,
        string $bookingGroupId,
        float $amount,
        ?string $description = null,
        ?int $userId = null,
        ?string $date = null
    ): CityLedgerTransaction {
        return $this->postCharge(
            account: $account,
            amount: $amount,
            description: $description ?: "Booking group $bookingGroupId",
            reference: 'GRP-'.$bookingGroupId,
            userId: $userId,
            date: $date,
        );
    }

    /**
     * Record a payment from the corporate account.
     * Dr Cash/Bank (by method), Cr Accounts Receivable.
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
        $this->guardPositiveAmount($amount);
        $date = $date ?? now()->toDateString();

        return DB::transaction(function () use ($account, $amount, $paymentMethod, $description, $reference, $userId, $date) {
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

            $this->posting->recordReceivablePayment(
                amount: $amount,
                paymentMethod: $paymentMethod,
                referenceType: 'city_ledger_payment',
                referenceId: $transaction->id,
                date: $date,
                description: $description,
                idempotencyKey: 'CLPAY-'.$transaction->id,
                party: $account,
            );

            return $transaction;
        });
    }

    /**
     * Create a credit note / adjustment for a city ledger account.
     * Dr Revenue / Cr Accounts Receivable - reverses the AR.
     */
    public function createCreditNote(
        CityLedgerAccount $account,
        float $amount,
        string $description,
        ?string $reference = null,
        ?int $userId = null,
        ?string $date = null
    ): CityLedgerTransaction {
        $this->guardPositiveAmount($amount);
        $date = $date ?? now()->toDateString();

        return DB::transaction(function () use ($account, $amount, $description, $reference, $userId, $date) {
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

            $this->posting->recordReceivableCreditNote(
                amount: $amount,
                revenueCode: $this->posting->revenueCodeFor('frontdesk'),
                referenceType: 'city_ledger_credit_note',
                referenceId: $transaction->id,
                date: $date,
                description: $description,
                idempotencyKey: 'CLCN-'.$transaction->id,
                party: $account,
            );

            return $transaction;
        });
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

    /**
     * A charge, payment or credit note is always a positive amount.
     *
     * The posting service quietly ignores a non-positive amount, so without this
     * the account's balance would move while the ledger entry silently did not.
     * Failing loudly keeps the two in step.
     */
    protected function guardPositiveAmount(float $amount): void
    {
        if ($amount <= 0) {
            throw new InvalidArgumentException('A city ledger amount must be greater than zero.');
        }
    }

    /**
     * A readable label for a guest on a charge description, including the group
     * when the guest belongs to one.
     */
    protected function registrationLabel(Registration $registration): string
    {
        $name = trim(($registration->title ?? '').' '.($registration->full_name ?? ''));

        if ($name === '') {
            $name = 'Guest #'.$registration->id;
        }

        return $registration->booking_group_id
            ? "$name (group {$registration->booking_group_id})"
            : $name;
    }
}
