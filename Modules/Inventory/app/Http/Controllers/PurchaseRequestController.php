<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Models\PurchaseRequest;
use Modules\Inventory\Models\PurchaseRequestApproval;
use Modules\Inventory\Models\PurchaseRequestItem;
use Modules\Inventory\Models\Supplier;

class PurchaseRequestController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        if ($user->isProcurementApprover()) {
            $requests = PurchaseRequest::with('requester', 'items', 'approvals.user')
                ->latest()
                ->get();
        } elseif ($user->isProcurementRequester()) {
            $requests = PurchaseRequest::with('items', 'approvals.user')
                ->where('requester_id', $user->id)
                ->latest()
                ->get();
        } else {
            $requests = collect();
        }

        return view('inventory::procurement.requests.index', compact('requests'));
    }

    public function create()
    {
        if (! auth()->user()->isProcurementRequester()) {
            return back()->with('error', 'Only line managers can create purchase requests.');
        }

        $suppliers = Supplier::all();

        return view('inventory::procurement.requests.create', compact('suppliers'));
    }

    public function store(Request $request)
    {
        if (! auth()->user()->isProcurementRequester()) {
            return back()->with('error', 'Only line managers can create purchase requests.');
        }

        $validated = $request->validate([
            'department' => 'nullable|string|max:255',
            'urgency' => 'required|in:normal,urgent,emergency',
            'justification' => 'required|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.estimated_unit_price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $prNumber = $this->generatePrNumber();

            $status = $request->boolean('submit_and_send') ? 'pending_purchaser' : 'draft';
            $currentRole = $request->boolean('submit_and_send') ? 'purchaser' : null;

            $purchaseRequest = PurchaseRequest::create([
                'pr_number' => $prNumber,
                'requester_id' => auth()->id(),
                'department' => $validated['department'],
                'urgency' => $validated['urgency'],
                'justification' => $validated['justification'],
                'status' => $status,
                'current_role' => $currentRole,
            ]);

            foreach ($validated['items'] as $item) {
                PurchaseRequestItem::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'estimated_unit_price' => $item['estimated_unit_price'] ?? null,
                    'notes' => $item['notes'] ?? null,
                ]);
            }

            if ($request->boolean('submit_and_send')) {
                PurchaseRequestApproval::create([
                    'purchase_request_id' => $purchaseRequest->id,
                    'role' => 'line_manager',
                    'action' => 'submitted',
                    'user_id' => auth()->id(),
                ]);
            }

            DB::commit();

            return redirect()->route('inventory.procurement.requests.show', $purchaseRequest)
                ->with('success', $request->boolean('submit_and_send')
                    ? 'Request submitted for purchasing review.'
                    : 'Purchase request created as draft.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating purchase request: '.$e->getMessage());

            return back()->with('error', 'Error creating purchase request.');
        }
    }

    public function show(PurchaseRequest $purchaseRequest)
    {
        $user = auth()->user();

        if ($user->isProcurementApprover()) {
            // Approvers may view any request in the chain.
        } elseif ($user->isProcurementRequester()) {
            if ($purchaseRequest->requester_id !== $user->id) {
                abort(403, 'You can only view your own purchase requests.');
            }
        } else {
            abort(403, 'You are not authorized to view this request.');
        }

        $purchaseRequest->load('requester', 'supplier', 'items', 'approvals.user');

        return view('inventory::procurement.requests.show', compact('purchaseRequest'));
    }

    public function edit(PurchaseRequest $purchaseRequest)
    {
        if (! in_array($purchaseRequest->status, ['draft', 'flagged'])) {
            return back()->with('error', 'Only draft or flagged requests can be edited.');
        }

        if ($purchaseRequest->requester_id !== auth()->id()) {
            return back()->with('error', 'You can only edit your own requests.');
        }

        $suppliers = Supplier::all();

        return view('inventory::procurement.requests.edit', compact('purchaseRequest', 'suppliers'));
    }

    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (! in_array($purchaseRequest->status, ['draft', 'flagged'])) {
            return back()->with('error', 'Only draft or flagged requests can be edited.');
        }

        if ($purchaseRequest->requester_id !== auth()->id()) {
            return back()->with('error', 'You can only edit your own requests.');
        }

        $validated = $request->validate([
            'department' => 'nullable|string|max:255',
            'urgency' => 'required|in:normal,urgent,emergency',
            'justification' => 'required|string|max:2000',
            'items' => 'required|array|min:1',
            'items.*.id' => 'nullable|exists:purchase_request_items,id',
            'items.*.item_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|numeric|min:0.01',
            'items.*.estimated_unit_price' => 'nullable|numeric|min:0',
            'items.*.notes' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $purchaseRequest->update([
                'department' => $validated['department'],
                'urgency' => $validated['urgency'],
                'justification' => $validated['justification'],
                'status' => 'draft',
                'current_role' => null,
            ]);

            $submittedIds = collect($validated['items'])->pluck('id')->filter();
            $purchaseRequest->items()->whereNotIn('id', $submittedIds)->delete();

            foreach ($validated['items'] as $itemData) {
                if (! empty($itemData['id'])) {
                    PurchaseRequestItem::where('id', $itemData['id'])
                        ->where('purchase_request_id', $purchaseRequest->id)
                        ->update([
                            'item_name' => $itemData['item_name'],
                            'quantity' => $itemData['quantity'],
                            'estimated_unit_price' => $itemData['estimated_unit_price'] ?? null,
                            'notes' => $itemData['notes'] ?? null,
                        ]);
                } else {
                    PurchaseRequestItem::create([
                        'purchase_request_id' => $purchaseRequest->id,
                        'item_name' => $itemData['item_name'],
                        'quantity' => $itemData['quantity'],
                        'estimated_unit_price' => $itemData['estimated_unit_price'] ?? null,
                        'notes' => $itemData['notes'] ?? null,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('inventory.procurement.requests.show', $purchaseRequest)
                ->with('success', 'Purchase request updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating purchase request: '.$e->getMessage());

            return back()->with('error', 'Error updating purchase request.');
        }
    }

    /**
     * Generate a unique PR number.
     */
    protected function generatePrNumber(): string
    {
        $prefix = 'PR-'.now()->format('Ymd');
        $lastPr = PurchaseRequest::where('pr_number', 'like', "{$prefix}%")
            ->orderByDesc('pr_number')
            ->first();
        $nextNum = $lastPr ? intval(substr($lastPr->pr_number, -4)) + 1 : 1;

        return $prefix.'-'.str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }
}
