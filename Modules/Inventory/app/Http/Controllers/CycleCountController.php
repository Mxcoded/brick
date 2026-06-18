<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Modules\Inventory\Models\CycleCount;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\StockMovement;
use Modules\Inventory\Models\Store;
use Modules\Inventory\Models\StoreItem;

class CycleCountController extends Controller
{
    public function index()
    {
        $counts = CycleCount::with('item', 'store', 'counter')->latest()->paginate(50);
        $items = Item::with('storeItems')->get();
        $stores = Store::all();

        return view('inventory::cycle_counts.index', compact('counts', 'items', 'stores'));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'store_id' => 'required|exists:stores,id',
            'actual_quantity' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $expectedQty = StoreItem::where('item_id', $validated['item_id'])
                ->where('store_id', $validated['store_id'])
                ->sum('quantity');

            $discrepancy = $validated['actual_quantity'] - $expectedQty;

            CycleCount::create([
                'item_id' => $validated['item_id'],
                'store_id' => $validated['store_id'],
                'expected_quantity' => $expectedQty,
                'actual_quantity' => $validated['actual_quantity'],
                'discrepancy' => $discrepancy,
                'counted_by' => auth()->id(),
                'counted_at' => now(),
                'status' => $discrepancy == 0 ? 'verified' : 'pending',
                'notes' => $validated['notes'],
            ]);

            if ($discrepancy != 0) {
                StockMovement::log([
                    'item_id' => $validated['item_id'],
                    'store_id' => $validated['store_id'],
                    'type' => 'cycle_count',
                    'quantity_delta' => $discrepancy,
                    'reference_type' => CycleCount::class,
                    'notes' => 'Cycle count discrepancy: '.$discrepancy,
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Cycle count recorded. Discrepancy: '.($discrepancy > 0 ? '+' : '').$discrepancy], 200);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['message' => 'Error recording cycle count.'], 500);
        }
    }
}
