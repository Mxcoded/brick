<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Banquet\Http\Controllers\BanquetController;

/*
|--------------------------------------------------------------------------
| Banquet Module Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for the Banquet module.
| These routes are loaded by the RouteServiceProvider within a group
| which contains the "web" middleware group.
|
*/

// Resource routes for banquet orders
Route::resource('banquet-orders', BanquetController::class)->names('banquet.orders');

// Routes for adding event days and menu items
Route::prefix('banquet-orders/{order_id}')->group(function () {
    Route::get('/add-day', [BanquetController::class, 'addDayForm'])->name('banquet.orders.add-day');
    Route::post('/store-day', [BanquetController::class, 'storeDay'])->name('banquet.orders.store-day');

    Route::prefix('{day_id}')->group(function () {
        Route::get('/add-menu-item', [BanquetController::class, 'addMenuItemForm'])->name('banquet.orders.add-menu-item');
        Route::post('/store-menu-item', [BanquetController::class, 'storeMenuItem'])->name('banquet.orders.store-menu-item');

        Route::get('/edit', [BanquetController::class, 'editDay'])->name('banquet.orders.edit-day');
        Route::put('/', [BanquetController::class, 'updateDay'])->name('banquet.orders.update-day');
        Route::patch('/status', [BanquetController::class, 'updateDayStatus'])->name('banquet.orders.update-day-status');

        Route::prefix('menu-items/{menu_item_id}')->group(function () {
            Route::get('/edit', [BanquetController::class, 'editMenuItem'])->name('banquet.orders.edit-menu-item');
            Route::put('/', [BanquetController::class, 'updateMenuItem'])->name('banquet.orders.update-menu-item');
        });
    });

    Route::get('/pdf', [BanquetController::class, 'generatePdf'])->name('banquet.orders.pdf');
    // Route::get('/edit', [BanquetController::class, 'edit'])->name('banquet.orders.edit');
    // Route::put('/', [BanquetController::class, 'update'])->name('banquet.orders.update');
});

// Datatable route
Route::get('/banquet/orders/datatable', [BanquetController::class, 'datatable'])->name('banquet.orders.datatable');
