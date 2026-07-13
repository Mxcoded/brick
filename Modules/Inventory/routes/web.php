<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\CycleCountController;
use Modules\Inventory\Http\Controllers\DepartmentController;
use Modules\Inventory\Http\Controllers\InventoryController;
use Modules\Inventory\Http\Controllers\PurchaseOrderController;
use Modules\Inventory\Http\Controllers\StockTakeController;
use Modules\Inventory\Http\Controllers\StoreController;
use Modules\Inventory\Http\Controllers\SupplierController;
use Modules\Inventory\Models\Item;
use Modules\Inventory\Models\Store;

Route::group(['prefix' => 'inventory', 'as' => 'inventory.', 'middleware' => ['auth', 'can:access_inventory_dashboard']], function () {
    // Dashboard
    Route::get('/', [InventoryController::class, 'index'])->name('dashboard');

    // Items management (CRUD)
    Route::get('/items', [InventoryController::class, 'itemsIndex'])->name('items.index');
    Route::get('/items/create', [InventoryController::class, 'create'])->name('items.create')->middleware('can:inventory.create');
    Route::post('/items', [InventoryController::class, 'store'])->name('items.store')->middleware('can:inventory.create');
    Route::get('/items/{item}/edit', [InventoryController::class, 'edit'])->name('items.edit')->middleware('can:inventory.update');
    Route::put('/items/{item}', [InventoryController::class, 'update'])->name('items.update')->middleware('can:inventory.update');
    Route::delete('/items/{item}', [InventoryController::class, 'destroy'])->name('items.destroy')->middleware('can:inventory.delete');
    Route::post('/items/restock', [InventoryController::class, 'restock'])->name('items.restock')->middleware('can:inventory.restock');

    // Low stock (read-only)
    Route::get('/low-stock', [InventoryController::class, 'lowStock'])->name('low-stock');

    // Item transfers
    Route::post('/transfer', [InventoryController::class, 'transferItems'])->name('transfer')->middleware('can:inventory.transfer');
    Route::get('/transfers', function () {
        $stores = Store::all();
        $items = Item::all();

        return view('inventory::transfer', compact('stores', 'items'));
    })->name('transfers.index')->middleware('can:inventory.transfer');

    // Item usage
    Route::get('/usage', [InventoryController::class, 'usage'])->name('usage')->middleware('can:inventory.usage');
    Route::post('/usage/store', [InventoryController::class, 'recordUsage'])->name('usage.store')->middleware('can:inventory.usage');

    // Inventory adjustments
    Route::get('/adjustments', [InventoryController::class, 'adjustments'])->name('adjustments.index')->middleware('can:inventory.adjustments');
    Route::post('/adjustments', [InventoryController::class, 'storeAdjustment'])->name('adjustments.store')->middleware('can:inventory.adjustments');

    // Cycle counts
    Route::get('/cycle-counts', [CycleCountController::class, 'index'])->name('cycle-counts.index')->middleware('can:inventory.adjustments');
    Route::post('/cycle-counts', [CycleCountController::class, 'store'])->name('cycle-counts.store')->middleware('can:inventory.adjustments');

    // Purchase orders
    Route::get('/purchase-orders', [PurchaseOrderController::class, 'index'])->name('purchase-orders.index');
    Route::get('/purchase-orders/create', [PurchaseOrderController::class, 'create'])->name('purchase-orders.create')->middleware('can:purchase_orders.create');
    Route::post('/purchase-orders', [PurchaseOrderController::class, 'store'])->name('purchase-orders.store')->middleware('can:purchase_orders.create');
    Route::get('/purchase-orders/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('purchase-orders.show');
    Route::get('/purchase-orders/{purchaseOrder}/pdf', [PurchaseOrderController::class, 'downloadPdf'])->name('purchase-orders.pdf');
    Route::post('/purchase-orders/{purchaseOrder}/approve', [PurchaseOrderController::class, 'approve'])->name('purchase-orders.approve')->middleware('can:purchase_orders.approve');
    Route::post('/purchase-orders/{purchaseOrder}/cancel', [PurchaseOrderController::class, 'cancel'])->name('purchase-orders.cancel')->middleware('can:purchase_orders.cancel');
    Route::post('/purchase-orders/{purchaseOrder}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive')->middleware('can:purchase_orders.receive');

    // Barcode scan lookup
    Route::get('/scan', [InventoryController::class, 'scan'])->name('scan')->middleware('can:inventory.scan');
    Route::get('/lookup-barcode', [InventoryController::class, 'lookupBarcode'])->name('lookup-barcode')->middleware('can:inventory.scan');

    // Supplier management
    Route::get('/suppliers', [SupplierController::class, 'index'])->name('suppliers.index')->middleware('can:suppliers.read');
    Route::get('/suppliers/create', [SupplierController::class, 'create'])->name('suppliers.create')->middleware('can:suppliers.create');
    Route::post('/suppliers', [SupplierController::class, 'store'])->name('suppliers.store')->middleware('can:suppliers.create');
    Route::get('/suppliers/export', [SupplierController::class, 'export'])->name('suppliers.export')->middleware('can:inventory.export');
    Route::get('/suppliers/import', [SupplierController::class, 'showImport'])->name('suppliers.import')->middleware('can:suppliers.create');
    Route::post('/suppliers/import', [SupplierController::class, 'import'])->name('suppliers.import.process')->middleware('can:suppliers.create');
    Route::get('/suppliers/import/template', [SupplierController::class, 'downloadTemplate'])->name('suppliers.import.template')->middleware('can:suppliers.create');
    Route::get('/suppliers/{supplier}', [SupplierController::class, 'show'])->name('suppliers.show')->middleware('can:suppliers.read');
    Route::get('/suppliers/{supplier}/edit', [SupplierController::class, 'edit'])->name('suppliers.edit')->middleware('can:suppliers.update');
    Route::put('/suppliers/{supplier}', [SupplierController::class, 'update'])->name('suppliers.update')->middleware('can:suppliers.update');
    Route::delete('/suppliers/{supplier}', [SupplierController::class, 'destroy'])->name('suppliers.destroy')->middleware('can:suppliers.delete');

    // Store management
    Route::get('/stores', [StoreController::class, 'index'])->name('stores.index')->middleware('can:stores.read');
    Route::get('/stores/create', [StoreController::class, 'create'])->name('stores.create')->middleware('can:stores.create');
    Route::post('/stores', [StoreController::class, 'store'])->name('stores.store')->middleware('can:stores.create');
    Route::get('/stores/{store}', [StoreController::class, 'show'])->name('stores.show')->middleware('can:stores.read');
    Route::get('/stores/{store}/edit', [StoreController::class, 'edit'])->name('stores.edit')->middleware('can:stores.update');
    Route::put('/stores/{store}', [StoreController::class, 'update'])->name('stores.update')->middleware('can:stores.update');
    Route::delete('/stores/{store}', [StoreController::class, 'destroy'])->name('stores.destroy')->middleware('can:stores.delete');

    // Department management
    Route::get('/departments', [DepartmentController::class, 'index'])->name('departments.index')->middleware('can:departments.read');
    Route::get('/departments/create', [DepartmentController::class, 'create'])->name('departments.create')->middleware('can:departments.create');
    Route::post('/departments', [DepartmentController::class, 'store'])->name('departments.store')->middleware('can:departments.create');
    Route::get('/departments/{department}', [DepartmentController::class, 'show'])->name('departments.show')->middleware('can:departments.read');
    Route::get('/departments/{department}/edit', [DepartmentController::class, 'edit'])->name('departments.edit')->middleware('can:departments.update');
    Route::put('/departments/{department}', [DepartmentController::class, 'update'])->name('departments.update')->middleware('can:departments.update');
    Route::delete('/departments/{department}', [DepartmentController::class, 'destroy'])->name('departments.destroy')->middleware('can:departments.delete');

    // API endpoint for store items and departments
    Route::get('/api/stores/{store}/items', [InventoryController::class, 'getStoreItems'])->name('api.stores.items');
    Route::get('/api/stores/{store}/departments', [DepartmentController::class, 'getDepartmentsByStore'])->name('api.stores.departments');
    Route::get('/api/generate-reference/{department}', [InventoryController::class, 'generateReference'])->name('api.generate-reference');

    // Inventory reports
    Route::get('/report', [InventoryController::class, 'report'])->name('report')->middleware('can:inventory.reports');
    Route::get('/valuation', [InventoryController::class, 'valuation'])->name('valuation')->middleware('can:inventory.reports');

    // Exports
    Route::get('/export/items', [InventoryController::class, 'exportItems'])->name('export.items')->middleware('can:inventory.export');
    Route::get('/export/adjustments', [InventoryController::class, 'exportAdjustments'])->name('export.adjustments')->middleware('can:inventory.export');
    Route::get('/export/purchase-orders', [PurchaseOrderController::class, 'export'])->name('export.purchase-orders')->middleware('can:inventory.export');

    // Stock takes
    Route::get('/stock-takes', [StockTakeController::class, 'index'])->name('stock-takes.index')->middleware('can:inventory.adjustments');
    Route::get('/stock-takes/create', [StockTakeController::class, 'create'])->name('stock-takes.create')->middleware('can:inventory.adjustments');
    Route::post('/stock-takes', [StockTakeController::class, 'store'])->name('stock-takes.store')->middleware('can:inventory.adjustments');
    Route::get('/stock-takes/{stockTake}', [StockTakeController::class, 'show'])->name('stock-takes.show')->middleware('can:inventory.adjustments');
    Route::post('/stock-takes/{stockTake}/update-item', [StockTakeController::class, 'updateItem'])->name('stock-takes.update-item')->middleware('can:inventory.adjustments');
    Route::post('/stock-takes/{stockTake}/complete', [StockTakeController::class, 'complete'])->name('stock-takes.complete')->middleware('can:inventory.adjustments');
    Route::post('/stock-takes/{stockTake}/approve', [StockTakeController::class, 'approve'])->name('stock-takes.approve')->middleware('can:inventory.adjustments');

    // Stock aging / expiry report
    Route::get('/stock-aging', [InventoryController::class, 'stockAging'])->name('stock-aging')->middleware('can:inventory.reports');

    // Barcode label printing
    Route::get('/barcode-labels', [InventoryController::class, 'barcodeLabels'])->name('barcode-labels')->middleware('can:inventory.read');

    // Mass import
    Route::get('/import', [InventoryController::class, 'showImport'])->name('import')->middleware('can:inventory.create');
    Route::post('/import', [InventoryController::class, 'importItems'])->name('import.process')->middleware('can:inventory.create');
    Route::get('/import/template', [InventoryController::class, 'downloadImportTemplate'])->name('import.template')->middleware('can:inventory.create');

    // Store Locations (bin/zone management)
    Route::get('/stores/{store}/locations', [InventoryController::class, 'locationsIndex'])->name('locations.index')->middleware('can:stores.update');
    Route::get('/stores/{store}/locations/create', [InventoryController::class, 'locationsCreate'])->name('locations.create')->middleware('can:stores.update');
    Route::post('/stores/{store}/locations', [InventoryController::class, 'locationsStore'])->name('locations.store')->middleware('can:stores.update');
    Route::get('/stores/{store}/locations/{location}/edit', [InventoryController::class, 'locationsEdit'])->name('locations.edit')->middleware('can:stores.update');
    Route::put('/stores/{store}/locations/{location}', [InventoryController::class, 'locationsUpdate'])->name('locations.update')->middleware('can:stores.update');
    Route::delete('/stores/{store}/locations/{location}', [InventoryController::class, 'locationsDestroy'])->name('locations.destroy')->middleware('can:stores.update');
    Route::get('/api/stores/{store}/locations', [InventoryController::class, 'getStoreLocations'])->name('api.stores.locations');

    // Item Returns
    Route::get('/returns', [InventoryController::class, 'returnsIndex'])->name('returns.index')->middleware('can:inventory.adjustments');
    Route::get('/returns/create', [InventoryController::class, 'returnsCreate'])->name('returns.create')->middleware('can:inventory.adjustments');
    Route::post('/returns', [InventoryController::class, 'returnsStore'])->name('returns.store')->middleware('can:inventory.adjustments');

    // Stock Alerts
    Route::get('/alerts', [InventoryController::class, 'alertsIndex'])->name('alerts.index');
    Route::post('/alerts/{alert}/resolve', [InventoryController::class, 'alertsResolve'])->name('alerts.resolve');
    Route::post('/alerts/resolve-all', [InventoryController::class, 'alertsResolveAll'])->name('alerts.resolve-all');

    // Item Photo Upload
    Route::post('/items/{item}/photo', [InventoryController::class, 'uploadPhoto'])->name('items.photo.upload')->middleware('can:inventory.update');
    Route::delete('/items/{item}/photo', [InventoryController::class, 'removePhoto'])->name('items.photo.remove')->middleware('can:inventory.update');

    // Mobile Stock Take View
    Route::get('/stock-takes/{stockTake}/mobile', [InventoryController::class, 'stockTakeMobile'])->name('stock-takes.mobile')->middleware('can:inventory.adjustments');
});

// ─── Procurement / Purchase Requests (separate from inventory dashboard gate) ───
Route::prefix('inventory/procurement')->as('inventory.procurement.')->middleware(['auth'])->group(function () {
    Route::get('/dashboard', [\Modules\Inventory\Http\Controllers\ProcurementController::class, 'dashboard'])->name('dashboard');

    Route::get('/requests', [\Modules\Inventory\Http\Controllers\PurchaseRequestController::class, 'index'])->name('requests.index');
    Route::get('/requests/create', [\Modules\Inventory\Http\Controllers\PurchaseRequestController::class, 'create'])->name('requests.create');
    Route::post('/requests', [\Modules\Inventory\Http\Controllers\PurchaseRequestController::class, 'store'])->name('requests.store');
    Route::get('/requests/{purchaseRequest}', [\Modules\Inventory\Http\Controllers\PurchaseRequestController::class, 'show'])->name('requests.show');
    Route::get('/requests/{purchaseRequest}/edit', [\Modules\Inventory\Http\Controllers\PurchaseRequestController::class, 'edit'])->name('requests.edit');
    Route::put('/requests/{purchaseRequest}', [\Modules\Inventory\Http\Controllers\PurchaseRequestController::class, 'update'])->name('requests.update');

    Route::post('/requests/{purchaseRequest}/submit', [\Modules\Inventory\Http\Controllers\ProcurementController::class, 'submit'])->name('submit');
    Route::post('/requests/{purchaseRequest}/review', [\Modules\Inventory\Http\Controllers\ProcurementController::class, 'review'])->name('review');
    Route::post('/requests/{purchaseRequest}/approve', [\Modules\Inventory\Http\Controllers\ProcurementController::class, 'approve'])->name('approve');
    Route::post('/requests/{purchaseRequest}/reject', [\Modules\Inventory\Http\Controllers\ProcurementController::class, 'reject'])->name('reject');
    Route::post('/requests/{purchaseRequest}/flag', [\Modules\Inventory\Http\Controllers\ProcurementController::class, 'flag'])->name('flag');
    Route::post('/requests/{purchaseRequest}/upload-invoice', [\Modules\Inventory\Http\Controllers\ProcurementController::class, 'uploadInvoice'])->name('upload-invoice');
    Route::post('/requests/{purchaseRequest}/convert-to-po', [\Modules\Inventory\Http\Controllers\ProcurementController::class, 'convertToPo'])->name('convert-to-po');
});
