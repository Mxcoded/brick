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

// Routes for adding event days, menu items, and event day CRUD
Route::prefix('banquet-orders/{order_id}')->group(function () {
    // Add event day routes
    Route::get('/add-day', [BanquetController::class, 'addDayForm'])->name('banquet.orders.add-day');
    Route::post('/store-day', [BanquetController::class, 'storeDay'])->name('banquet.orders.store-day');

    // Nested routes for a specific event day
    Route::prefix('{day_id}')->group(function () {
        Route::get('/add-menu-item', [BanquetController::class, 'addMenuItemForm'])->name('banquet.orders.add-menu-item');
        Route::post('/store-menu-item', [BanquetController::class, 'storeMenuItem'])->name('banquet.orders.store-menu-item');
        Route::get('/edit', [BanquetController::class, 'editDay'])->name('banquet.orders.edit-day');
        Route::put('/', [BanquetController::class, 'updateDay'])->name('banquet.orders.update-day');
        Route::patch('/status', [BanquetController::class, 'updateDayStatus'])->name('banquet.orders.update-day-status');

        // Nested routes for menu items
        Route::prefix('menu-items/{menu_item_id}')->group(function () {
            Route::get('/edit', [BanquetController::class, 'editMenuItem'])->name('banquet.orders.edit-menu-item');
            Route::put('/', [BanquetController::class, 'updateMenuItem'])->name('banquet.orders.update-menu-item');
            Route::delete('/', [BanquetController::class, 'destroyMenuItem'])->name('banquet.orders.menu-item.destroy');
        });
    });

    // Event day CRUD routes (corrected)
    Route::get('event-days/{day_id}', [BanquetController::class, 'showDay'])->name('banquet.orders.event-days.show');
    Route::get('event-days/{day_id}/edit', [BanquetController::class, 'editDay'])->name('banquet.orders.event-days.edit');
    Route::put('event-days/{day_id}', [BanquetController::class, 'updateDay'])->name('banquet.orders.event-days.update');
    Route::delete('event-days/{day_id}', [BanquetController::class, 'destroyDay'])->name('banquet.orders.event-days.destroy');

    // PDF generation route
    Route::get('/pdf', [BanquetController::class, 'generatePdf'])->name('banquet.orders.pdf');
});

// Datatable route
Route::get('/banquet/orders/datatable', [BanquetController::class, 'datatable'])->name('banquet.orders.datatable');
