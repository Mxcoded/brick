<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Modules\Inventory\Models\Store;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\StoreItem;
use Modules\Inventory\Models\Transfer;
use Modules\Inventory\Models\UsageLog;
use Modules\Inventory\Models\Supplier;
use Modules\Inventory\Models\RestockLog;
use Modules\Inventory\Models\PriceHistory;
use Modules\Inventory\Models\Department;
use Illuminate\View\View;
use Illuminate\Http\JsonResponse;

class InventoryController extends Controller
{
    /**
     * Display a list of all items with their supplier and current stock levels.
     */
    public function index(): View
    {
        $items = Item::with('supplier', 'storeItems.store')->get();
        $stores = Store::all();
        return view('inventory::index', compact('items', 'stores'));
    }

    /**
     * Show the form for creating a new item.
     */
    public function create(): View
    {
        $suppliers = Supplier::all();
        $stores = Store::all();
        return view('inventory::create', compact('suppliers', 'stores'));
    }

    /**
     * Store a newly created item and its initial inventory.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'description' => 'required|string|max:255|unique:items,description',
            'category' => 'nullable|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
            'price' => 'nullable|numeric|min:0',
            'unit_of_measurement' => 'nullable|string|max:50',
            'unit_value' => 'nullable|numeric|min:0',
            'store_id' => 'required|exists:stores,id',
            'quantity' => 'required|integer|min:1',
            'lot_number' => 'nullable|string',
            'expiry_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            // Create the new item in the central catalog
            $item = Item::create([
                'supplier_id' => $validatedData['supplier_id'],
                'description' => $validatedData['description'],
                'category' => $validatedData['category'],
                'price' => $validatedData['price'],
                'unit_of_measurement' => $validatedData['unit_of_measurement'],
                'unit_value' => $validatedData['unit_value'],
            ]);

            // Log the initial price
            if ($validatedData['price']) {
                PriceHistory::create([
                    'item_id' => $item->id,
                    'supplier_id' => $validatedData['supplier_id'],
                    'price' => $validatedData['price'],
                    'effective_date' => now(),
                ]);
            }

            // Create the initial stock record
            $totalCost = $validatedData['quantity'] * ($item->price ?? 0);
            StoreItem::create([
                'store_id' => $validatedData['store_id'],
                'item_id' => $item->id,
                'lot_number' => $validatedData['lot_number'] ?? 'N/A',
                'quantity' => $validatedData['quantity'],
                'expiry_date' => $validatedData['expiry_date'],
                'total_cost' => $totalCost,
            ]);

            // Log the restock event for the initial stock
            RestockLog::create([
                'item_id' => $item->id,
                'store_id' => $validatedData['store_id'],
                'quantity' => $validatedData['quantity'],
                'total_cost' => $totalCost,
                'lot_number' => $validatedData['lot_number'] ?? 'N/A',
                'restocked_by' => 'User Name' // Placeholder
            ]);

            DB::commit();
            return response()->json(['message' => 'New item and initial stock created successfully.'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating new item: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Error creating new item.'], 500);
        }
    }

    /**
     * Restock an existing item.
     */
    public function restock(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'item_id' => 'required|exists:items,id',
            'store_id' => 'required|exists:stores,id',
            'quantity' => 'required|integer|min:1',
            'lot_number' => 'nullable|string',
            'expiry_date' => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $item = Item::findOrFail($validatedData['item_id']);

            // Correct logic: Find an existing lot or create a new one
            $storeItem = StoreItem::firstOrNew([
                'item_id' => $item->id,
                'store_id' => $validatedData['store_id'],
                'lot_number' => $validatedData['lot_number'] ?? 'N/A'
            ]);

            // If a new record is being created, set its initial cost and expiry date
            if (!$storeItem->exists) {
                $storeItem->total_cost = 0;
                $storeItem->expiry_date = $validatedData['expiry_date'];
            }

            // Update the quantity and total cost for the existing or new record
            $totalCost = $validatedData['quantity'] * ($item->price ?? 0);
            $storeItem->quantity += $validatedData['quantity'];
            $storeItem->total_cost += $totalCost;
            $storeItem->expiry_date = $validatedData['expiry_date'];
            $storeItem->save();

            // Log the restock event
            RestockLog::create([
                'item_id' => $item->id,
                'store_id' => $validatedData['store_id'],
                'quantity' => $validatedData['quantity'],
                'total_cost' => $totalCost,
                'lot_number' => $validatedData['lot_number'] ?? 'N/A',
                'restocked_by' => 'User Name' // Placeholder
            ]);

            DB::commit();
            return response()->json(['message' => 'Item restocked successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error restocking item: ' . $e->getMessage(), ['exception' => $e]);
            return response()->json(['message' => 'Error restocking item.'], 500);
        }
    }
    /**
     * Show the form for editing the specified item.
     */
    public function edit(Item $item): View
    {
        $suppliers = Supplier::all();
        return view('inventory::edit', compact('item', 'suppliers'));
    }

    /**
     * Update the specified item in storage.
     */
    public function update(Request $request, Item $item): JsonResponse
    {
        $validatedData = $request->validate([
            'description' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
            'price' => 'nullable|numeric|min:0',
            'unit_of_measurement' => 'nullable|string|max:50',
            'unit_value' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            // Check if the price has changed before logging
            if (isset($validatedData['price']) && $validatedData['price'] != $item->price) {
                PriceHistory::create([
                    'item_id' => $item->id,
                    'supplier_id' => $validatedData['supplier_id'],
                    'price' => $validatedData['price'],
                    'effective_date' => now(),
                ]);
            }

            $item->update($validatedData);
            DB::commit();
            return response()->json(['message' => 'Item updated successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating item: ' . $e->getMessage());
            return response()->json(['message' => 'Error updating item.'], 500);
        }
    }

    /**
     * Remove the specified item from storage.
     */
    public function destroy(Item $item): JsonResponse
    {
        DB::beginTransaction();
        try {
            // Delete all associated StoreItem, Transfer, and UsageLog records
            $item->storeItems()->delete();
            $item->transfers()->delete();
            $item->usageLogs()->delete();

            $item->delete();
            DB::commit();
            return response()->json(['message' => 'Item deleted successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting item: ' . $e->getMessage());
            return response()->json(['message' => 'Error deleting item.'], 500);
        }
    }

    /**
     * Handles item transfers between stores using FEFO logic.
     */
    public function transferItems(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'item_id' => 'required|exists:items,id',
            'from_store_id' => 'required|exists:stores,id',
            'to_store_id' => 'required|exists:stores,id|different:from_store_id',
            'quantity' => 'required|integer|min:1',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $item = Item::findOrFail($validatedData['item_id']);
            $fromStore = Store::findOrFail($validatedData['from_store_id']);
            $toStore = Store::findOrFail($validatedData['to_store_id']);

            $remainingQuantityToTransfer = $validatedData['quantity'];
            $transferCost = 0;

            // Get all lots from the source store for the specific item, ordered by expiry date (FEFO)
            $lotsToTransfer = StoreItem::where('store_id', $fromStore->id)
                ->where('item_id', $item->id)
                ->where('quantity', '>', 0)
                ->orderBy('expiry_date', 'asc')
                ->get();

            if ($lotsToTransfer->sum('quantity') < $remainingQuantityToTransfer) {
                DB::rollBack();
                return response()->json(['message' => 'Insufficient stock at the source store.'], 400);
            }

            // Iterate through lots and transfer based on expiry date
            foreach ($lotsToTransfer as $lot) {
                if ($remainingQuantityToTransfer <= 0) break;

                $quantityToTake = min($remainingQuantityToTransfer, $lot->quantity);
                $costOfTransfer = $quantityToTake * ($item->price ?? 0);
                $transferCost += $costOfTransfer;

                // Reduce quantity and total cost at the source store
                $lot->quantity -= $quantityToTake;
                $lot->total_cost -= $costOfTransfer;
                $lot->save();

                // Add to the destination store
                // We'll create a new or update an existing lot entry for the destination store
                $destinationLot = StoreItem::firstOrNew([
                    'store_id' => $toStore->id,
                    'item_id' => $item->id,
                    'lot_number' => $lot->lot_number,
                ]);

                $destinationLot->quantity += $quantityToTake;
                $destinationLot->total_cost += $costOfTransfer;
                $destinationLot->expiry_date = $lot->expiry_date;
                $destinationLot->save();

                $remainingQuantityToTransfer -= $quantityToTake;
            }

            // Log the transfer for auditing
            Transfer::create([
                'from_store_id' => $fromStore->id,
                'to_store_id' => $toStore->id,
                'item_id' => $item->id,
                'quantity' => $validatedData['quantity'],
                'notes' => $validatedData['notes'],
            ]);

            DB::commit();
            return response()->json(['message' => 'Items transferred successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error transferring items: ' . $e->getMessage());
            return response()->json(['message' => 'Error transferring items.'], 500);
        }
    }

    /**
     * Records the usage of an item from a specific store.
     */
    public function recordUsage(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'item_id' => 'required|exists:items,id',
            'store_id' => 'required|exists:stores,id',
            'quantity_used' => 'required|integer|min:1',
            'used_for' => 'required|string|max:255',
            'technician_name' => 'nullable|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $item = Item::findOrFail($validatedData['item_id']);
            $remainingQuantityToUse = $validatedData['quantity_used'];
            $usageCost = 0;

            // Get all lots from the source store, ordered by expiry date (FEFO)
            $lotsToUse = StoreItem::where('store_id', $validatedData['store_id'])
                ->where('item_id', $item->id)
                ->where('quantity', '>', 0)
                ->orderBy('expiry_date', 'asc')
                ->get();

            if ($lotsToUse->sum('quantity') < $remainingQuantityToUse) {
                DB::rollBack();
                return response()->json(['message' => 'Insufficient stock to record usage.'], 400);
            }

            // Deduct from expiring lots first
            foreach ($lotsToUse as $lot) {
                if ($remainingQuantityToUse <= 0) break;

                $quantityToDeduct = min($remainingQuantityToUse, $lot->quantity);
                $costOfUsage = $quantityToDeduct * ($item->price ?? 0);
                $usageCost += $costOfUsage;

                $lot->quantity -= $quantityToDeduct;
                $lot->total_cost -= $costOfUsage;
                $lot->save();

                $remainingQuantityToUse -= $quantityToDeduct;
            }

            // Log the usage
            UsageLog::create([
                'item_id' => $item->id,
                'store_id' => $validatedData['store_id'],
                'quantity_used' => $validatedData['quantity_used'],
                'used_for' => $validatedData['used_for'],
                'technician_name' => $validatedData['technician_name'],
            ]);

            DB::commit();
            return response()->json(['message' => 'Item usage recorded successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error recording item usage: ' . $e->getMessage());
            return response()->json(['message' => 'Error recording item usage.'], 500);
        }
    }

    /**
     * Show the form for recording item usage.
     */
    public function usage(): View
    {
        $items = Item::all();
        $stores = Store::all();
        $departments = Department::all(); // Pass departments to the view
        return view('inventory::usage', compact('items', 'stores', 'departments'));
    }

    /**
     * Display a comprehensive inventory report.
     */
    public function report(): View
    {
        $stores = Store::all();
        $items = Item::all();
        // Eager load relationships for all reports
        $usageLogs = UsageLog::with(['item', 'store', 'department'])->get();
        $restockLogs = RestockLog::with(['item', 'store'])->get();
        $transferLogs = Transfer::with(['item', 'fromStore', 'toStore'])->get();
        $priceHistory = PriceHistory::with(['item', 'supplier'])->orderBy('effective_date', 'desc')->get();

        return view('inventory::report', compact('stores', 'items', 'usageLogs', 'restockLogs', 'transferLogs', 'priceHistory'));
    }

    /**
     * Get stock items for a specific store.
     *
     * @param  \Modules\Inventory\Models\Store  $store
     * @return \Illuminate\Http\JsonResponse
     */
    public function getStoreItems(Store $store): JsonResponse
    {
        $storeItems = StoreItem::where('store_id', $store->id)
            ->with('item')
            ->get();
        return response()->json($storeItems);
    }

    /**
     * Get a specific item's price history.
     */
    public function getItemPriceHistory(Item $item): JsonResponse
    {
        $priceHistory = $item->priceHistory()->with('supplier')->orderBy('effective_date', 'desc')->get();
        return response()->json($priceHistory);
    }
}
