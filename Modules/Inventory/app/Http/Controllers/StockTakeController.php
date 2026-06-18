<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Modules\Inventory\Models\InventoryAdjustment;
use Modules\Inventory\Models\StockMovement;
use Modules\Inventory\Models\StockTake;
use Modules\Inventory\Models\StockTakeItem;
use Modules\Inventory\Models\Store;
use Modules\Inventory\Models\StoreItem;

class StockTakeController extends Controller
{
    public function index(): View
    {
        $stockTakes = StockTake::with('store', 'taker')->latest()->get();

        return view('inventory::stock_takes.index', compact('stockTakes'));
    }

    public function create(): View
    {
        $stores = Store::orderBy('name')->get();

        return view('inventory::stock_takes.create', compact('stores'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'store_id' => 'required|exists:stores,id',
            'notes' => 'nullable|string|max:500',
        ]);

        $storeId = $validated['store_id'];

        $stockTake = StockTake::create([
            'store_id' => $storeId,
            'taken_by' => auth()->id(),
            'taken_at' => now(),
            'status' => 'in_progress',
            'notes' => $validated['notes'],
        ]);

        $storeItems = StoreItem::where('store_id', $storeId)
            ->select('item_id', DB::raw('SUM(quantity) as total_qty'))
            ->groupBy('item_id')
            ->get();

        foreach ($storeItems as $si) {
            StockTakeItem::create([
                'stock_take_id' => $stockTake->id,
                'item_id' => $si->item_id,
                'expected_quantity' => $si->total_qty,
                'actual_quantity' => $si->total_qty,
                'variance' => 0,
            ]);
        }

        return redirect()->route('inventory.stock-takes.show', $stockTake)
            ->with('success', 'Stock take started. Enter actual quantities for each item.');
    }

    public function show(StockTake $stockTake): View
    {
        $stockTake->load('store', 'taker', 'items.item');

        return view('inventory::stock_takes.show', compact('stockTake'));
    }

    public function updateItem(Request $request, StockTake $stockTake): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'actual_quantity' => 'required|numeric|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        $item = StockTakeItem::where('stock_take_id', $stockTake->id)
            ->where('item_id', $validated['item_id'])
            ->firstOrFail();

        $item->update([
            'actual_quantity' => $validated['actual_quantity'],
            'variance' => $validated['actual_quantity'] - $item->expected_quantity,
            'notes' => $validated['notes'] ?? $item->notes,
        ]);

        return response()->json(['message' => 'Quantity updated.', 'variance' => $item->variance]);
    }

    public function complete(StockTake $stockTake): RedirectResponse
    {
        if ($stockTake->status !== 'in_progress') {
            return back()->with('error', 'Stock take is not in progress.');
        }

        $stockTake->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return redirect()->route('inventory.stock-takes.show', $stockTake)
            ->with('success', 'Stock take completed. Review variances and approve to apply adjustments.');
    }

    public function approve(StockTake $stockTake): RedirectResponse
    {
        if ($stockTake->status !== 'completed') {
            return back()->with('error', 'Stock take must be completed before approval.');
        }

        DB::beginTransaction();
        try {
            $stockTake->load('items.item');

            foreach ($stockTake->items as $item) {
                if ($item->variance == 0) {
                    continue;
                }

                $variance = (int) $item->variance;

                if ($variance > 0) {
                    StoreItem::create([
                        'store_id' => $stockTake->store_id,
                        'item_id' => $item->item_id,
                        'lot_number' => 'STOCKTAKE-'.$stockTake->id,
                        'quantity' => $variance,
                        'total_cost' => $variance * ($item->item->price ?? 0),
                    ]);
                } else {
                    $remaining = abs($variance);
                    $lots = StoreItem::where('store_id', $stockTake->store_id)
                        ->where('item_id', $item->item_id)
                        ->where('quantity', '>', 0)
                        ->orderBy('expiry_date')
                        ->get();

                    foreach ($lots as $lot) {
                        if ($remaining <= 0) {
                            break;
                        }
                        $take = min($remaining, $lot->quantity);
                        $lot->quantity -= $take;
                        $lot->total_cost -= $take * ($item->item->price ?? 0);
                        $lot->save();
                        $remaining -= $take;
                    }
                }

                InventoryAdjustment::create([
                    'item_id' => $item->item_id,
                    'store_id' => $stockTake->store_id,
                    'type' => $variance > 0 ? 'correction' : 'write_off',
                    'quantity_change' => $variance,
                    'reason' => 'Stock take #'.$stockTake->id.' variance adjustment',
                    'adjusted_by' => auth()->id(),
                ]);

                StockMovement::log([
                    'item_id' => $item->item_id,
                    'store_id' => $stockTake->store_id,
                    'type' => 'stock_take',
                    'quantity_delta' => $variance,
                    'cost_delta' => $variance * ($item->item->price ?? 0),
                    'reference_type' => StockTake::class,
                    'reference_id' => $stockTake->id,
                    'notes' => 'Stock take variance correction',
                ]);
            }

            $stockTake->update(['status' => 'approved']);

            DB::commit();

            return redirect()->route('inventory.stock-takes.show', $stockTake)
                ->with('success', 'Stock take approved. Adjustments applied.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Error approving stock take.');
        }
    }
}
