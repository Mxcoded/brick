<?php

use Illuminate\Support\Facades\Route;
use Modules\Banquet\Http\Controllers\BanquetController;

/*
|--------------------------------------------------------------------------
| Banquet Module Web Routes
|--------------------------------------------------------------------------
| Pattern: /banquet/...
| Name Prefix: banquet.
*/

Route::prefix('banquet')
    ->middleware(['web', 'auth', 'can:access_banquet_dashboard'])
    ->name('banquet.')
    ->group(function () {

        // ==========================================================
        // 1. DASHBOARD & REPORTING (Read Access)
        // ==========================================================

        // Main Dashboard (Index)
        Route::get('/', [BanquetController::class, 'index'])->name('index'); // URL: /banquet

        // Reports
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/form', [BanquetController::class, 'eventReportForm'])->name('form');
            Route::post('/generate', [BanquetController::class, 'generateEventReport'])->name('generate');
        });

        // ==========================================================
        // 2. ORDER MANAGEMENT
        // ==========================================================

        // Datatable Source
        Route::get('/orders/datatable', [BanquetController::class, 'datatable'])->name('orders.datatable');

        // EXPLICIT DELETE ROUTE (Fixes the "Failed to delete" bug)
        // Must be defined BEFORE the resource to prevent conflict
        Route::delete('/orders/{order_id}', [BanquetController::class, 'destroy'])
            ->name('orders.destroy')
            ->middleware('can:manage_banquet');

        // Standard Resource Routes (Index, Create, Store, Show, Edit, Update)
        // We override the parameter name to 'order_id' to match your Controller variables
        Route::resource('orders', BanquetController::class)
            ->names('orders')
            ->parameters(['orders' => 'order_id'])
            ->except(['destroy']) // Exclude destroy as we defined it above
            ->middleware([
                'create' => 'can:manage_banquet',
                'store'  => 'can:manage_banquet',
                'edit'   => 'can:manage_banquet',
                'update' => 'can:manage_banquet',
            ]);

        // ==========================================================
        // 3. NESTED EVENT OPERATIONS (Days, Menus, PDFs)
        // URL: /banquet/orders/{order_id}/...
        // ==========================================================
        Route::prefix('orders/{order_id}')->name('orders.')->group(function () {

            // Function Sheet PDF (View Access)
            Route::get('/pdf', [BanquetController::class, 'generatePdf'])->name('pdf');
            // Invoice PDF (View Access)
            Route::get('/invoice', [BanquetController::class, 'generateInvoice'])->name('invoice');

            // --- WRITE OPERATIONS (Protected by manage_banquet) ---
            Route::middleware(['can:manage_banquet'])->group(function () {
                //PAYMENTS actions
                Route::post('/payment', [BanquetController::class, 'storePayment'])->name('payment.store');
                Route::delete('/payment/{payment_id}', [BanquetController::class, 'destroyPayment'])->name('payment.destroy');
                // Add Event Days
                Route::get('/add-day', [BanquetController::class, 'addDayForm'])->name('add-day');
                Route::post('/store-day', [BanquetController::class, 'storeDay'])->name('store-day');

                // Day-Specific Operations
                Route::prefix('days/{day_id}')->group(function () {

                    // Day Management
                    Route::get('/edit', [BanquetController::class, 'editDay'])->name('edit-day');
                    Route::put('/', [BanquetController::class, 'updateDay'])->name('update-day');
                    Route::delete('/', [BanquetController::class, 'destroyDay'])->name('event-days.destroy');
                    Route::patch('/status', [BanquetController::class, 'updateDayStatus'])->name('update-day-status');

                    // Menu Items
                    Route::get('/add-menu', [BanquetController::class, 'addMenuItemForm'])->name('add-menu-item');
                    Route::post('/store-menu', [BanquetController::class, 'storeMenuItem'])->name('store-menu-item');

                    // Specific Menu Item Operations
                    Route::prefix('items/{menu_item_id}')->group(function () {
                        Route::get('/edit', [BanquetController::class, 'editMenuItem'])->name('edit-menu-item');
                        Route::put('/', [BanquetController::class, 'updateMenuItem'])->name('update-menu-item');
                        Route::delete('/', [BanquetController::class, 'deleteMenuItem'])->name('menu-item.destroy');
                    });
                });

                // Legacy support for any views still using these named routes
                Route::get('event-days/{day_id}/edit', [BanquetController::class, 'editDay'])->name('event-days.edit');
                Route::put('event-days/{day_id}', [BanquetController::class, 'updateDay'])->name('event-days.update');
            });

            // View Day Details (Read Access)
            Route::get('days/{day_id}', [BanquetController::class, 'showDay'])->name('event-days.show');
        });
    });
