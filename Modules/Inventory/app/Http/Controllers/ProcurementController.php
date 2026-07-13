<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Models\PurchaseOrder;
use Modules\Inventory\Models\PurchaseOrderItem;
use Modules\Inventory\Models\PurchaseRequest;
use Modules\Inventory\Models\PurchaseRequestApproval;
use Modules\Inventory\Models\Store;

class ProcurementController extends Controller
{
    public function dashboard()
    {
        $user = auth()->user();
        $view = 'inventory::procurement.dashboard';

        if ($user->hasRole('line_manager')) {
            return $this->lineManagerDashboard();
        }
        if ($user->hasRole('purchaser')) {
            return $this->purchaserDashboard();
        }
        if ($user->hasRole('gm')) {
            return $this->gmDashboard();
        }
        if ($user->hasRole('finance')) {
            return $this->financeDashboard();
        }
        if ($user->hasRole('auditor')) {
            return $this->auditorDashboard();
        }
        if ($user->hasRole('ggm')) {
            return $this->ggmDashboard();
        }

        return redirect()->route('inventory.procurement.requests.index')
            ->with('info', 'No procurement dashboard configured for your role.');
    }

    protected function lineManagerDashboard()
    {
        $user = auth()->id();
        $requests = PurchaseRequest::with('items', 'approvals.user')
            ->where('requester_id', $user)
            ->latest()
            ->get();

        $metrics = [
            'total' => $requests->count(),
            'pending' => $requests->whereIn('status', ['pending_purchaser', 'pending_gm', 'pending_finance', 'pending_auditor', 'pending_ggm'])->count(),
            'approved' => $requests->where('status', 'approved')->count(),
            'rejected' => $requests->where('status', 'rejected')->count(),
            'flagged' => $requests->where('status', 'flagged')->count(),
        ];

        return view('inventory::procurement.dashboard', [
            'role' => 'line_manager',
            'requests' => $requests,
            'metrics' => $metrics,
            'inbox' => $requests->whereIn('status', ['flagged']),
        ]);
    }

    protected function purchaserDashboard()
    {
        $inbox = PurchaseRequest::with('requester', 'items')
            ->where('status', 'pending_purchaser')
            ->latest()
            ->get();

        $processed = PurchaseRequest::with('requester')
            ->whereIn('status', ['pending_gm', 'rejected'])
            ->latest()
            ->get();

        $metrics = [
            'incoming' => $inbox->count(),
            'processed' => $processed->count(),
        ];

        return view('inventory::procurement.dashboard', [
            'role' => 'purchaser',
            'inbox' => $inbox,
            'processed' => $processed,
            'metrics' => $metrics,
        ]);
    }

    protected function gmDashboard()
    {
        $inbox = PurchaseRequest::with('requester', 'items', 'supplier')
            ->where('status', 'pending_gm')
            ->latest()
            ->get();

        $history = PurchaseRequest::with('requester')
            ->whereIn('status', ['pending_finance', 'rejected', 'flagged'])
            ->latest()
            ->get();

        $metrics = [
            'pending' => $inbox->count(),
        ];

        return view('inventory::procurement.dashboard', [
            'role' => 'gm',
            'inbox' => $inbox,
            'history' => $history,
            'metrics' => $metrics,
        ]);
    }

    protected function financeDashboard()
    {
        $inbox = PurchaseRequest::with('requester', 'items', 'supplier')
            ->where('status', 'pending_finance')
            ->latest()
            ->get();

        $history = PurchaseRequest::with('requester')
            ->whereIn('status', ['pending_auditor', 'rejected'])
            ->latest()
            ->get();

        $metrics = [
            'pending' => $inbox->count(),
        ];

        return view('inventory::procurement.dashboard', [
            'role' => 'finance',
            'inbox' => $inbox,
            'history' => $history,
            'metrics' => $metrics,
        ]);
    }

    protected function auditorDashboard()
    {
        $inbox = PurchaseRequest::with('requester', 'items', 'supplier')
            ->where('status', 'pending_auditor')
            ->latest()
            ->get();

        $history = PurchaseRequest::with('requester')
            ->whereIn('status', ['pending_ggm', 'rejected'])
            ->latest()
            ->get();

        $metrics = [
            'pending' => $inbox->count(),
        ];

        return view('inventory::procurement.dashboard', [
            'role' => 'auditor',
            'inbox' => $inbox,
            'history' => $history,
            'metrics' => $metrics,
        ]);
    }

    protected function ggmDashboard()
    {
        $inbox = PurchaseRequest::with('requester', 'items', 'supplier')
            ->where('status', 'pending_ggm')
            ->latest()
            ->get();

        $history = PurchaseRequest::with('requester')
            ->whereIn('status', ['approved', 'rejected'])
            ->latest()
            ->get();

        $metrics = [
            'pending' => $inbox->count(),
        ];

        return view('inventory::procurement.dashboard', [
            'role' => 'ggm',
            'inbox' => $inbox,
            'history' => $history,
            'metrics' => $metrics,
        ]);
    }

    /* ───────── Workflow Actions ───────── */

    public function submit(PurchaseRequest $purchaseRequest)
    {
        if (! auth()->user()->hasRole('line_manager')) {
            return back()->with('error', 'Only line managers can submit purchase requests.');
        }
        if ($purchaseRequest->requester_id !== auth()->id()) {
            return back()->with('error', 'You can only submit your own requests.');
        }
        if ($purchaseRequest->status !== 'draft') {
            return back()->with('error', 'Only draft requests can be submitted.');
        }

        DB::beginTransaction();
        try {
            $purchaseRequest->update([
                'status' => 'pending_purchaser',
                'current_role' => 'purchaser',
            ]);

            PurchaseRequestApproval::create([
                'purchase_request_id' => $purchaseRequest->id,
                'role' => 'line_manager',
                'action' => 'submitted',
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('inventory.procurement.requests.show', $purchaseRequest)
                ->with('success', 'Request submitted for purchasing review.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error submitting request: '.$e->getMessage());

            return back()->with('error', 'Error submitting request.');
        }
    }

    public function review(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (! auth()->user()->hasRole('purchaser')) {
            return back()->with('error', 'Only purchasers can review purchase requests.');
        }

        $validated = $request->validate([
            'supplier_id' => 'required|exists:suppliers,id',
            'gl_code' => 'nullable|string|max:255',
            'cost_center' => 'nullable|string|max:255',
            'procurement_notes' => 'nullable|string|max:2000',
            'items' => 'required|json',
        ]);

        if ($purchaseRequest->status !== 'pending_purchaser') {
            return back()->with('error', 'Request is not awaiting purchaser review.');
        }

        $itemsData = json_decode($validated['items'], true);
        if (empty($itemsData)) {
            return back()->with('error', 'At least one item must have a unit price.');
        }

        DB::beginTransaction();
        try {
            $purchaseRequest->update([
                'supplier_id' => $validated['supplier_id'],
                'gl_code' => $validated['gl_code'] ?? null,
                'cost_center' => $validated['cost_center'] ?? null,
                'procurement_notes' => $validated['procurement_notes'] ?? null,
                'status' => 'pending_gm',
                'current_role' => 'gm',
                'pricing_details' => collect($itemsData)->pluck('unit_price', 'id')->toArray(),
            ]);

            foreach ($itemsData as $itemData) {
                $price = $itemData['unit_price'] ?? 0;
                $existing = $purchaseRequest->items()->find($itemData['id']);
                if ($existing) {
                    $existing->update(['estimated_unit_price' => $price]);
                }
            }

            PurchaseRequestApproval::create([
                'purchase_request_id' => $purchaseRequest->id,
                'role' => 'purchaser',
                'action' => 'reviewed',
                'user_id' => auth()->id(),
                'notes' => $validated['procurement_notes'] ?? null,
            ]);

            DB::commit();

            return redirect()->route('inventory.procurement.requests.show', $purchaseRequest)
                ->with('success', 'Request forwarded to GM for approval.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error reviewing request: '.$e->getMessage());

            return back()->with('error', 'Error reviewing request.');
        }
    }

    public function uploadInvoice(Request $request, PurchaseRequest $purchaseRequest)
    {
        if (! auth()->user()->hasRole('purchaser')) {
            return back()->with('error', 'Only purchasers can upload invoices.');
        }

        $validated = $request->validate([
            'invoice' => 'required|file|mimes:pdf,jpg,jpeg,png|max:10240',
        ]);

        if ($purchaseRequest->status !== 'pending_purchaser') {
            return back()->with('error', 'Cannot attach invoice at this stage.');
        }

        $path = $request->file('invoice')->store('procurement/invoices', 'public');

        $purchaseRequest->update(['invoice_path' => $path]);

        PurchaseRequestApproval::create([
            'purchase_request_id' => $purchaseRequest->id,
            'role' => 'purchaser',
            'action' => 'invoice_attached',
            'user_id' => auth()->id(),
        ]);

        return back()->with('success', 'Invoice attached successfully.');
    }

    public function approve(PurchaseRequest $purchaseRequest)
    {
        $user = auth()->user();

        $allowedRoles = ['gm', 'finance', 'auditor', 'ggm'];
        if (! $user->hasAnyRole($allowedRoles)) {
            return back()->with('error', 'You are not authorized to approve purchase requests.');
        }

        $roleMap = [
            'gm' => ['pending_gm', 'pending_finance', 'gm'],
            'finance' => ['pending_finance', 'pending_auditor', 'finance'],
            'auditor' => ['pending_auditor', 'pending_ggm', 'auditor'],
            'ggm' => ['pending_ggm', 'approved', 'ggm'],
        ];

        $mapped = null;
        foreach ($roleMap as $role => [$from, $to, $logRole]) {
            if ($user->hasRole($role) && $purchaseRequest->status === $from) {
                $mapped = compact('from', 'to', 'logRole');
                break;
            }
        }

        if (! $mapped) {
            return back()->with('error', 'You are not authorized to approve this request at its current stage.');
        }

        DB::beginTransaction();
        try {
            $nextRole = in_array($mapped['to'], ['approved', 'rejected']) ? null : str_replace('pending_', '', $mapped['to']);
            $purchaseRequest->update([
                'status' => $mapped['to'],
                'current_role' => $nextRole,
            ]);

            PurchaseRequestApproval::create([
                'purchase_request_id' => $purchaseRequest->id,
                'role' => $mapped['logRole'],
                'action' => $mapped['to'] === 'approved' ? 'final_approved' : 'approved',
                'user_id' => $user->id,
            ]);

            DB::commit();

            $message = $mapped['to'] === 'approved'
                ? 'Request fully approved.'
                : 'Request approved and forwarded to next stage.';

            return redirect()->route('inventory.procurement.requests.show', $purchaseRequest)
                ->with('success', $message);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error approving request: '.$e->getMessage());

            return back()->with('error', 'Error approving request.');
        }
    }

    public function reject(Request $request, PurchaseRequest $purchaseRequest)
    {
        $validated = $request->validate([
            'notes' => 'required|string|max:2000',
        ]);

        $user = auth()->user();

        $allowedRoles = ['purchaser', 'gm', 'finance', 'auditor', 'ggm'];
        if (! $user->hasAnyRole($allowedRoles)) {
            return back()->with('error', 'You are not authorized to reject purchase requests.');
        }

        $roleStepMap = [
            'purchaser' => ['pending_purchaser', 'draft', 'line_manager'],
            'gm' => ['pending_gm', 'pending_purchaser', 'purchaser'],
            'finance' => ['pending_finance', 'pending_gm', 'gm'],
            'auditor' => ['pending_auditor', 'pending_finance', 'finance'],
            'ggm' => ['pending_ggm', 'pending_auditor', 'auditor'],
        ];

        $mapped = null;
        foreach ($roleStepMap as $role => [$from, $to, $returnToRole]) {
            if ($user->hasRole($role) && $purchaseRequest->status === $from) {
                $mapped = compact('from', 'to', 'returnToRole');
                break;
            }
        }

        if (! $mapped) {
            return back()->with('error', 'You cannot reject this request at its current stage.');
        }

        DB::beginTransaction();
        try {
            $purchaseRequest->update([
                'status' => $mapped['to'],
                'current_role' => $mapped['returnToRole'],
            ]);

            PurchaseRequestApproval::create([
                'purchase_request_id' => $purchaseRequest->id,
                'role' => $mapped['returnToRole'],
                'action' => 'rejected',
                'user_id' => $user->id,
                'notes' => $validated['notes'],
            ]);

            DB::commit();

            return redirect()->route('inventory.procurement.requests.show', $purchaseRequest)
                ->with('success', 'Request rejected and returned.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error rejecting request: '.$e->getMessage());

            return back()->with('error', 'Error rejecting request.');
        }
    }

    public function flag(Request $request, PurchaseRequest $purchaseRequest)
    {
        $validated = $request->validate([
            'notes' => 'required|string|max:2000',
        ]);

        $user = auth()->user();
        if (! $user->hasRole('purchaser') && ! $user->hasRole('gm')) {
            return back()->with('error', 'Only purchasers and GMs can flag requests.');
        }

        $expectedStatus = $user->hasRole('purchaser') ? 'pending_purchaser' : 'pending_gm';
        if ($purchaseRequest->status !== $expectedStatus) {
            return back()->with('error', 'Cannot flag this request at its current stage.');
        }

        DB::beginTransaction();
        try {
            $purchaseRequest->update([
                'status' => 'flagged',
                'current_role' => 'line_manager',
            ]);

            PurchaseRequestApproval::create([
                'purchase_request_id' => $purchaseRequest->id,
                'role' => $user->hasRole('purchaser') ? 'purchaser' : 'gm',
                'action' => 'flagged',
                'user_id' => $user->id,
                'notes' => $validated['notes'],
            ]);

            DB::commit();

            return redirect()->route('inventory.procurement.requests.show', $purchaseRequest)
                ->with('success', 'Request flagged and returned to requester.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error flagging request: '.$e->getMessage());

            return back()->with('error', 'Error flagging request.');
        }
    }

    public function convertToPo(PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'approved') {
            return back()->with('error', 'Only fully approved requests can be converted to purchase orders.');
        }

        if (! auth()->user()->hasRole('ggm')) {
            return back()->with('error', 'Only GGM can convert requests to purchase orders.');
        }

        $supplierId = $purchaseRequest->supplier_id;
        if (! $supplierId) {
            return back()->with('error', 'No supplier assigned to this request.');
        }

        $store = Store::first();
        if (! $store) {
            return back()->with('error', 'No store configured. Please create a store first.');
        }

        DB::beginTransaction();
        try {
            $poNumber = $this->generatePoNumber();

            $order = PurchaseOrder::create([
                'po_number' => $poNumber,
                'supplier_id' => $supplierId,
                'store_id' => $store->id,
                'status' => 'draft',
                'notes' => 'Converted from PR #'.$purchaseRequest->pr_number."\n".$purchaseRequest->justification,
                'created_by' => auth()->id(),
            ]);

            foreach ($purchaseRequest->items as $item) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'item_id' => null,
                    'quantity_ordered' => (int) round((float) $item->quantity),
                    'quantity_received' => 0,
                    'unit_price' => $item->estimated_unit_price ?? 0,
                ]);
            }

            $purchaseRequest->update(['status' => 'ordered']);

            PurchaseRequestApproval::create([
                'purchase_request_id' => $purchaseRequest->id,
                'role' => 'ggm',
                'action' => 'converted_to_po',
                'user_id' => auth()->id(),
            ]);

            DB::commit();

            return redirect()->route('inventory.purchase-orders.show', $order)
                ->with('success', 'Purchase request converted to PO #'.$poNumber.'.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error converting request to PO: '.$e->getMessage());

            return back()->with('error', 'Error converting to purchase order.');
        }
    }

    /**
     * Generate a unique PO number.
     */
    protected function generatePoNumber(): string
    {
        $prefix = 'PO-'.now()->format('Ymd');
        $lastOrder = PurchaseOrder::where('po_number', 'like', "{$prefix}%")
            ->orderByDesc('po_number')
            ->first();
        $nextNum = $lastOrder ? intval(substr($lastOrder->po_number, -4)) + 1 : 1;

        return $prefix.'-'.str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }
}
