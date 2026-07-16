<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Finance\Services\PostingService;
use Modules\Inventory\Exports\PurchaseOrdersExport;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\PurchaseOrder;
use Modules\Inventory\Models\PurchaseOrderItem;
use Modules\Inventory\Models\RestockLog;
use Modules\Inventory\Models\StockMovement;
use Modules\Inventory\Models\Store;
use Modules\Inventory\Models\StoreItem;
use Modules\Inventory\Models\Supplier;

class PurchaseOrderController extends Controller
{
    public function index()
    {
        $orders = PurchaseOrder::with('supplier', 'store', 'createdBy', 'items')->latest()->get();

        return view('inventory::purchase_orders.index', compact('orders'));
    }

    public function create()
    {
        $suppliers = Supplier::all();
        $stores = Store::all();
        $items = Item::with('supplier')->get();
        $lastOrder = PurchaseOrder::latest()->first();
        $nextNumber = $lastOrder ? intval(substr($lastOrder->po_number, -4)) + 1 : 1;
        $poNumber = 'PO-'.now()->format('Ymd').'-'.str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        return view('inventory::purchase_orders.create', compact('suppliers', 'stores', 'items', 'poNumber'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'po_number' => 'required|string|unique:purchase_orders,po_number',
            'supplier_id' => 'required|exists:suppliers,id',
            'store_id' => 'required|exists:stores,id',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.item_id' => 'required|exists:items,id',
            'items.*.quantity_ordered' => 'required|integer|min:1',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $order = PurchaseOrder::create([
                'po_number' => $validated['po_number'],
                'supplier_id' => $validated['supplier_id'],
                'store_id' => $validated['store_id'],
                'status' => 'draft',
                'notes' => $validated['notes'],
                'created_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $lineItem) {
                PurchaseOrderItem::create([
                    'purchase_order_id' => $order->id,
                    'item_id' => $lineItem['item_id'],
                    'quantity_ordered' => $lineItem['quantity_ordered'],
                    'quantity_received' => 0,
                    'unit_price' => $lineItem['unit_price'],
                ]);
            }

            DB::commit();

            return redirect()->route('inventory.purchase-orders.show', $order)
                ->with('success', 'Purchase order created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating purchase order: '.$e->getMessage());

            return back()->with('error', 'Error creating purchase order.');
        }
    }

    public function show(PurchaseOrder $purchaseOrder)
    {
        $order = $purchaseOrder->load('supplier', 'store', 'createdBy', 'approvedBy', 'items.item');

        return view('inventory::purchase_orders.show', compact('order'));
    }

    public function approve(PurchaseOrder $purchaseOrder)
    {
        if (! in_array($purchaseOrder->status, ['draft', 'pending_approval'])) {
            return back()->with('error', 'Only draft or pending approval orders can be approved.');
        }

        $purchaseOrder->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Purchase order approved.');
    }

    public function cancel(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (in_array($purchaseOrder->status, ['received', 'cancelled'])) {
            return back()->with('error', 'Order cannot be cancelled.');
        }

        $validated = $request->validate([
            'cancellation_reason' => 'nullable|string|max:500',
        ]);

        $purchaseOrder->update([
            'status' => 'cancelled',
            'cancellation_reason' => $validated['cancellation_reason'],
            'cancelled_by' => auth()->id(),
            'cancelled_at' => now(),
        ]);

        return back()->with('success', 'Purchase order cancelled.');
    }

    public function receive(Request $request, PurchaseOrder $purchaseOrder)
    {
        if (! in_array($purchaseOrder->status, ['approved', 'partially_received'])) {
            return back()->with('error', 'Order must be approved before receiving.');
        }

        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id' => 'required|exists:purchase_order_items,id',
            'items.*.quantity_received' => 'required|integer|min:0',
        ]);

        DB::beginTransaction();
        try {
            $allFullyReceived = true;
            $receivedValue = 0;

            foreach ($validated['items'] as $line) {
                $poItem = PurchaseOrderItem::findOrFail($line['id']);
                $newReceived = min($line['quantity_received'], $poItem->quantity_ordered - $poItem->quantity_received);
                if ($newReceived <= 0) {
                    continue;
                }

                $poItem->increment('quantity_received', $newReceived);

                $totalCost = $newReceived * $poItem->unit_price;
                $receivedValue += $totalCost;
                $storeItem = StoreItem::firstOrNew([
                    'store_id' => $purchaseOrder->store_id,
                    'item_id' => $poItem->item_id,
                    'lot_number' => 'PO-'.$purchaseOrder->po_number,
                ]);
                $storeItem->quantity += $newReceived;
                $storeItem->total_cost += $totalCost;
                $storeItem->save();

                RestockLog::create([
                    'item_id' => $poItem->item_id,
                    'store_id' => $purchaseOrder->store_id,
                    'quantity' => $newReceived,
                    'total_cost' => $totalCost,
                    'lot_number' => 'PO-'.$purchaseOrder->po_number,
                    'restocked_by_id' => auth()->id(),
                    'restocked_by' => auth()->user()->name,
                ]);

                StockMovement::log([
                    'item_id' => $poItem->item_id,
                    'store_id' => $purchaseOrder->store_id,
                    'type' => 'restock',
                    'quantity_delta' => $newReceived,
                    'cost_delta' => $totalCost,
                    'reference_type' => PurchaseOrder::class,
                    'reference_id' => $purchaseOrder->id,
                    'notes' => 'PO #'.$purchaseOrder->po_number,
                ]);

                if ($poItem->quantity_received < $poItem->quantity_ordered) {
                    $allFullyReceived = false;
                }
            }

            $purchaseOrder->update([
                'status' => $allFullyReceived ? 'received' : 'partially_received',
            ]);

            try {
                $expenseCode = config('finance.accounts.expense.inventory', '5000');
                app(PostingService::class)
                    ->recordApLiability((float) $receivedValue, $expenseCode, 'purchase_order', $purchaseOrder->id);
            } catch (\Throwable $e) {
                report($e);
            }

            DB::commit();

            return redirect()->route('inventory.purchase-orders.show', $purchaseOrder)
                ->with('success', 'Stock received successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error receiving purchase order: '.$e->getMessage());

            return back()->with('error', 'Error receiving stock.');
        }
    }

    public function export(Request $request)
    {
        $status = $request->query('status');
        $supplierId = $request->query('supplier_id');

        $filename = 'purchase-orders';
        if ($status) {
            $filename .= '-'.$status;
        }
        $filename .= '-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(
            new PurchaseOrdersExport($status, $supplierId ? (int) $supplierId : null),
            $filename
        );
    }

    public function downloadPdf(PurchaseOrder $purchaseOrder)
    {
        $order = $purchaseOrder->load('supplier', 'store', 'createdBy', 'items.item');

        $pdf = Pdf::loadView('inventory::purchase_orders.pdf', compact('order'));

        return $pdf->download('PO-'.$order->po_number.'.pdf');
    }
}
