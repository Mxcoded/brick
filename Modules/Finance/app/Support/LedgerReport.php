<?php

namespace Modules\Finance\Support;

use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Models\JournalLine;
use Modules\Frontdeskcrm\Models\CityLedgerAccount;
use Modules\Frontdeskcrm\Models\CityLedgerTransaction;
use Modules\Frontdeskcrm\Models\Invoice;

/**
 * Read-only aggregations over the chart of accounts for the financial reports.
 *
 * Mirrors the logic that previously lived in FinanceController::loadAccounts()
 * and its report actions. Only posted journal entries are counted, and each
 * account is decorated with debit_total / credit_total for the given period.
 */
class LedgerReport
{
    /**
     * Loads every account with its posted journal-line totals for the period,
     * decorating each model with `debit_total` and `credit_total`.
     *
     * @return Collection<int, ChartOfAccount>
     */
    public static function accounts(?string $from = null, ?string $to = null): Collection
    {
        return ChartOfAccount::with(['lines' => function ($query) use ($from, $to) {
            $query->whereHas('journalEntry', function ($entry) use ($from, $to) {
                $entry->where('status', 'posted');

                if ($from) {
                    $entry->whereDate('date', '>=', $from);
                }

                if ($to) {
                    $entry->whereDate('date', '<=', $to);
                }
            });
        }])->orderBy('code')->get()->each(function (ChartOfAccount $account): void {
            $account->debit_total = (float) $account->lines->sum('debit');
            $account->credit_total = (float) $account->lines->sum('credit');
        });
    }

    /**
     * Trial-balance payload: accounts with a non-zero movement plus totals.
     *
     * @return array{accounts: Collection, totalDebit: float, totalCredit: float, balanced: bool}
     */
    public static function trialBalance(?string $from = null, ?string $to = null): array
    {
        $accounts = static::accounts($from, $to);

        $totalDebit = (float) $accounts->sum('debit_total');
        $totalCredit = (float) $accounts->sum('credit_total');

        $rows = $accounts
            ->filter(fn (ChartOfAccount $account): bool => $account->debit_total != 0 || $account->credit_total != 0)
            ->values();

        return [
            'accounts' => $rows,
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
            'balanced' => abs($totalDebit - $totalCredit) < 0.005,
        ];
    }

    /**
     * Profit & loss payload grouped by income and expense accounts.
     *
     * @return array{income: Collection, expense: Collection, totalIncome: float, totalExpense: float, netIncome: float}
     */
    public static function profitLoss(?string $from = null, ?string $to = null): array
    {
        $accounts = static::accounts($from, $to);

        $income = $accounts
            ->where('type', 'income')
            ->map(fn (ChartOfAccount $a) => tap($a, fn () => $a->amount = $a->credit_total - $a->debit_total))
            ->filter(fn (ChartOfAccount $a): bool => $a->amount != 0)
            ->values();

        $expense = $accounts
            ->where('type', 'expense')
            ->map(fn (ChartOfAccount $a) => tap($a, fn () => $a->amount = $a->debit_total - $a->credit_total))
            ->filter(fn (ChartOfAccount $a): bool => $a->amount != 0)
            ->values();

        $totalIncome = (float) $income->sum('amount');
        $totalExpense = (float) $expense->sum('amount');

        return [
            'income' => $income,
            'expense' => $expense,
            'totalIncome' => $totalIncome,
            'totalExpense' => $totalExpense,
            'netIncome' => $totalIncome - $totalExpense,
        ];
    }

    /**
     * Balance-sheet payload: assets, liabilities, equity and retained earnings.
     *
     * @return array<string, mixed>
     */
    public static function balanceSheet(?string $from = null, ?string $to = null): array
    {
        $accounts = static::accounts($from, $to);

        $assets = $accounts
            ->where('type', 'asset')
            ->map(fn (ChartOfAccount $a) => tap($a, fn () => $a->amount = $a->debit_total - $a->credit_total))
            ->filter(fn (ChartOfAccount $a): bool => $a->amount != 0)
            ->values();

        $liabilities = $accounts
            ->where('type', 'liability')
            ->map(fn (ChartOfAccount $a) => tap($a, fn () => $a->amount = $a->credit_total - $a->debit_total))
            ->filter(fn (ChartOfAccount $a): bool => $a->amount != 0)
            ->values();

        $equity = $accounts
            ->where('type', 'equity')
            ->map(fn (ChartOfAccount $a) => tap($a, fn () => $a->amount = $a->credit_total - $a->debit_total))
            ->filter(fn (ChartOfAccount $a): bool => $a->amount != 0)
            ->values();

        $totalAssets = (float) $assets->sum('amount');
        $totalLiabilities = (float) $liabilities->sum('amount');
        $totalEquity = (float) $equity->sum('amount');

        $netIncome = (float) $accounts->where('type', 'income')->sum(fn ($a) => $a->credit_total - $a->debit_total)
            - (float) $accounts->where('type', 'expense')->sum(fn ($a) => $a->debit_total - $a->credit_total);

        $totalEquityAndLiabilities = $totalLiabilities + $totalEquity + $netIncome;

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'netIncome' => $netIncome,
            'totalAssets' => $totalAssets,
            'totalLiabilities' => $totalLiabilities,
            'totalEquity' => $totalEquity,
            'totalEquityAndLiabilities' => $totalEquityAndLiabilities,
            'balanced' => abs($totalAssets - $totalEquityAndLiabilities) < 0.005,
        ];
    }

    /**
     * General-ledger detail: every posted line for one account, with a running
     * balance, ordered by date and entry number.
     *
     * Passing null for the account returns every account that moved, which is
     * what the "all accounts" export uses.
     *
     * @return array{account: ChartOfAccount|null, rows: Collection, openingBalance: float, closingBalance: float, totalDebit: float, totalCredit: float}
     */
    public static function generalLedger(?string $code, ?string $from = null, ?string $to = null): array
    {
        $account = $code ? ChartOfAccount::where('code', $code)->first() : null;

        $query = JournalLine::query()
            ->with(['account', 'journalEntry'])
            ->whereHas('journalEntry', function ($entry) use ($from, $to) {
                $entry->where('status', 'posted');

                if ($from) {
                    $entry->whereDate('date', '>=', $from);
                }

                if ($to) {
                    $entry->whereDate('date', '<=', $to);
                }
            })
            ->when($account, fn ($q) => $q->where('account_id', $account->id));

        $rows = collect($query->get())
            ->sortBy([
                fn ($a, $b) => $a->journalEntry->date->timestamp,
                fn ($a, $b) => $a->journalEntry->entry_number,
            ])
            ->values();

        // Balance runs in the account's own natural direction so a credit
        // balance account reads correctly instead of going negative.
        $creditNormal = $account?->normal_balance === 'credit';
        $running = 0.0;

        $rows = $rows->map(function (JournalLine $line) use (&$running, $creditNormal): JournalLine {
            $movement = $creditNormal
                ? (float) $line->credit - (float) $line->debit
                : (float) $line->debit - (float) $line->credit;

            $running += $movement;
            $line->balance = round($running, 2);

            return $line;
        });

        $totalDebit = (float) $rows->sum('debit');
        $totalCredit = (float) $rows->sum('credit');
        $closing = $creditNormal ? $totalCredit - $totalDebit : $totalDebit - $totalCredit;

        // Everything posted to this account strictly before the window starts.
        // Without this the opening balance is always zero, so a report filtered
        // to April shows a ledger that never carried a balance into April.
        $openingBalance = 0.0;

        if ($account && $from) {
            $opening = JournalLine::query()
                ->where('account_id', $account->id)
                ->whereHas('journalEntry', function ($entry) use ($from) {
                    $entry->where('status', 'posted')
                        ->whereDate('date', '<', $from);
                });

            $openingDebit = (float) $opening->clone()->sum('debit');
            $openingCredit = (float) $opening->clone()->sum('credit');

            $openingBalance = $creditNormal
                ? $openingCredit - $openingDebit
                : $openingDebit - $openingCredit;
        }

        return [
            'account' => $account,
            'rows' => $rows,
            'openingBalance' => round($openingBalance, 2),
            'closingBalance' => round($closing, 2),
            'totalDebit' => $totalDebit,
            'totalCredit' => $totalCredit,
        ];
    }

    /**
     * Revenue by operating source, for a hotel's management reporting.
     *
     * Groups posted income lines by the business area that raised the revenue
     * (the posting module - front desk, restaurant, banquet, gym, website) and
     * reports the matching expense so each department can be seen to be
     * contribution-positive rather than judged on revenue alone.
     *
     * @return array{rows: Collection, totalRevenue: float, totalExpense: float, netContribution: float}
     */
    public static function revenueBySource(?string $from = null, ?string $to = null): array
    {
        $scope = function ($query) use ($from, $to) {
            $query->where('status', 'posted');

            if ($from) {
                $query->whereDate('date', '>=', $from);
            }

            if ($to) {
                $query->whereDate('date', '<=', $to);
            }

            return $query;
        };

        $pAndL = ChartOfAccount::whereIn('type', ['income', 'expense'])->pluck('type', 'id');

        $lines = JournalLine::query()
            ->with(['journalEntry', 'account'])
            ->whereIn('account_id', $pAndL->keys())
            ->whereHas('journalEntry', $scope)
            ->get();

        $buckets = [];

        foreach ($lines as $line) {
            $source = static::sourceFor($line);

            $buckets[$source] ??= [
                'source' => $source,
                'label' => static::sourceLabel($source),
                'revenue' => 0.0,
                'expense' => 0.0,
                'entries' => [],
            ];

            if ($pAndL[$line->account_id] === 'income') {
                $buckets[$source]['revenue'] += (float) $line->credit - (float) $line->debit;
            } else {
                $buckets[$source]['expense'] += (float) $line->debit - (float) $line->credit;
            }

            $buckets[$source]['entries'][$line->journalEntry->id] = true;
        }

        $rows = collect($buckets)
            ->map(fn (array $row): array => [
                'source' => $row['source'],
                'label' => $row['label'],
                'revenue' => round($row['revenue'], 2),
                'expense' => round($row['expense'], 2),
                'net' => round($row['revenue'] - $row['expense'], 2),
                'entryCount' => count($row['entries']),
            ])
            ->sortByDesc('revenue')
            ->values();

        $totalRevenue = (float) $rows->sum('revenue');
        $totalExpense = (float) $rows->sum('expense');

        return [
            'rows' => $rows,
            'totalRevenue' => $totalRevenue,
            'totalExpense' => $totalExpense,
            'netContribution' => $totalRevenue - $totalExpense,
        ];
    }

    /**
     * Accounts receivable aging: who owes what, and how late.
     *
     * Built from the posted journal rather than from any module's own balance,
     * so the number Finance reports is the number the ledger holds. The AR
     * control account is split back out per customer, and each customer is split
     * per open document.
     *
     * Money received is applied to the oldest open charge first (FIFO), which is
     * what a hotel's credit control does and is the only way to tell which
     * document a part payment actually settled. Bucketing the raw debit total by
     * age would show a fully paid customer as 60 days overdue.
     *
     * @return array{rows: Collection, buckets: array<string, float>, totalOutstanding: float, asOf: string}
     */
    public static function receivableAging(?string $asOf = null): array
    {
        $asOf = $asOf ? Carbon::parse($asOf) : Carbon::now();
        $arCode = config('finance.accounts.accounts_receivable', '1200');

        $account = ChartOfAccount::where('code', $arCode)->first();

        $rows = new Collection;

        if (! $account) {
            return [
                'rows' => $rows,
                'buckets' => static::agingBuckets(),
                'totalOutstanding' => 0.0,
                'asOf' => $asOf->toDateString(),
            ];
        }

        $lines = JournalLine::query()
            ->with('journalEntry')
            ->where('account_id', $account->id)
            ->whereHas('journalEntry', function ($entry) use ($asOf) {
                $entry->where('status', 'posted')
                    ->whereDate('date', '<=', $asOf->toDateString());
            })
            ->orderBy('id')
            ->get();

        // Debits raise what is owed, credits settle it, grouped per customer.
        $documents = [];
        $credits = [];

        foreach ($lines as $line) {
            $entry = $line->journalEntry;
            $debit = (float) $line->debit;
            $credit = (float) $line->credit;
            $date = $entry->date;
            $customer = static::lineParty($line) ?? static::receivableCustomer($entry);

            if ($debit > 0) {
                $documents[] = [
                    'key' => $entry->entry_number,
                    'customer' => $customer,
                    'date' => $date,
                    'amount' => $debit,
                    'outstanding' => $debit,
                    'due' => static::receivableDueDate($customer, $date),
                ];

                continue;
            }

            if ($credit > 0) {
                $credits[] = [
                    'key' => $entry->entry_number,
                    'customer' => $customer,
                    'date' => $date,
                    'amount' => $credit,
                ];
            }
        }

        // Oldest open charge is settled first.
        usort($documents, fn ($a, $b) => [$a['date']->timestamp, $a['key']] <=> [$b['date']->timestamp, $b['key']]);
        usort($credits, fn ($a, $b) => [$a['date']->timestamp, $a['key']] <=> [$b['date']->timestamp, $b['key']]);

        foreach ($credits as $payment) {
            $left = $payment['amount'];

            foreach ($documents as $index => $document) {
                if ($left <= 0.005) {
                    break;
                }

                if ($document['outstanding'] <= 0.005
                    || $document['customer']['key'] !== $payment['customer']['key']
                    || $document['date']->gt($payment['date'])) {
                    continue;
                }

                $applied = min($left, $document['outstanding']);

                $documents[$index]['outstanding'] -= $applied;
                $left -= $applied;
            }
        }

        $rows = collect($documents)
            ->filter(fn (array $doc): bool => $doc['outstanding'] > 0.005)
            ->groupBy('customer.key')
            ->map(function (Collection $docs, string $key): array {
                $first = $docs->first();
                $aging = static::agingBuckets();
                $oldestDue = null;

                foreach ($docs as $doc) {
                    $daysOverdue = (int) $doc['due']->diffInDays(Carbon::now(), false);
                    $daysOverdue = $daysOverdue > 0 ? $daysOverdue : 0;

                    $aging[static::agingBucket($daysOverdue)] += $doc['outstanding'];

                    if ($oldestDue === null || $doc['due']->lt($oldestDue)) {
                        $oldestDue = $doc['due'];
                    }
                }

                return [
                    'customer' => $first['customer'],
                    'label' => $first['customer']['label'],
                    'aging' => $aging,
                    'total' => round((float) array_sum($aging), 2),
                    'documents' => $docs->count(),
                    'oldestDue' => $oldestDue,
                ];
            })
            ->sortByDesc('total')
            ->values();

        $buckets = static::agingBuckets();

        foreach ($rows as $row) {
            foreach ($row['aging'] as $name => $amount) {
                $buckets[$name] += $amount;
            }
        }

        foreach ($buckets as $name => $amount) {
            $buckets[$name] = round($amount, 2);
        }

        return [
            'rows' => $rows,
            'buckets' => $buckets,
            'totalOutstanding' => round((float) array_sum($buckets), 2),
            'asOf' => $asOf->toDateString(),
        ];
    }

    /**
     * The aging buckets, in the order they are shown.
     *
     * @return array<string, float>
     */
    protected static function agingBuckets(): array
    {
        return ['current' => 0.0, 'days_1_30' => 0.0, 'days_31_60' => 0.0, 'days_61_90' => 0.0, 'days_90_plus' => 0.0];
    }

    /**
     * Which bucket a document falls into, by how many days past due it is.
     */
    protected static function agingBucket(int $daysOverdue): string
    {
        return match (true) {
            $daysOverdue <= 0 => 'current',
            $daysOverdue <= 30 => 'days_1_30',
            $daysOverdue <= 60 => 'days_31_60',
            $daysOverdue <= 90 => 'days_61_90',
            default => 'days_90_plus',
        };
    }

    /**
     * The customer a line's own sub-ledger points at, when the line carries one.
     *
     * This is the reliable answer: it is written on the line at posting time, so
     * it needs no second lookup and survives the referenced charge being edited.
     * A city ledger party is re-keyed to the "city_ledger:" scheme so it groups
     * together with any reference-derived rows and keeps the account's payment
     * terms in receivableDueDate().
     *
     * @return array{key: string, label: string}|null
     */
    protected static function lineParty(JournalLine $line): ?array
    {
        if (! $line->party_type || ! $line->party_id) {
            return null;
        }

        $label = SubLedger::label($line->party_type, $line->party_id);

        if (! $label) {
            return null;
        }

        $key = $line->party_type === CityLedgerAccount::class
            ? 'city_ledger:'.$line->party_id
            : $line->party_type.':'.$line->party_id;

        return ['key' => $key, 'label' => $label];
    }

    /**
     * The customer an AR entry belongs to, resolved from the entry's reference.
     *
     * Finance does not depend on the front desk module: the front desk's models
     * are only touched when they are actually loaded, so this still works with
     * that module disabled. An entry whose reference cannot be resolved is
     * reported under its own reference rather than being dropped.
     *
     * @return array{key: string, label: string}
     */
    protected static function receivableCustomer($entry): array
    {
        $type = (string) ($entry->reference_type ?? '');
        $id = $entry->reference_id;

        $fallback = ['key' => $type.':'.$id, 'label' => Str::of($type)->headline()->toString()];

        if (! $id || $type === '') {
            return $fallback;
        }

        if (str_starts_with($type, 'city_ledger')
            && class_exists(CityLedgerTransaction::class)) {
            $transaction = CityLedgerTransaction::with('account')->find($id);
            $account = $transaction?->account;

            if ($account) {
                return ['key' => 'city_ledger:'.$account->id, 'label' => $account->name];
            }
        }

        if (str_contains($type, 'invoice') && class_exists(Invoice::class)) {
            $invoice = Invoice::with('cityLedgerAccount')->find($id);

            if ($invoice?->cityLedgerAccount) {
                return [
                    'key' => 'city_ledger:'.$invoice->cityLedgerAccount->id,
                    'label' => $invoice->cityLedgerAccount->name,
                ];
            }

            if ($invoice) {
                return ['key' => 'invoice:'.$invoice->id, 'label' => $invoice->invoice_number];
            }
        }

        return $fallback;
    }

    /**
     * When a document falls due.
     *
     * An invoice carries its own due date. A city ledger charge is due after the
     * account's agreed terms (net15/net30/net45/net60) from the charge date, and
     * anything else is treated as due immediately.
     */
    protected static function receivableDueDate(array $customer, $date): Carbon
    {
        if (str_starts_with($customer['key'], 'city_ledger:')
            && class_exists(CityLedgerAccount::class)) {
            $accountId = (int) Str::after($customer['key'], 'city_ledger:');
            $terms = CityLedgerAccount::whereKey($accountId)->value('payment_terms');

            $days = (int) Str::of((string) $terms)->after('net')->value(0);

            return $date->copy()->addDays($days);
        }

        return $date->copy();
    }

    /**
     * Which operating area a posting line belongs to.
     *
     * The posting service maps each module to an account code in
     * `finance.accounts`, so the code is the reliable signal - but several
     * modules can deliberately share one account (the website and the front
     * desk both post room revenue to 4000). When that happens the GL line is
     * named rather than the writer, because a hotel genuinely cannot separate
     * direct and walk-in revenue until they are posted to different accounts.
     */
    protected static function sourceFor(JournalLine $line): string
    {
        $accountCode = (string) ($line->account?->code ?? '');
        $modules = static::accountToModuleMap()[$accountCode] ?? [];

        if (count($modules) === 1) {
            return Str::of($modules[0])->headline()->toString();
        }

        return $line->account?->name ?: ($modules ? implode(' / ', $modules) : 'Unallocated');
    }

    /**
     * The posting modules that write to a given account code.
     *
     * @return array<string, array<int, string>>
     */
    protected static function accountToModuleMap(): array
    {
        $map = [];

        foreach (['revenue', 'expense'] as $kind) {
            foreach ((array) config('finance.accounts.'.$kind, []) as $module => $code) {
                if (is_string($code) && $code !== '') {
                    $map[$code][] = (string) $module;
                }
            }
        }

        return $map;
    }

    protected static function sourceLabel(string $source): string
    {
        return $source === '' ? 'Unallocated' : $source;
    }
}
