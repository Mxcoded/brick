<?php

use Illuminate\Support\Facades\Route;
use Modules\Inventory\Http\Controllers\InventoryController;
use Modules\Inventory\Http\Controllers\SupplierController;
use Modules\Inventory\Http\Controllers\StoreController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::group(['prefix' => 'inventory', 'as' => 'inventory.'], function () {
    // Items management and dashboard
    Route::get('/', [InventoryController::class, 'index'])->name('index');
    Route::resource('items', InventoryController::class);
    Route::post('/items/restock', [InventoryController::class, 'restock'])->name('items.restock');

    // Item transfers
    Route::post('/transfer', [InventoryController::class, 'transferItems'])->name('transfer');
    Route::get('/transfers', function () {
        $stores = \Modules\Inventory\Models\Store::all();
        $items = \Modules\Inventory\Models\Item::all();
        return view('inventory::transfer', compact('stores', 'items'));
    })->name('transfers.index');

    // Item usage
    Route::get('/usage', [InventoryController::class, 'usage'])->name('usage');
    Route::post('/usage/store', [InventoryController::class, 'recordUsage'])->name('usage.store');

    // Supplier management
    Route::resource('suppliers', SupplierController::class)->names('suppliers');

    // Store management
    Route::resource('stores', StoreController::class)->names('stores');

    // API endpoint for store items
    Route::get('/api/stores/{store}/items', [InventoryController::class, 'getStoreItems'])->name('api.stores.items');

    // Inventory reports
    Route::get('/report', [InventoryController::class, 'report'])->name('report');
});
