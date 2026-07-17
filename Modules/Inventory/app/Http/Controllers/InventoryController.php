<?php

namespace Modules\Inventory\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ToArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Facades\Excel;
use Modules\Inventory\Exports\AdjustmentsExport;
use Modules\Inventory\Exports\ItemsExport;
use Modules\Inventory\Models\Department;
use Modules\Inventory\Models\InventoryAdjustment;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\ItemReturn;
use Modules\Inventory\Models\PriceHistory;
use Modules\Inventory\Models\PurchaseOrder;
use Modules\Inventory\Models\RestockLog;
use Modules\Inventory\Models\StockAlert;
use Modules\Inventory\Models\StockMovement;
use Modules\Inventory\Models\StockTake;
use Modules\Inventory\Models\Store;
use Modules\Inventory\Models\StoreItem;
use Modules\Inventory\Models\StoreLocation;
use Modules\Inventory\Models\Supplier;
use Modules\Inventory\Models\Transfer;
use Modules\Inventory\Models\UsageLog;
use PhpOffice\PhpSpreadsheet\Shared\Date;

class InventoryController extends Controller
{
    /**
     * Suggested reorder quantity based on usage velocity.
     */
    private function usageVelocity(Item $item, int $days = 30): int
    {
        $since = now()->subDays($days);
        $totalUsed = UsageLog::where('item_id', $item->id)
            ->where('created_at', '>=', $since)
            ->sum('quantity_used');

        return (int) ceil($totalUsed / max($days, 1));
    }

    /**
     * Generate stock alerts for low stock, expiring items, and expired items.
     */
    private function generateStockAlerts(): void
    {
        $now = now()->startOfDay();

        // Low stock alerts
        Item::lowStock()->with('storeItems.store')->chunk(50, function ($items) {
            foreach ($items as $item) {
                foreach ($item->storeItems->groupBy('store_id') as $storeId => $storeItems) {
                    $totalQty = $storeItems->sum('quantity');
                    if ($totalQty < ($item->min_stock ?? 0) && $totalQty > 0) {
                        StockAlert::create([
                            'item_id' => $item->id,
                            'store_id' => $storeId,
                            'type' => 'low_stock',
                            'severity' => 'warning',
                            'message' => "{$item->description} is low ({$totalQty} units, min {$item->min_stock}).",
                        ]);
                    }
                }
            }
        });

        // Expired items alerts
        StoreItem::with('item')
            ->whereNotNull('expiry_date')
            ->where('quantity', '>', 0)
            ->where('expiry_date', '<', $now)
            ->chunk(50, function ($storeItems) {
                foreach ($storeItems as $si) {
                    StockAlert::create([
                        'item_id' => $si->item_id,
                        'store_id' => $si->store_id,
                        'type' => 'expired',
                        'severity' => 'danger',
                        'message' => "{$si->item->description} expired on {$si->expiry_date->format('d M Y')}.",
                    ]);
                }
            });

        // Expiring within 30 days
        StoreItem::with('item')
            ->whereNotNull('expiry_date')
            ->where('quantity', '>', 0)
            ->where('expiry_date', '<=', $now->copy()->addDays(30))
            ->where('expiry_date', '>=', $now)
            ->chunk(50, function ($storeItems) {
                foreach ($storeItems as $si) {
                    StockAlert::create([
                        'item_id' => $si->item_id,
                        'store_id' => $si->store_id,
                        'type' => 'expiring',
                        'severity' => 'info',
                        'message' => "{$si->item->description} expires on {$si->expiry_date->format('d M Y')}.",
                    ]);
                }
            });
    }

    /**
     * Display a list of all items with their supplier and current stock levels.
     */
    public function index(): View
    {
        $items = Item::with('supplier', 'storeItems.store')->get();
        $stores = Store::all();

        $lowStockCount = Item::lowStock()->count();
        $pendingPOCount = PurchaseOrder::whereIn('status', ['draft', 'pending_approval'])->count();

        $recentAdjustments = InventoryAdjustment::with('item', 'adjustedBy')->latest()->take(5)->get();

        $now = now()->startOfDay();
        $expiringSoonCount = StoreItem::whereNotNull('expiry_date')
            ->where('quantity', '>', 0)
            ->where('expiry_date', '<=', $now->copy()->addDays(30))
            ->where('expiry_date', '>', $now)
            ->count();
        $expiredCount = StoreItem::whereNotNull('expiry_date')
            ->where('quantity', '>', 0)
            ->where('expiry_date', '<', $now)
            ->count();

        $recentMovements = StockMovement::with('item', 'store', 'user')->latest()->take(10)->get();

        // Reorder suggestions: items where current qty < min_stock, with usage velocity
        $reorderSuggestions = Item::with('storeItems.store', 'supplier')
            ->lowStock()
            ->get()
            ->map(function ($item) {
                $totalQty = $item->storeItems->sum('quantity');
                $dailyUsage = $this->usageVelocity($item);
                $suggestedOrder = max(($item->max_stock ?? ($item->min_stock * 2)) - $totalQty, 1);

                return (object) [
                    'item' => $item,
                    'total_qty' => $totalQty,
                    'min_stock' => $item->min_stock,
                    'daily_usage' => $dailyUsage,
                    'stockout_in' => $dailyUsage > 0 ? ceil($totalQty / $dailyUsage) : 'N/A',
                    'suggested_order' => $suggested_order = max($suggestedOrder, $dailyUsage * 14),
                ];
            })
            ->sortByDesc(fn ($r) => $r->daily_usage)
            ->take(10);

        // Resolve existing alerts older than 24h and regenerate fresh ones
        StockAlert::where('resolved', false)->where('created_at', '<', now()->subDay())->update(['resolved' => true, 'resolved_at' => now()]);
        $this->generateStockAlerts();

        return view('inventory::index', compact(
            'items', 'stores', 'lowStockCount', 'pendingPOCount',
            'recentAdjustments', 'expiringSoonCount', 'expiredCount',
            'recentMovements', 'reorderSuggestions'
        ));
    }

    public function itemsIndex(): View
    {
        $items = Item::with('supplier', 'storeItems')
            ->orderBy('description')
            ->paginate(25);

        $categories = Item::whereNotNull('category')->distinct()->orderBy('category')->pluck('category');
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        return view('inventory::items_index', compact('items', 'categories', 'suppliers'));
    }

    /**
     * Show the form for creating a new item.
     */
    public function create(): View
    {
        $suppliers = Supplier::all();
        $stores = Store::all();
        $nextSku = Item::generateNextSku();

        return view('inventory::create', compact('suppliers', 'stores', 'nextSku'));
    }

    /**
     * Store a newly created item and its initial inventory.
     */
    public function store(Request $request): JsonResponse
    {
        $validatedData = $request->validate([
            'sku' => 'nullable|string|max:100|unique:items,sku',
            'description' => 'required|string|max:255|unique:items,description',
            'category' => 'nullable|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
            'price' => 'nullable|numeric|min:0',
            'unit_of_measurement' => 'nullable|string|max:50',
            'unit_value' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'store_id' => 'required|exists:stores,id',
            'quantity' => 'required|integer|min:1',
            'lot_number' => 'nullable|string',
            'expiry_date' => 'nullable|date',
            'conv_from_unit' => 'nullable|string|max:50',
            'conv_to_unit' => 'nullable|string|max:50',
            'conv_rate' => 'nullable|numeric|min:0.0001',
        ]);

        DB::beginTransaction();
        try {
            // Create the new item in the central catalog
            $item = Item::create([
                'sku' => $validatedData['sku'] ?? null,
                'supplier_id' => $validatedData['supplier_id'],
                'description' => $validatedData['description'],
                'category' => $validatedData['category'],
                'price' => $validatedData['price'],
                'unit_of_measurement' => $validatedData['unit_of_measurement'],
                'unit_value' => $validatedData['unit_value'],
                'min_stock' => $validatedData['min_stock'] ?? null,
                'max_stock' => $validatedData['max_stock'] ?? null,
            ]);

            // Save unit conversion if provided
            if ($validatedData['conv_from_unit'] && $validatedData['conv_to_unit'] && $validatedData['conv_rate']) {
                $item->conversions()->create([
                    'from_unit' => $validatedData['conv_from_unit'],
                    'to_unit' => $validatedData['conv_to_unit'],
                    'conversion_rate' => $validatedData['conv_rate'],
                ]);
            }

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
                'restocked_by_id' => auth()->id(),
                'restocked_by' => auth()->user()->name ?? 'System',
            ]);

            DB::commit();

            return response()->json(['message' => 'New item and initial stock created successfully.'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating new item: '.$e->getMessage(), ['exception' => $e]);

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
                'lot_number' => $validatedData['lot_number'] ?? 'N/A',
            ]);

            // If a new record is being created, set its initial cost and expiry date
            if (! $storeItem->exists) {
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
            $restockLog = RestockLog::create([
                'item_id' => $item->id,
                'store_id' => $validatedData['store_id'],
                'quantity' => $validatedData['quantity'],
                'total_cost' => $totalCost,
                'lot_number' => $validatedData['lot_number'] ?? 'N/A',
                'restocked_by_id' => auth()->id(),
                'restocked_by' => auth()->user()->name ?? 'System',
            ]);

            StockMovement::log([
                'item_id' => $item->id,
                'store_id' => $validatedData['store_id'],
                'type' => 'restock',
                'quantity_delta' => $validatedData['quantity'],
                'cost_delta' => $totalCost,
                'reference_type' => RestockLog::class,
                'reference_id' => $restockLog->id,
            ]);

            DB::commit();

            return response()->json(['message' => 'Item restocked successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error restocking item: '.$e->getMessage(), ['exception' => $e]);

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
            'sku' => 'nullable|string|max:100|unique:items,sku,'.$item->id,
            'description' => 'required|string|max:255',
            'category' => 'nullable|string|max:255',
            'supplier_id' => 'required|exists:suppliers,id',
            'price' => 'nullable|numeric|min:0',
            'unit_of_measurement' => 'nullable|string|max:50',
            'unit_value' => 'nullable|numeric|min:0',
            'min_stock' => 'nullable|integer|min:0',
            'max_stock' => 'nullable|integer|min:0',
            'conv_from_unit' => 'nullable|string|max:50',
            'conv_to_unit' => 'nullable|string|max:50',
            'conv_rate' => 'nullable|numeric|min:0.0001',
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

            // Update or create unit conversion
            if ($validatedData['conv_from_unit'] && $validatedData['conv_to_unit'] && $validatedData['conv_rate']) {
                $item->conversions()->delete();
                $item->conversions()->create([
                    'from_unit' => $validatedData['conv_from_unit'],
                    'to_unit' => $validatedData['conv_to_unit'],
                    'conversion_rate' => $validatedData['conv_rate'],
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Item updated successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating item: '.$e->getMessage());

            return response()->json(['message' => 'Error updating item.'], 500);
        }
    }

    /**
     * Remove the specified item from storage.
     */
    public function destroy(Item $item): RedirectResponse
    {
        DB::beginTransaction();
        try {
            // Delete all associated StoreItem, Transfer, and UsageLog records
            $item->storeItems()->delete();
            $item->transfers()->delete();
            $item->usageLogs()->delete();

            $item->delete();
            DB::commit();

            return redirect()->route('inventory.dashboard')
                ->with('success', 'Item deleted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting item: '.$e->getMessage());

            return redirect()->route('inventory.dashboard')
                ->with('error', 'Error deleting item.');
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
                if ($remainingQuantityToTransfer <= 0) {
                    break;
                }

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
            $transfer = Transfer::create([
                'from_store_id' => $fromStore->id,
                'to_store_id' => $toStore->id,
                'item_id' => $item->id,
                'quantity' => $validatedData['quantity'],
                'notes' => $validatedData['notes'],
            ]);

            StockMovement::log([
                'item_id' => $item->id,
                'store_id' => $fromStore->id,
                'type' => 'transfer_out',
                'quantity_delta' => -$validatedData['quantity'],
                'cost_delta' => -$transferCost,
                'reference_type' => Transfer::class,
                'reference_id' => $transfer->id,
                'notes' => 'Transferred to '.$toStore->name,
            ]);

            StockMovement::log([
                'item_id' => $item->id,
                'store_id' => $toStore->id,
                'type' => 'transfer_in',
                'quantity_delta' => $validatedData['quantity'],
                'cost_delta' => $transferCost,
                'reference_type' => Transfer::class,
                'reference_id' => $transfer->id,
                'notes' => 'Received from '.$fromStore->name,
            ]);

            DB::commit();

            return response()->json(['message' => 'Items transferred successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error transferring items: '.$e->getMessage());

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
            'department_id' => 'nullable|exists:departments,id',
            'quantity_used' => 'required|integer|min:1',
            'used_for' => 'required|string|max:255',
            'technician_name' => 'nullable|string|max:255',
            'date_used' => 'nullable|date',
            'reference' => 'nullable|string|max:100',
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
                if ($remainingQuantityToUse <= 0) {
                    break;
                }

                $quantityToDeduct = min($remainingQuantityToUse, $lot->quantity);
                $costOfUsage = $quantityToDeduct * ($item->price ?? 0);
                $usageCost += $costOfUsage;

                $lot->quantity -= $quantityToDeduct;
                $lot->total_cost -= $costOfUsage;
                $lot->save();

                $remainingQuantityToUse -= $quantityToDeduct;
            }

            // Log the usage
            $usageLog = UsageLog::create([
                'item_id' => $item->id,
                'store_id' => $validatedData['store_id'],
                'department_id' => $validatedData['department_id'] ?? null,
                'unit_cost' => $item->price ?? 0,
                'quantity_used' => $validatedData['quantity_used'],
                'used_for' => $validatedData['used_for'],
                'technician_name' => $validatedData['technician_name'],
                'date_used' => $validatedData['date_used'] ?? now()->toDateString(),
                'reference' => $validatedData['reference'] ?? null,
            ]);

            StockMovement::log([
                'item_id' => $item->id,
                'store_id' => $validatedData['store_id'],
                'type' => 'usage',
                'quantity_delta' => -$validatedData['quantity_used'],
                'cost_delta' => -$usageCost,
                'reference_type' => UsageLog::class,
                'reference_id' => $usageLog->id,
            ]);

            DB::commit();

            return response()->json(['message' => 'Item usage recorded successfully.', 'cost' => $usageCost]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error recording item usage: '.$e->getMessage());

            return response()->json(['message' => 'Error recording item usage.'], 500);
        }
    }

    /**
     * Show the form for recording item usage.
     */
    public function usage(): View
    {
        $items = Item::orderBy('description')->get(['id', 'description', 'category', 'unit_of_measurement', 'price']);
        $stores = Store::with('departments')->get(['id', 'name']);
        $recentUsage = UsageLog::with('item:id,description,unit_of_measurement', 'store:id,name', 'department:id,name')->latest()->take(10)->get();

        return view('inventory::usage', compact('items', 'stores', 'recentUsage'));
    }

    /**
     * Display a comprehensive inventory report.
     */
    public function report(Request $request): View
    {
        $stores = Store::all();
        $items = Item::all();
        $usageLogs = UsageLog::with(['item', 'store', 'department'])->latest()->paginate(50, pageName: 'usage_page');
        $restockLogs = RestockLog::with(['item', 'store'])->latest()->paginate(50, pageName: 'restock_page');
        $transferLogs = Transfer::with(['item', 'fromStore', 'toStore'])->latest()->paginate(50, pageName: 'transfer_page');
        $priceHistory = PriceHistory::with(['item', 'supplier'])->orderBy('effective_date', 'desc')->paginate(50, pageName: 'price_page');

        return view('inventory::report', compact('stores', 'items', 'usageLogs', 'restockLogs', 'transferLogs', 'priceHistory'));
    }

    /**
     * Generate the next reference number for a department.
     */
    public function generateReference(Department $department): JsonResponse
    {
        $prefix = strtoupper(substr(preg_replace('/[^a-zA-Z]/', '', $department->name), 0, 2));

        if (strlen($prefix) < 2) {
            $prefix = str_pad($prefix, 2, 'X');
        }

        $last = UsageLog::where('reference', 'LIKE', $prefix.'-%')
            ->orderByRaw('CAST(SUBSTRING(reference, 4) AS UNSIGNED) DESC')
            ->value('reference');

        if ($last) {
            $num = (int) substr($last, 3) + 1;
        } else {
            $num = 1;
        }

        return response()->json([
            'reference' => $prefix.'-'.str_pad($num, 3, '0', STR_PAD_LEFT),
        ]);
    }

    /**
     * Get stock items for a specific store.
     */
    public function getStoreItems(Store $store, Request $request): JsonResponse
    {
        $query = StoreItem::where('store_id', $store->id)->with('item');

        if ($request->filled('item_id')) {
            $query->where('item_id', $request->item_id);
        }

        return response()->json($query->get());
    }

    /**
     * Display inventory adjustments.
     */
    public function adjustments()
    {
        $adjustments = InventoryAdjustment::with('item', 'store', 'adjustedBy')->latest()->paginate(50);
        $items = Item::all();
        $stores = Store::all();

        return view('inventory::adjustments.index', compact('adjustments', 'items', 'stores'));
    }

    /**
     * Record an inventory adjustment (write-off or correction).
     */
    public function storeAdjustment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'store_id' => 'required|exists:stores,id',
            'type' => 'required|in:write_off,correction',
            'quantity_change' => 'required|integer|min:1',
            'reason' => 'required|string|max:255',
        ]);

        DB::beginTransaction();
        try {
            $quantityChange = $validated['type'] === 'write_off' ? -$validated['quantity_change'] : $validated['quantity_change'];

            $lots = StoreItem::where('store_id', $validated['store_id'])
                ->where('item_id', $validated['item_id'])
                ->where('quantity', '>', 0)
                ->orderBy('expiry_date', 'asc')
                ->get();

            $remaining = abs($quantityChange);

            if ($quantityChange < 0 && $lots->sum('quantity') < $remaining) {
                DB::rollBack();

                return response()->json(['message' => 'Insufficient stock for write-off.'], 400);
            }

            foreach ($lots as $lot) {
                if ($remaining <= 0) {
                    break;
                }

                $toDeduct = min($remaining, $lot->quantity);
                $costDeduction = $toDeduct * ($lot->item->price ?? 0);
                $lot->quantity -= $toDeduct;
                $lot->total_cost -= $costDeduction;
                $lot->save();
                $remaining -= $toDeduct;
            }

            if ($quantityChange > 0) {
                $defaultLot = StoreItem::firstOrNew([
                    'store_id' => $validated['store_id'],
                    'item_id' => $validated['item_id'],
                    'lot_number' => 'ADJ-'.now()->format('Ymd'),
                ]);
                $defaultLot->quantity += $quantityChange;
                $defaultLot->total_cost += $quantityChange * (Item::find($validated['item_id'])->price ?? 0);
                $defaultLot->save();
            }

            $adjustment = InventoryAdjustment::create([
                'item_id' => $validated['item_id'],
                'store_id' => $validated['store_id'],
                'type' => $validated['type'],
                'quantity_change' => $quantityChange,
                'reason' => $validated['reason'],
                'adjusted_by' => auth()->id(),
            ]);

            StockMovement::log([
                'item_id' => $validated['item_id'],
                'store_id' => $validated['store_id'],
                'type' => 'adjustment',
                'quantity_delta' => $quantityChange,
                'cost_delta' => $quantityChange * (Item::find($validated['item_id'])->price ?? 0),
                'reference_type' => InventoryAdjustment::class,
                'reference_id' => $adjustment->id,
            ]);

            DB::commit();

            return response()->json(['message' => 'Adjustment recorded successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error recording adjustment: '.$e->getMessage());

            return response()->json(['message' => 'Error recording adjustment.'], 500);
        }
    }

    /**
     * Show the low stock items.
     */
    public function lowStock()
    {
        $items = Item::with('supplier', 'storeItems.store')
            ->lowStock()
            ->get();

        return view('inventory::low_stock', compact('items'));
    }

    public function scan()
    {
        return view('inventory::scan');
    }

    public function lookupBarcode(Request $request)
    {
        $sku = $request->query('sku');
        $item = Item::with('storeItems.store', 'supplier')->where('sku', $sku)->first();

        if (! $item) {
            return response()->json(['found' => false, 'message' => 'Item not found with that SKU.']);
        }

        return response()->json([
            'found' => true,
            'item' => [
                'id' => $item->id,
                'sku' => $item->sku,
                'description' => $item->description,
                'category' => $item->category,
                'supplier' => $item->supplier->name ?? 'N/A',
                'price' => $item->price,
                'stock' => $item->storeItems->map(fn ($si) => [
                    'store' => $si->store->name ?? 'Unknown',
                    'quantity' => $si->quantity,
                ]),
                'total_quantity' => $item->storeItems->sum('quantity'),
            ],
        ]);
    }

    // ─── Store Locations ─────────────────────────────────────────────

    public function locationsIndex(Store $store): View
    {
        $locations = $store->locations()->orderBy('zone')->orderBy('aisle')->orderBy('rack')->get();

        return view('inventory::locations.index', compact('store', 'locations'));
    }

    public function locationsCreate(Store $store): View
    {
        return view('inventory::locations.create', compact('store'));
    }

    public function locationsStore(Request $request, Store $store): RedirectResponse
    {
        $validated = $request->validate([
            'zone' => 'nullable|string|max:100',
            'aisle' => 'nullable|string|max:100',
            'rack' => 'nullable|string|max:100',
            'shelf' => 'nullable|string|max:100',
            'code' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $store->locations()->create($validated);

        return redirect()->route('inventory.locations.index', $store)
            ->with('success', 'Location created successfully.');
    }

    public function locationsEdit(Store $store, StoreLocation $location): View
    {
        return view('inventory::locations.edit', compact('store', 'location'));
    }

    public function locationsUpdate(Request $request, Store $store, StoreLocation $location): RedirectResponse
    {
        $validated = $request->validate([
            'zone' => 'nullable|string|max:100',
            'aisle' => 'nullable|string|max:100',
            'rack' => 'nullable|string|max:100',
            'shelf' => 'nullable|string|max:100',
            'code' => 'nullable|string|max:50',
            'notes' => 'nullable|string',
        ]);

        $location->update($validated);

        return redirect()->route('inventory.locations.index', $store)
            ->with('success', 'Location updated successfully.');
    }

    public function locationsDestroy(Store $store, StoreLocation $location): RedirectResponse
    {
        $location->delete();

        return redirect()->route('inventory.locations.index', $store)
            ->with('success', 'Location deleted successfully.');
    }

    public function getStoreLocations(Store $store): JsonResponse
    {
        return response()->json($store->locations()->orderBy('code')->orderBy('zone')->get());
    }

    // ─── Item Returns ─────────────────────────────────────────────────

    public function returnsIndex(): View
    {
        $returns = ItemReturn::with('item', 'store', 'department')->latest()->paginate(50);
        $stores = Store::all();
        $items = Item::all();

        return view('inventory::returns.index', compact('returns', 'stores', 'items'));
    }

    public function returnsCreate(): View
    {
        $stores = Store::with('departments')->get();
        $items = Item::with('storeItems.store')->orderBy('description')->get();

        return view('inventory::returns.create', compact('stores', 'items'));
    }

    public function returnsStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'store_id' => 'required|exists:stores,id',
            'department_id' => 'nullable|exists:departments,id',
            'quantity_returned' => 'required|integer|min:1',
            'reason' => 'nullable|string|max:255',
            'returned_by' => 'nullable|string|max:255',
            'received_by' => 'nullable|string|max:255',
            'reference' => 'nullable|string|max:100',
            'notes' => 'nullable|string',
        ]);

        DB::beginTransaction();
        try {
            $item = Item::findOrFail($validated['item_id']);

            // Restore stock to store (using a generic return lot)
            $storeItem = StoreItem::firstOrNew([
                'store_id' => $validated['store_id'],
                'item_id' => $item->id,
                'lot_number' => 'RET-'.now()->format('Ymd'),
            ]);

            $returnCost = $validated['quantity_returned'] * ($item->price ?? 0);
            $storeItem->quantity += $validated['quantity_returned'];
            $storeItem->total_cost += $returnCost;
            $storeItem->save();

            $itemReturn = ItemReturn::create([
                'item_id' => $item->id,
                'store_id' => $validated['store_id'],
                'department_id' => $validated['department_id'] ?? null,
                'quantity_returned' => $validated['quantity_returned'],
                'reason' => $validated['reason'],
                'returned_by' => $validated['returned_by'],
                'received_by' => $validated['received_by'] ?? auth()->user()->name,
                'reference' => $validated['reference'],
                'notes' => $validated['notes'],
            ]);

            StockMovement::log([
                'item_id' => $item->id,
                'store_id' => $validated['store_id'],
                'type' => 'return',
                'quantity_delta' => $validated['quantity_returned'],
                'cost_delta' => $returnCost,
                'reference_type' => ItemReturn::class,
                'reference_id' => $itemReturn->id,
                'notes' => $validated['reason'] ?? 'Return from department',
            ]);

            DB::commit();

            return redirect()->route('inventory.returns.index')
                ->with('success', 'Return recorded and stock restored successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error recording return: '.$e->getMessage());

            return back()->with('error', 'Error recording return.')->withInput();
        }
    }

    // ─── Stock Alerts ─────────────────────────────────────────────────

    public function alertsIndex(): View
    {
        $alerts = StockAlert::with('item', 'store')
            ->unresolved()
            ->latest()
            ->paginate(50);

        $resolvedCount = StockAlert::where('resolved', true)->count();

        return view('inventory::alerts.index', compact('alerts', 'resolvedCount'));
    }

    public function alertsResolve(StockAlert $alert): RedirectResponse
    {
        $alert->update(['resolved' => true, 'resolved_at' => now()]);

        return back()->with('success', 'Alert resolved.');
    }

    public function alertsResolveAll(): RedirectResponse
    {
        StockAlert::unresolved()->update(['resolved' => true, 'resolved_at' => now()]);

        return redirect()->route('inventory.alerts.index')->with('success', 'All alerts resolved.');
    }

    // ─── Item Photo Upload ────────────────────────────────────────────

    public function uploadPhoto(Request $request, Item $item): JsonResponse
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
        ]);

        $photoPath = $request->file('photo')->store('items/photos', 'public');
        $item->update(['photo_path' => $photoPath]);

        return response()->json([
            'message' => 'Photo uploaded successfully.',
            'url' => asset('storage/'.$photoPath),
        ]);
    }

    public function removePhoto(Item $item): JsonResponse
    {
        if ($item->photo_path) {
            Storage::disk('public')->delete($item->photo_path);
            $item->update(['photo_path' => null]);
        }

        return response()->json(['message' => 'Photo removed.']);
    }

    // ─── Mobile Stock Take View ───────────────────────────────────────

    public function stockTakeMobile(StockTake $stockTake): View
    {
        $stockTake->load('store', 'taker', 'items.item');

        return view('inventory::stock_takes.mobile', compact('stockTake'));
    }

    public function exportItems(Request $request)
    {
        $category = $request->query('category');
        $supplierId = $request->query('supplier_id');

        $filename = 'inventory-items';
        if ($category) {
            $filename .= '-'.strtolower($category);
        }
        $filename .= '-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(
            new ItemsExport($category, $supplierId),
            $filename
        );
    }

    /**
     * Show the export adjustments page.
     */
    public function exportAdjustments(Request $request)
    {
        $type = $request->query('type');
        $storeId = $request->query('store_id');

        $filename = 'inventory-adjustments';
        if ($type) {
            $filename .= '-'.str_replace('_', '-', $type);
        }
        $filename .= '-'.now()->format('Y-m-d').'.xlsx';

        return Excel::download(
            new AdjustmentsExport($type, $storeId ? (int) $storeId : null),
            $filename
        );
    }

    /**
     * Display stock valuation report with quantities and values by store/category.
     */
    public function valuation(Request $request): View
    {
        $stores = Store::orderBy('name')->get(['id', 'name']);

        $items = Item::with('storeItems.store', 'supplier')
            ->when($request->filled('store_id'), function ($q) use ($request) {
                $q->whereHas('storeItems', fn ($sq) => $sq->where('store_id', $request->store_id));
            })
            ->when($request->filled('category'), function ($q) use ($request) {
                $q->where('category', $request->category);
            })
            ->orderBy('category')
            ->orderBy('description')
            ->get();

        $categories = Item::whereNotNull('category')->distinct()->orderBy('category')->pluck('category');

        $storeTotals = [];
        $grandTotal = 0;

        foreach ($stores as $store) {
            $storeTotals[$store->id] = 0;
        }

        foreach ($items as $item) {
            $item->totalQty = 0;
            $item->totalValue = 0;
            foreach ($item->storeItems as $si) {
                $item->totalQty += $si->quantity;
                $item->totalValue += $si->total_cost ?? 0;
                if (isset($storeTotals[$si->store_id])) {
                    $storeTotals[$si->store_id] += $si->total_cost ?? 0;
                }
            }
            $grandTotal += $item->totalValue;
        }

        return view('inventory::valuation', compact('items', 'stores', 'categories', 'storeTotals', 'grandTotal'));
    }

    /**
     * Display stock aging / expiry report grouped by expiry timeframes.
     */
    public function stockAging(Request $request): View
    {
        $stores = Store::orderBy('name')->get(['id', 'name']);

        $storeId = $request->store_id;
        $now = now()->startOfDay();

        $query = StoreItem::with('item', 'store')
            ->whereNotNull('expiry_date')
            ->where('quantity', '>', 0);

        if ($storeId) {
            $query->where('store_id', $storeId);
        }

        $items = $query->orderBy('expiry_date')->get();

        $expired = $items->filter(fn ($si) => $si->expiry_date < $now);
        $expiring30 = $items->filter(fn ($si) => $si->expiry_date >= $now && $si->expiry_date <= $now->copy()->addDays(30));
        $expiring60 = $items->filter(fn ($si) => $si->expiry_date > $now->copy()->addDays(30) && $si->expiry_date <= $now->copy()->addDays(60));
        $expiring90 = $items->filter(fn ($si) => $si->expiry_date > $now->copy()->addDays(60) && $si->expiry_date <= $now->copy()->addDays(90));
        $farFuture = $items->filter(fn ($si) => $si->expiry_date > $now->copy()->addDays(90));

        return view('inventory::stock_aging', compact('stores', 'expired', 'expiring30', 'expiring60', 'expiring90', 'farFuture', 'storeId'));
    }

    public function barcodeLabels(): View
    {
        $items = Item::orderBy('description')->get(['id', 'sku', 'description', 'price', 'unit_of_measurement']);

        return view('inventory::barcode_labels', compact('items'));
    }

    public function showImport(): View
    {
        $stores = Store::orderBy('name')->get(['id', 'name']);
        $suppliers = Supplier::orderBy('name')->get(['id', 'name']);

        return view('inventory::import', compact('stores', 'suppliers'));
    }

    public function downloadImportTemplate()
    {
        $headings = ['sku', 'description', 'category', 'supplier_id', 'price', 'unit_of_measurement', 'unit_value', 'min_stock', 'max_stock', 'store_id', 'quantity', 'lot_number', 'expiry_date'];

        return Excel::download(new class($headings) implements FromArray, WithHeadings
        {
            protected array $headings;

            public function __construct(array $headings)
            {
                $this->headings = $headings;
            }

            public function array(): array
            {
                return [
                    ['BRK-0001', 'Example Item', 'Food & Beverage', '1', '1500.00', 'kg', '1', '10', '100', '1', '50', 'LOT001', '2026-12-31'],
                ];
            }

            public function headings(): array
            {
                return $this->headings;
            }
        }, 'import-template.xlsx');
    }

    public function importItems(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'default_store_id' => 'nullable|exists:stores,id',
        ]);

        $defaultSupplierId = $request->default_supplier_id;
        $defaultStoreId = $request->default_store_id;

        $imported = 0;
        $errors = [];

        try {
            $rows = Excel::toArray(new class implements ToArray
            {
                public function array(array $array)
                {
                    return $array;
                }
            }, $request->file('file'));

            $rows = $rows[0] ?? [];
            $headings = array_map('strtolower', $rows[0] ?? []);
            unset($rows[0]);

            $skuIdx = array_search('sku', $headings);
            $descIdx = array_search('description', $headings);
            $catIdx = array_search('category', $headings);
            $supplierIdx = array_search('supplier_id', $headings);
            $priceIdx = array_search('price', $headings);
            $uomIdx = array_search('unit_of_measurement', $headings);
            $uvIdx = array_search('unit_value', $headings);
            $minIdx = array_search('min_stock', $headings);
            $maxIdx = array_search('max_stock', $headings);
            $storeIdx = array_search('store_id', $headings);
            $qtyIdx = array_search('quantity', $headings);
            $lotIdx = array_search('lot_number', $headings);
            $expiryIdx = array_search('expiry_date', $headings);

            if ($descIdx === false) {
                return back()->with('error', 'The spreadsheet must have a "description" column.');
            }

            foreach ($rows as $i => $row) {
                $rowNum = $i + 2;

                if (empty(array_filter($row))) {
                    continue;
                }

                $description = trim($row[$descIdx] ?? '');
                if (empty($description)) {
                    $errors[] = "Row {$rowNum}: description is required.";

                    continue;
                }

                $supplierId = $supplierIdx !== false && ! empty($row[$supplierIdx])
                    ? (int) $row[$supplierIdx]
                    : ($defaultSupplierId ?: null);

                $storeId = $storeIdx !== false && ! empty($row[$storeIdx])
                    ? (int) $row[$storeIdx]
                    : ($defaultStoreId ?: null);

                DB::beginTransaction();
                try {
                    $sku = $skuIdx !== false ? trim($row[$skuIdx] ?? '') : null;

                    $item = Item::create([
                        'sku' => $sku ?: null,
                        'supplier_id' => $supplierId,
                        'description' => $description,
                        'category' => $catIdx !== false ? trim($row[$catIdx] ?? '') : null,
                        'price' => $priceIdx !== false ? (float) ($row[$priceIdx] ?? 0) : 0,
                        'unit_of_measurement' => $uomIdx !== false ? trim($row[$uomIdx] ?? '') : null,
                        'unit_value' => $uvIdx !== false ? (float) ($row[$uvIdx] ?? 0) : null,
                        'min_stock' => $minIdx !== false ? (int) ($row[$minIdx] ?? 0) : null,
                        'max_stock' => $maxIdx !== false ? (int) ($row[$maxIdx] ?? 0) : null,
                    ]);

                    $quantity = $qtyIdx !== false ? (int) ($row[$qtyIdx] ?? 0) : 0;

                    if ($quantity > 0 && $storeId) {
                        $totalCost = $quantity * ($item->price ?? 0);
                        StoreItem::create([
                            'store_id' => $storeId,
                            'item_id' => $item->id,
                            'lot_number' => $lotIdx !== false ? trim($row[$lotIdx] ?? '') : 'IMPORT',
                            'quantity' => $quantity,
                            'total_cost' => $totalCost,
                            'expiry_date' => $expiryIdx !== false && ! empty($row[$expiryIdx])
                                ? Date::excelToDateTimeObject($row[$expiryIdx])->format('Y-m-d')
                                : null,
                        ]);

                        RestockLog::create([
                            'item_id' => $item->id,
                            'store_id' => $storeId,
                            'quantity' => $quantity,
                            'total_cost' => $totalCost,
                            'lot_number' => $lotIdx !== false ? trim($row[$lotIdx] ?? '') : 'IMPORT',
                            'restocked_by_id' => auth()->id(),
                            'restocked_by' => auth()->user()->name ?? 'System',
                        ]);

                        StockMovement::log([
                            'item_id' => $item->id,
                            'store_id' => $storeId,
                            'type' => 'restock',
                            'quantity_delta' => $quantity,
                            'cost_delta' => $totalCost,
                            'notes' => 'Bulk import',
                        ]);
                    }

                    DB::commit();
                    $imported++;
                } catch (\Exception $e) {
                    DB::rollBack();
                    $errors[] = "Row {$rowNum} ({$description}): ".$e->getMessage();
                }
            }
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to read file: '.$e->getMessage());
        }

        $message = "Imported {$imported} item(s) successfully.";
        if (! empty($errors)) {
            $message .= ' '.count($errors).' error(s): '.implode('; ', array_slice($errors, 0, 10));
            if (count($errors) > 10) {
                $message .= ' (and '.(count($errors) - 10).' more)';
            }
        }

        return redirect()->route('inventory.import')->with(
            $errors ? 'warning' : 'success',
            $message
        );
    }
}
