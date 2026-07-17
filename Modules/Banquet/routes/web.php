<?php

use Illuminate\Support\Facades\Route;
use Modules\Banquet\Http\Controllers\BanquetController;
use Modules\Banquet\Http\Controllers\CustomerController;
use Modules\Banquet\Http\Controllers\EnquiryController;
use Modules\Banquet\Http\Controllers\EventLeadController;
use Modules\Banquet\Http\Controllers\LeadEventController;

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
            Route::post('/export', [BanquetController::class, 'exportEventReport'])->name('export');
        });

        // ==========================================================
        // CUSTOMER MANAGEMENT
        // ==========================================================
        Route::prefix('customers')->name('customers.')->group(function () {
            Route::get('/', [CustomerController::class, 'index'])->name('index');
            Route::get('/datatable', [CustomerController::class, 'datatable'])->name('datatable');
            Route::get('/export', [CustomerController::class, 'export'])->name('export');
            Route::get('/create', [CustomerController::class, 'create'])->name('create')->middleware('can:banquet.create');
            Route::post('/', [CustomerController::class, 'store'])->name('store')->middleware('can:banquet.create');
            Route::get('/{id}', [CustomerController::class, 'show'])->name('show');
            Route::get('/{id}/edit', [CustomerController::class, 'edit'])->name('edit')->middleware('can:banquet.update');
            Route::put('/{id}', [CustomerController::class, 'update'])->name('update')->middleware('can:banquet.update');
            Route::delete('/{id}', [CustomerController::class, 'destroy'])->name('destroy')->middleware('can:banquet.delete');
        });

        // ==========================================================
        // ENQUIRY MANAGEMENT
        // ==========================================================
        Route::prefix('enquiries')->name('enquiries.')->group(function () {
            Route::get('/', [EnquiryController::class, 'index'])->name('index');
            Route::get('/datatable', [EnquiryController::class, 'datatable'])->name('datatable');
            Route::get('/{id}', [EnquiryController::class, 'show'])->name('show');
            Route::patch('/{id}/status', [EnquiryController::class, 'updateStatus'])->name('update-status')->middleware('can:banquet.update');
            Route::put('/{id}/notes', [EnquiryController::class, 'updateNotes'])->name('update-notes')->middleware('can:banquet.update');
            Route::post('/{id}/convert', [EnquiryController::class, 'convertToOrder'])->name('convert')->middleware('can:banquet.create');
            Route::delete('/{id}', [EnquiryController::class, 'destroy'])->name('destroy')->middleware('can:banquet.delete');
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
            ->middleware('can:banquet.delete');

        // Standard Resource Routes (Index, Create, Store, Show, Edit, Update)
        // We override the parameter name to 'order_id' to match your Controller variables
        Route::resource('orders', BanquetController::class)
            ->names('orders')
            ->parameters(['orders' => 'order_id'])
            ->except(['destroy']) // Exclude destroy as we defined it above
            ->middleware([
                'create' => 'can:banquet.create',
                'store' => 'can:banquet.create',
                'edit' => 'can:banquet.update',
                'update' => 'can:banquet.update',
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

            // --- PAYMENTS ---
            Route::post('/payment', [BanquetController::class, 'storePayment'])->name('payment.store')->middleware('can:banquet.create');
            Route::delete('/payment/{payment_id}', [BanquetController::class, 'destroyPayment'])->name('payment.destroy')->middleware('can:banquet.delete');

            // --- ADD EVENT DAYS ---
            Route::get('/add-day', [BanquetController::class, 'addDayForm'])->name('add-day')->middleware('can:banquet.create');
            Route::post('/store-day', [BanquetController::class, 'storeDay'])->name('store-day')->middleware('can:banquet.create');

            // --- DAY-SPECIFIC OPERATIONS ---
            Route::prefix('days/{day_id}')->group(function () {
                Route::get('/edit', [BanquetController::class, 'editDay'])->name('edit-day')->middleware('can:banquet.update');
                Route::put('/', [BanquetController::class, 'updateDay'])->name('update-day')->middleware('can:banquet.update');
                Route::delete('/', [BanquetController::class, 'destroyDay'])->name('event-days.destroy')->middleware('can:banquet.delete');
                Route::patch('/status', [BanquetController::class, 'updateDayStatus'])->name('update-day-status')->middleware('can:banquet.update');

                // Menu Items
                Route::get('/add-menu', [BanquetController::class, 'addMenuItemForm'])->name('add-menu-item')->middleware('can:banquet.create');
                Route::post('/store-menu', [BanquetController::class, 'storeMenuItem'])->name('store-menu-item')->middleware('can:banquet.create');

                // Specific Menu Item Operations
                Route::prefix('items/{menu_item_id}')->group(function () {
                    Route::get('/edit', [BanquetController::class, 'editMenuItem'])->name('edit-menu-item')->middleware('can:banquet.update');
                    Route::put('/', [BanquetController::class, 'updateMenuItem'])->name('update-menu-item')->middleware('can:banquet.update');
                    Route::delete('/', [BanquetController::class, 'deleteMenuItem'])->name('menu-item.destroy')->middleware('can:banquet.delete');
                });
            });

            // Legacy support for any views still using these named routes
            Route::get('event-days/{day_id}/edit', [BanquetController::class, 'editDay'])->name('event-days.edit')->middleware('can:banquet.update');
            Route::put('event-days/{day_id}', [BanquetController::class, 'updateDay'])->name('event-days.update')->middleware('can:banquet.update');

            // View Day Details (Read Access)
            Route::get('days/{day_id}', [BanquetController::class, 'showDay'])->name('event-days.show');
        });

        // ==========================================================
        // LEAD EVENTS (Admin-managed event campaigns)
        // ==========================================================
        Route::prefix('lead-events')->name('lead-events.')->group(function () {
            Route::get('/', [LeadEventController::class, 'index'])->name('index');
            Route::get('/create', [LeadEventController::class, 'create'])->name('create');
            Route::post('/', [LeadEventController::class, 'store'])->name('store');
            Route::get('/{id}/edit', [LeadEventController::class, 'edit'])->name('edit');
            Route::put('/{id}', [LeadEventController::class, 'update'])->name('update');
            Route::get('/{id}/qrcode', [LeadEventController::class, 'qrcode'])->name('qrcode');
            Route::delete('/{id}', [LeadEventController::class, 'destroy'])->name('destroy');
        });

        // ==========================================================
        // EVENT LEAD MANAGEMENT (Leads submitted via public forms)
        // ==========================================================
        Route::prefix('event-leads')->name('event-leads.')->group(function () {
            Route::get('/', [EventLeadController::class, 'index'])->name('index');
            Route::get('/datatable', [EventLeadController::class, 'datatable'])->name('datatable');
            Route::get('/export', [EventLeadController::class, 'export'])->name('export');
            Route::post('/clean-duplicates', [EventLeadController::class, 'cleanDuplicates'])->name('clean-duplicates');
            Route::get('/{id}', [EventLeadController::class, 'show'])->name('show');
            Route::patch('/{id}/status', [EventLeadController::class, 'updateStatus'])->name('update-status');
            Route::put('/{id}/notes', [EventLeadController::class, 'updateNotes'])->name('update-notes');
            Route::delete('/{id}', [EventLeadController::class, 'destroy'])->name('destroy');
        });
    });
