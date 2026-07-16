<?php

namespace Modules\Finance\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Modules\Finance\Models\ChartOfAccount;
use Modules\Finance\Models\JournalEntry;

class FinanceController extends Controller
{
    public function index()
    {
        $accountsCount = ChartOfAccount::count();
        $postedCount = JournalEntry::where('status', 'posted')->count();
        $entries = JournalEntry::with('lines.account')->latest()->paginate(10);

        return view('finance::index', compact('accountsCount', 'postedCount', 'entries'));
    }

    // ─── Chart of Accounts ─────────────────────────────────────────────

    public function coaIndex()
    {
        $accounts = ChartOfAccount::orderBy('code')->paginate(50);

        return view('finance::coa.index', compact('accounts'));
    }

    public function coaCreate()
    {
        $accounts = ChartOfAccount::orderBy('code')->get();

        return view('finance::coa.create', compact('accounts'));
    }

    public function coaStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:finance_chart_of_accounts,code'],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:asset,liability,equity,income,expense'],
            'normal_balance' => ['required', 'in:debit,credit'],
            'parent_id' => ['nullable', 'exists:finance_chart_of_accounts,id'],
            'is_contra' => ['boolean'],
            'active' => ['boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['is_contra'] = $request->boolean('is_contra');
        $validated['active'] = $request->boolean('active', true);

        ChartOfAccount::create($validated);

        return redirect()->route('finance.coa.index')
            ->with('success', 'Account created successfully.');
    }

    public function coaEdit(ChartOfAccount $chartOfAccount)
    {
        $accounts = ChartOfAccount::where('id', '!=', $chartOfAccount->id)->orderBy('code')->get();

        return view('finance::coa.edit', compact('chartOfAccount', 'accounts'));
    }

    public function coaUpdate(Request $request, ChartOfAccount $chartOfAccount): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'unique:finance_chart_of_accounts,code,'.$chartOfAccount->id],
            'name' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:asset,liability,equity,income,expense'],
            'normal_balance' => ['required', 'in:debit,credit'],
            'parent_id' => ['nullable', 'exists:finance_chart_of_accounts,id'],
            'is_contra' => ['boolean'],
            'active' => ['boolean'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['is_contra'] = $request->boolean('is_contra');
        $validated['active'] = $request->boolean('active', true);

        $chartOfAccount->update($validated);

        return redirect()->route('finance.coa.index')
            ->with('success', 'Account updated successfully.');
    }

    public function coaDestroy(ChartOfAccount $chartOfAccount): RedirectResponse
    {
        if ($chartOfAccount->children()->exists() || $chartOfAccount->lines()->exists()) {
            return redirect()->route('finance.coa.index')
                ->with('error', 'Cannot delete an account that has child accounts or posted transactions.');
        }

        $chartOfAccount->delete();

        return redirect()->route('finance.coa.index')
            ->with('success', 'Account deleted successfully.');
    }

    // ─── Journal Entries ───────────────────────────────────────────────

    public function journalIndex()
    {
        $entries = JournalEntry::with('lines.account')->latest()->paginate(25);

        return view('finance::journal.index', compact('entries'));
    }

    public function journalShow(JournalEntry $journalEntry)
    {
        $journalEntry->load('lines.account', 'createdBy');

        return view('finance::journal.show', compact('journalEntry'));
    }

    // ─── Reports ───────────────────────────────────────────────────────

    public function reportsIndex()
    {
        return view('finance::reports.index');
    }

    public function trialBalance(Request $request)
    {
        $accounts = $this->loadAccounts($request);

        $totalDebit = $accounts->sum('debit_total');
        $totalCredit = $accounts->sum('credit_total');

        return view('finance::reports.trial_balance', compact('accounts', 'totalDebit', 'totalCredit'));
    }

    public function profitLoss(Request $request)
    {
        $accounts = $this->loadAccounts($request);

        $totalIncome = $accounts->where('type', 'income')
            ->sum(fn ($a) => $a->credit_total - $a->debit_total);
        $totalExpense = $accounts->where('type', 'expense')
            ->sum(fn ($a) => $a->debit_total - $a->credit_total);
        $netIncome = $totalIncome - $totalExpense;

        return view('finance::reports.profit_loss', compact('accounts', 'totalIncome', 'totalExpense', 'netIncome'));
    }

    public function balanceSheet(Request $request)
    {
        $accounts = $this->loadAccounts($request);

        $totalAssets = $accounts->where('type', 'asset')
            ->sum(fn ($a) => $a->debit_total - $a->credit_total);
        $totalLiabilities = $accounts->where('type', 'liability')
            ->sum(fn ($a) => $a->credit_total - $a->debit_total);
        $totalEquity = $accounts->where('type', 'equity')
            ->sum(fn ($a) => $a->credit_total - $a->debit_total);
        $netIncome = $accounts->where('type', 'income')
            ->sum(fn ($a) => $a->credit_total - $a->debit_total)
            - $accounts->where('type', 'expense')
                ->sum(fn ($a) => $a->debit_total - $a->credit_total);

        $totalEquityAndLiabilities = $totalLiabilities + $totalEquity + $netIncome;

        return view('finance::reports.balance_sheet', compact(
            'accounts', 'totalAssets', 'totalLiabilities', 'totalEquity', 'netIncome', 'totalEquityAndLiabilities'
        ));
    }

    /**
     * Loads all accounts with their posted journal-line totals (optionally date-filtered).
     */
    protected function loadAccounts(Request $request): Collection
    {
        $from = $request->input('from');
        $to = $request->input('to');

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
        }])->orderBy('code')->get()->each(function ($account) {
            $account->debit_total = (float) $account->lines->sum('debit');
            $account->credit_total = (float) $account->lines->sum('credit');
        });
    }
}
