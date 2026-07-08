<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Frontdeskcrm\Models\CorporateAccount;
use Modules\Frontdeskcrm\Models\Registration;

class CityLedgerController extends Controller
{
    public function index()
    {
        $accounts = CorporateAccount::withCount('transactions', 'registrations')
            ->orderBy('company_name')
            ->paginate(20);

        $summary = [
            'total_outstanding' => CorporateAccount::sum('current_balance'),
            'total_credit_limit' => CorporateAccount::sum('credit_limit'),
            'active_accounts' => CorporateAccount::where('is_active', true)->count(),
            'total_accounts' => CorporateAccount::count(),
        ];

        return view('frontdeskcrm::city-ledger.index', compact('accounts', 'summary'));
    }

    public function create()
    {
        return view('frontdeskcrm::city-ledger.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'credit_limit' => 'required|numeric|min:0',
            'payment_terms' => 'required|in:net_15,net_30,net_45,net_60,on_demand',
            'notes' => 'nullable|string|max:1000',
        ]);

        $validated['is_active'] = true;
        $validated['current_balance'] = 0;

        CorporateAccount::create($validated);

        return redirect()->route('frontdesk.city-ledger.index')
            ->with('success', 'Corporate account created successfully.');
    }

    public function show(CorporateAccount $corporateAccount)
    {
        $transactions = $corporateAccount->transactions()
            ->with('registration', 'createdBy')
            ->latest()
            ->paginate(30);

        return view('frontdeskcrm::city-ledger.show', compact('corporateAccount', 'transactions'));
    }

    public function edit(CorporateAccount $corporateAccount)
    {
        return view('frontdeskcrm::city-ledger.edit', compact('corporateAccount'));
    }

    public function update(Request $request, CorporateAccount $corporateAccount)
    {
        $validated = $request->validate([
            'company_name' => 'required|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:50',
            'address' => 'nullable|string',
            'credit_limit' => 'required|numeric|min:0',
            'payment_terms' => 'required|in:net_15,net_30,net_45,net_60,on_demand',
            'notes' => 'nullable|string|max:1000',
            'is_active' => 'boolean',
        ]);

        $corporateAccount->update($validated);

        return redirect()->route('frontdesk.city-ledger.show', $corporateAccount)
            ->with('success', 'Account updated successfully.');
    }

    public function recordPayment(Request $request, CorporateAccount $corporateAccount)
    {
        $validated = $request->validate([
            'amount' => 'required|numeric|min:0.01',
            'reference' => 'nullable|string|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        $balanceBefore = $corporateAccount->current_balance;
        $balanceAfter = max(0, $balanceBefore - $validated['amount']);

        $corporateAccount->transactions()->create([
            'type' => 'payment',
            'amount' => $validated['amount'],
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'description' => $validated['description'] ?? 'Payment received',
            'reference' => $validated['reference'] ?? null,
            'created_by' => Auth::id(),
        ]);

        $corporateAccount->decrement('current_balance', $validated['amount']);

        return back()->with('success', 'Payment of ₦'.number_format($validated['amount'], 2).' recorded.');
    }

    public function chargeRegistration(Request $request, CorporateAccount $corporateAccount)
    {
        $validated = $request->validate([
            'registration_id' => 'required|exists:registrations,id',
            'amount' => 'required|numeric|min:0.01',
            'description' => 'nullable|string|max:255',
        ]);

        $registration = Registration::findOrFail($validated['registration_id']);

        $balanceBefore = $corporateAccount->current_balance;
        $balanceAfter = $balanceBefore + $validated['amount'];

        if ($balanceAfter > $corporateAccount->credit_limit) {
            return back()->with('error', 'This charge would exceed the account credit limit of ₦'.number_format($corporateAccount->credit_limit, 2));
        }

        $corporateAccount->transactions()->create([
            'type' => 'charge',
            'registration_id' => $registration->id,
            'amount' => $validated['amount'],
            'balance_before' => $balanceBefore,
            'balance_after' => $balanceAfter,
            'description' => $validated['description'] ?? 'Charge for registration #'.$registration->id,
            'created_by' => Auth::id(),
        ]);

        $corporateAccount->increment('current_balance', $validated['amount']);

        return back()->with('success', 'Charge of ₦'.number_format($validated['amount'], 2).' billed to account.');
    }

    public function transactions(CorporateAccount $corporateAccount)
    {
        return response()->json(
            $corporateAccount->transactions()
                ->with('registration', 'createdBy')
                ->latest()
                ->paginate(50)
        );
    }
}
