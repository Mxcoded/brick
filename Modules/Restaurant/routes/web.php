<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;
use Modules\Restaurant\Http\Controllers\RestaurantController;
use Modules\Restaurant\Http\Controllers\WaiterAuthController;

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

Route::prefix('restaurant')->middleware(['web'])->group(function () {
    Route::get('/', [RestaurantController::class, 'index'])->name('restaurant.landing');
    Route::post('/select-source', [RestaurantController::class, 'selectSource'])->name('restaurant.select-source');

    // Explicit routes for online (no optional source to avoid slash issues)
    Route::prefix('online')->name('restaurant.online.')->group(function () {
        Route::get('menu', function (Request $request) {
            return app(RestaurantController::class)->menu('online', null, $request);
        })->name('menu');

        Route::post('cart/add', function (Request $request) {
            return app(RestaurantController::class)->addToCart($request, 'online', null);
        })->name('cart.add');

        Route::post('order/add', function (Request $request) {
            return app(RestaurantController::class)->addToOrder($request, 'online', null);
        })->name('order.add');

        Route::get('cart', function (Request $request) {
            return app(RestaurantController::class)->viewCart('online', null);
        })->name('cart');

        Route::post('cart/update', function (Request $request) {
            return app(RestaurantController::class)->updateCart($request, 'online', null);
        })->name('cart.update');

        Route::post('cart/remove', function (Request $request) {
            return app(RestaurantController::class)->removeFromCart($request, 'online', null);
        })->name('cart.remove');

        Route::post('order/submit', function (Request $request) {
            return app(RestaurantController::class)->submitOrder($request, 'online', null);
        })->name('order.submit');

        Route::get('order/confirm/{order}', function ($order, Request $request) {
            return app(RestaurantController::class)->confirmOrder('online', null, $order);
        })->name('order.confirm');
        Route::get('/getcart', [RestaurantController::class, 'getCart'])->name('cart.get');

    });
    // General route for other types
    Route::get('/{type}/{source?}/getcart', [RestaurantController::class, 'getCart'])->name('restaurant.cart.get');
    Route::get('/{type}/{source?}/menu', [RestaurantController::class, 'menu'])->name('restaurant.menu');

    // Log::info('Restaurant menu route accessed: type=' . request()->type . ', source=' . request()->source);

    Route::post('/{type}/{source?}/cart/add', [RestaurantController::class, 'addToCart'])->name('restaurant.cart.add');
    Route::post('/{type}/{source?}/order/add', [RestaurantController::class, 'addToOrder'])->name('restaurant.order.add');
    Route::get('/{type}/{source?}/cart', [RestaurantController::class, 'viewCart'])->name('restaurant.cart');
    Route::post('/{type}/{source?}/cart/update', [RestaurantController::class, 'updateCart'])->name('restaurant.cart.update');
    Route::post('/{type}/{source?}/cart/remove', [RestaurantController::class, 'removeFromCart'])->name('restaurant.cart.remove');
    Route::post('/{type}/{source?}/order/submit', [RestaurantController::class, 'submitOrder'])->name('restaurant.order.submit');
    Route::get('/{type}/{source?}/order/confirm/{order}', [RestaurantController::class, 'confirmOrder'])->name('restaurant.order.confirm');
    Route::match(['get', 'post'], '/online/orders', [RestaurantController::class, 'viewOrderHistory'])->name('restaurant.online.orders');
});

// Waiter login (no auth)
Route::prefix('restaurant-waiter')->middleware(['web'])->group(function () {
    Route::get('/login', [WaiterAuthController::class, 'showLoginForm'])->name('restaurant.waiter.login');
    Route::post('/login', [WaiterAuthController::class, 'login']);
    Route::post('/logout', [WaiterAuthController::class, 'logout'])->name('restaurant.waiter.logout');
});

Route::prefix('restaurant-waiter')->middleware(['web', 'waiter-auth', 'can:access_restaurant_dashboard'])->group(function () {
    Route::get('/dashboard', [RestaurantController::class, 'waiterDashboard'])->name('restaurant.waiter.dashboard');
    Route::get('/dashboard/data', [RestaurantController::class, 'waiterDashboardData'])->name('restaurant.waiter.dashboard.data');
    Route::post('/order/{order}/accept', [RestaurantController::class, 'acceptOrder'])->name('restaurant.waiter.accept');
    Route::post('/order/{order}/update-status', [RestaurantController::class, 'updateOrderStatus'])->name('restaurant.waiter.update-status');
    Route::post('/order/{order}/reject', [RestaurantController::class, 'rejectOrder'])->name('restaurant.waiter.reject');
    Route::post('/order/{order}/void', [RestaurantController::class, 'voidOrder'])->name('restaurant.waiter.void');

    // POS routes
    Route::post('/pos/cart/add', [RestaurantController::class, 'posAddToCart'])->name('restaurant.waiter.pos.cart.add');
    Route::post('/pos/cart/update', [RestaurantController::class, 'posUpdateCart'])->name('restaurant.waiter.pos.cart.update');
    Route::post('/pos/cart/remove', [RestaurantController::class, 'posRemoveFromCart'])->name('restaurant.waiter.pos.cart.remove');
    Route::get('/pos/cart', [RestaurantController::class, 'posGetCart'])->name('restaurant.waiter.pos.cart.get');
    Route::post('/pos/order/submit', [RestaurantController::class, 'posSubmitOrder'])->name('restaurant.waiter.pos.order.submit');

    // Shift routes
    Route::get('/shift/current', [RestaurantController::class, 'currentShift'])->name('restaurant.waiter.shift.current');
    Route::post('/shift/start', [RestaurantController::class, 'startShift'])->name('restaurant.waiter.shift.start');
    Route::post('/shift/end', [RestaurantController::class, 'endShift'])->name('restaurant.waiter.shift.end');

    // POS settings
    Route::get('/pos/settings', [RestaurantController::class, 'posSettings'])->name('restaurant.waiter.pos.settings');

    // Receipt print
    Route::get('/order/{order}/receipt', [RestaurantController::class, 'printReceipt'])->name('restaurant.waiter.receipt');

    // Payment
    Route::post('/order/{order}/pay', [RestaurantController::class, 'processPayment'])->name('restaurant.waiter.order.pay');

    // Guest lookup (charge to room)
    Route::get('/guest/lookup', [RestaurantController::class, 'guestLookup'])->name('restaurant.waiter.guest.lookup');

    // Split order
    Route::post('/order/{order}/split', [RestaurantController::class, 'splitOrder'])->name('restaurant.waiter.order.split');
});

Route::prefix('restaurant-admin')->middleware(['web', 'auth', 'can:access_restaurant_dashboard'])->group(function () {
    Route::get('/dashboard', [RestaurantController::class, 'adminDashboard'])->name('restaurant.admin.dashboard');

    // Menu Category CRUD
    Route::post('/dashboard/category/add', [RestaurantController::class, 'addMenuCategory'])->name('restaurant.admin.add-category');
    Route::get('/dashboard/category/{category}/edit', [RestaurantController::class, 'editMenuCategory'])->name('restaurant.admin.edit-category');
    Route::post('/dashboard/category/{category}/update', [RestaurantController::class, 'updateMenuCategory'])->name('restaurant.admin.update-category');
    Route::post('/dashboard/category/{category}/delete', [RestaurantController::class, 'deleteMenuCategory'])->name('restaurant.admin.delete-category');
    Route::get('/get-subcategories/{category}', [RestaurantController::class, 'getSubcategories'])->name('restaurant.admin.get-subcategories');

    // Menu Item CRUD
    Route::post('/dashboard/item/add', [RestaurantController::class, 'addMenuItem'])->name('restaurant.admin.add-item');
    Route::get('/dashboard/item/{item}/edit', [RestaurantController::class, 'editMenuItem'])->name('restaurant.admin.edit-item');
    Route::post('/dashboard/item/{item}/update', [RestaurantController::class, 'updateMenuItem'])->name('restaurant.admin.update-item');
    Route::post('/dashboard/item/{item}/delete', [RestaurantController::class, 'deleteMenuItem'])->name('restaurant.admin.delete-item');

    // Restore soft-deleted items
    Route::post('/dashboard/trashed/category/{id}/restore', [RestaurantController::class, 'restoreMenuCategory'])->name('restaurant.admin.restore-category');
    Route::post('/dashboard/trashed/item/{id}/restore', [RestaurantController::class, 'restoreMenuItem'])->name('restaurant.admin.restore-item');

    // Order Management
    Route::get('/order/{order}', [RestaurantController::class, 'showOrder'])->name('restaurant.admin.order.show');
    Route::post('/order/{order}/update', [RestaurantController::class, 'updateOrder'])->name('restaurant.admin.order.update');

    // Settings
    Route::get('/settings', [RestaurantController::class, 'adminSettings'])->name('restaurant.admin.settings');
    Route::post('/settings/update', [RestaurantController::class, 'updateSettings'])->name('restaurant.admin.settings.update');

    // Reports
    Route::get('/reports/sales', [RestaurantController::class, 'reportSales'])->name('restaurant.admin.reports.sales');
    Route::get('/reports/sales/data', [RestaurantController::class, 'salesReport'])->name('restaurant.admin.reports.sales.data');
    Route::get('/reports/sales/export', [RestaurantController::class, 'exportSalesCsv'])->name('restaurant.admin.reports.sales.export');
    Route::get('/reports/popular-items', [RestaurantController::class, 'popularItems'])->name('restaurant.admin.reports.popular');
    Route::get('/reports/popular-items/export', [RestaurantController::class, 'exportPopularCsv'])->name('restaurant.admin.reports.popular.export');
    Route::get('/reports/waiter-performance', [RestaurantController::class, 'waiterPerformance'])->name('restaurant.admin.reports.waiter');
    Route::get('/reports/shift/{shift}', [RestaurantController::class, 'shiftReport'])->name('restaurant.admin.reports.shift');

    // Kitchen Display
    Route::get('/kitchen', [RestaurantController::class, 'kdsOrders'])->name('restaurant.admin.kitchen');
    Route::get('/kitchen/data', [RestaurantController::class, 'kdsData'])->name('restaurant.admin.kitchen.data');
    Route::post('/kitchen/order/{order}/accept', [RestaurantController::class, 'kdsAcceptOrder'])->name('restaurant.admin.kitchen.accept');
    Route::post('/kitchen/order/{order}/status', [RestaurantController::class, 'kdsUpdateStatus'])->name('restaurant.admin.kitchen.status');

    // Table Management
    Route::get('/tables', [RestaurantController::class, 'tableIndex'])->name('restaurant.admin.tables');
    Route::post('/tables/store', [RestaurantController::class, 'tableStore'])->name('restaurant.admin.tables.store');
    Route::post('/tables/{table}/update', [RestaurantController::class, 'tableUpdate'])->name('restaurant.admin.tables.update');
    Route::post('/tables/{table}/delete', [RestaurantController::class, 'tableDestroy'])->name('restaurant.admin.tables.delete');

    // Stock / Inventory
    Route::get('/stock', [RestaurantController::class, 'stockIndex'])->name('restaurant.admin.stock.index');
    Route::post('/stock/store', [RestaurantController::class, 'stockStore'])->name('restaurant.admin.stock.store');
    Route::post('/stock/{stock_item}/update', [RestaurantController::class, 'stockUpdate'])->name('restaurant.admin.stock.update');
    Route::post('/stock/{stock_item}/delete', [RestaurantController::class, 'stockDestroy'])->name('restaurant.admin.stock.destroy');
    Route::post('/stock/movement', [RestaurantController::class, 'stockMovementStore'])->name('restaurant.admin.stock.movement');
    Route::post('/menu-item/{menu_item}/recipe', [RestaurantController::class, 'recipeStore'])->name('restaurant.admin.recipe.store');
    Route::post('/recipe/{recipe_item}/delete', [RestaurantController::class, 'recipeDestroy'])->name('restaurant.admin.recipe.destroy');

    // Customers
    Route::get('/customers', [RestaurantController::class, 'customerIndex'])->name('restaurant.admin.customers');
    Route::get('/customers/{customer}', [RestaurantController::class, 'customerShow'])->name('restaurant.admin.customer.show');
    Route::post('/customers/store', [RestaurantController::class, 'customerStore'])->name('restaurant.admin.customer.store');
});
