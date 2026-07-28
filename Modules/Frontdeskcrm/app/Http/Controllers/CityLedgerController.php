<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Frontdeskcrm\Models\CityLedgerAccount;
use Modules\Frontdeskcrm\Models\CityLedgerTransaction;
use Modules\Frontdeskcrm\Services\CityLedgerService;

class CityLedgerController extends Controller
{
    public function __construct(
        protected CityLedgerService $cityLedgerService
    ) {}

    public function index()
    {
        $accounts = CityLedgerAccount::withCount('transactions')
            ->orderBy('name')
            ->paginate(15);

        $totalOutstanding = CityLedgerAccount::where('status', 'active')->sum('balance');

        return view('frontdeskcrm::city-ledger.index', compact('accounts', 'totalOutstanding'));
    }

    public function create()
    {
        return view('frontdeskcrm::city-ledger.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string|max:100',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'required|in:net15,net30,net45,net60',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $validated['credit_limit'] = $validated['credit_limit'] ?? 0;

        $account = $this->cityLedgerService->createAccount($validated, auth()->id());

        return redirect()->route('frontdesk.city-ledger.show', $account)
            ->with('success', 'City Ledger account created successfully.');
    }

    public function show(CityLedgerAccount $cityLedgerAccount)
    {
        $account = $cityLedgerAccount;
        $account->loadCount('transactions');

        $transactions = CityLedgerTransaction::where('city_ledger_account_id', $account->id)
            ->with('createdBy')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        return view('frontdeskcrm::city-ledger.show', compact('account', 'transactions'));
    }

    public function edit(CityLedgerAccount $cityLedgerAccount)
    {
        return view('frontdeskcrm::city-ledger.edit', ['account' => $cityLedgerAccount]);
    }

    public function update(Request $request, CityLedgerAccount $cityLedgerAccount)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'tax_id' => 'nullable|string|max:100',
            'credit_limit' => 'nullable|numeric|min:0',
            'payment_terms' => 'required|in:net15,net30,net45,net60',
            'status' => 'required|in:active,inactive',
            'notes' => 'nullable|string',
        ]);

        $this->cityLedgerService->updateAccount($cityLedgerAccount, $validated);

        return redirect()->route('frontdesk.city-ledger.show', $cityLedgerAccount)
            ->with('success', 'City Ledger account updated successfully.');
    }

    public function postCharge(Request $request, CityLedgerAccount $cityLedgerAccount)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'description' => 'required|string|max:500',
            'reference' => 'nullable|string|max:255',
            'registration_id' => 'nullable|integer|exists:registrations,id',
            'invoice_id' => 'nullable|integer|exists:invoices,id',
            'transaction_date' => 'nullable|date',
        ]);

        $this->cityLedgerService->postCharge(
            $cityLedgerAccount,
            $validated['amount'],
            $validated['description'],
            $validated['reference'] ?? null,
            $validated['registration_id'] ?? null,
            $validated['invoice_id'] ?? null,
            auth()->id(),
            $validated['transaction_date'] ?? null,
        );

        return redirect()->route('frontdesk.city-ledger.show', $cityLedgerAccount)
            ->with('success', 'Charge posted to City Ledger account.');
    }

    public function recordPayment(Request $request, CityLedgerAccount $cityLedgerAccount)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'payment_method' => 'required|string',
            'description' => 'required|string|max:500',
            'reference' => 'nullable|string|max:255',
            'transaction_date' => 'nullable|date',
        ]);

        $this->cityLedgerService->recordPayment(
            $cityLedgerAccount,
            $validated['amount'],
            $validated['payment_method'],
            $validated['description'],
            $validated['reference'] ?? null,
            auth()->id(),
            $validated['transaction_date'] ?? null,
        );

        return redirect()->route('frontdesk.city-ledger.show', $cityLedgerAccount)
            ->with('success', 'Payment recorded against City Ledger account.');
    }

    public function aging()
    {
        $report = $this->cityLedgerService->getAgingReport();

        $totals = [
            'current' => collect($report)->sum(fn ($r) => $r['aging']['current']),
            '1_30' => collect($report)->sum(fn ($r) => $r['aging']['1_30']),
            '31_60' => collect($report)->sum(fn ($r) => $r['aging']['31_60']),
            '61_90' => collect($report)->sum(fn ($r) => $r['aging']['61_90']),
            '90_plus' => collect($report)->sum(fn ($r) => $r['aging']['90_plus']),
            'total' => collect($report)->sum('total_outstanding'),
        ];

        return view('frontdeskcrm::city-ledger.aging', compact('report', 'totals'));
    }
}
