<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Modules\Restaurant\Http\Controllers\RestaurantController;
use Modules\Restaurant\Http\Controllers\WaiterAuthController;
use Modules\Restaurant\Http\Middleware\AuthorizePublicOrderSource;
use Modules\Restaurant\Support\RestaurantPermission;

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

    // Explicit routes for online (no optional source to avoid slash issues).
    // Cart mutation and order submission are throttled per session: these are
    // unauthenticated write endpoints, and without a limit one caller can fill
    // a menu item's stock or a menu of orders at a machine-gun rate.
    Route::prefix('online')->name('restaurant.online.')->group(function () {
        Route::get('menu', function (Request $request) {
            return app(RestaurantController::class)->menu('online', null, $request);
        })->name('menu');

        Route::post('cart/add', function (Request $request) {
            return app(RestaurantController::class)->addToCart($request, 'online', null);
        })->middleware('throttle:menu-cart')->name('cart.add');

        Route::post('order/add', function (Request $request) {
            return app(RestaurantController::class)->addToOrder($request, 'online', null);
        })->middleware('throttle:menu-cart')->name('order.add');

        Route::get('cart', function (Request $request) {
            return app(RestaurantController::class)->viewCart('online', null);
        })->name('cart');

        Route::post('cart/update', function (Request $request) {
            return app(RestaurantController::class)->updateCart($request, 'online', null);
        })->middleware('throttle:menu-cart')->name('cart.update');

        Route::post('cart/remove', function (Request $request) {
            return app(RestaurantController::class)->removeFromCart($request, 'online', null);
        })->middleware('throttle:menu-cart')->name('cart.remove');

        Route::post('order/submit', function (Request $request) {
            return app(RestaurantController::class)->submitOrder($request, 'online', null);
        })->middleware('throttle:menu-order-submit')->name('order.submit');

        Route::get('order/confirm/{order}', function ($order, Request $request) {
            return app(RestaurantController::class)->confirmOrder('online', null, $order);
        })->name('order.confirm');
        Route::get('/getcart', [RestaurantController::class, 'getCart'])->name('cart.get');

    });
    // Shared entry points for the three order sources. AuthorizePublicOrderSource
    // is what keeps them apart: online stays open, room is limited to the
    // checked-in guest's own room, and tables are staff-only on the terminal.
    Route::middleware(AuthorizePublicOrderSource::class)->group(function () {
        Route::get('/{type}/{source?}/getcart', [RestaurantController::class, 'getCart'])->name('restaurant.cart.get');
        Route::get('/{type}/{source?}/menu', [RestaurantController::class, 'menu'])->name('restaurant.menu');

        Route::post('/{type}/{source?}/cart/add', [RestaurantController::class, 'addToCart'])->middleware('throttle:menu-cart')->name('restaurant.cart.add');
        Route::post('/{type}/{source?}/order/add', [RestaurantController::class, 'addToOrder'])->middleware('throttle:menu-cart')->name('restaurant.order.add');
        Route::get('/{type}/{source?}/cart', [RestaurantController::class, 'viewCart'])->name('restaurant.cart');
        Route::post('/{type}/{source?}/cart/update', [RestaurantController::class, 'updateCart'])->middleware('throttle:menu-cart')->name('restaurant.cart.update');
        Route::post('/{type}/{source?}/cart/remove', [RestaurantController::class, 'removeFromCart'])->middleware('throttle:menu-cart')->name('restaurant.cart.remove');
        Route::post('/{type}/{source?}/order/submit', [RestaurantController::class, 'submitOrder'])->middleware('throttle:menu-order-submit')->name('restaurant.order.submit');
        Route::get('/{type}/{source?}/order/confirm/{order}', [RestaurantController::class, 'confirmOrder'])->name('restaurant.order.confirm');
    });

    // The order-history page is a plain, shareable form. The lookup it submits
    // to is POST-only and throttled tightly, so the customer's phone number
    // never rides in a URL. Only orders this session placed are returned; the
    // controller enforces that part.
    Route::get('/online/orders', [RestaurantController::class, 'viewOrderHistory'])->name('restaurant.online.orders');
    Route::post('/online/orders', [RestaurantController::class, 'viewOrderHistory'])->middleware('throttle:menu-order-lookup')->name('restaurant.online.orders.lookup');
});

// Waiter login (no auth)
Route::prefix('restaurant-waiter')->middleware(['web'])->group(function () {
    Route::get('/login', [WaiterAuthController::class, 'showLoginForm'])->name('restaurant.waiter.login');
    Route::post('/login', [WaiterAuthController::class, 'login']);
    Route::post('/logout', [WaiterAuthController::class, 'logout'])->name('restaurant.waiter.logout');
});

// Waiter terminal. The POS routes below run on the operational capabilities a
// server already has in the config map, so serving a table is unaffected by the
// split. Refund is the exception: it moves money and reverses a ledger entry,
// so it needs payments.refund, which the waiter role is not granted.
Route::prefix('restaurant-waiter')->middleware(['web', 'waiter-auth', 'can:access_restaurant_dashboard'])->group(function () {
    Route::get('/dashboard', [RestaurantController::class, 'waiterDashboard'])->middleware('can:'.RestaurantPermission::ORDERS_READ)->name('restaurant.waiter.dashboard');
    Route::post('/order/{order}/accept', [RestaurantController::class, 'acceptOrder'])->middleware('can:'.RestaurantPermission::ORDERS_ACCEPT)->name('restaurant.waiter.accept');
    Route::post('/order/{order}/update-status', [RestaurantController::class, 'updateOrderStatus'])->middleware('can:'.RestaurantPermission::ORDERS_UPDATE)->name('restaurant.waiter.update-status');
    Route::post('/order/{order}/reject', [RestaurantController::class, 'rejectOrder'])->middleware('can:'.RestaurantPermission::ORDERS_REJECT)->name('restaurant.waiter.reject');
    Route::post('/order/{order}/void', [RestaurantController::class, 'voidOrder'])->middleware('can:'.RestaurantPermission::ORDERS_VOID)->name('restaurant.waiter.void');

    // POS routes. The cart is per-session scratch space, so it is cheap to
    // rebuild; submitting is rate limited so a wedged or scripted client cannot
    // create a stream of real orders.
    Route::post('/pos/cart/add', [RestaurantController::class, 'posAddToCart'])->middleware(['can:'.RestaurantPermission::ORDERS_CREATE, 'throttle:pos-cart'])->name('restaurant.waiter.pos.cart.add');
    Route::post('/pos/cart/update', [RestaurantController::class, 'posUpdateCart'])->middleware(['can:'.RestaurantPermission::ORDERS_CREATE, 'throttle:pos-cart'])->name('restaurant.waiter.pos.cart.update');
    Route::post('/pos/cart/remove', [RestaurantController::class, 'posRemoveFromCart'])->middleware(['can:'.RestaurantPermission::ORDERS_CREATE, 'throttle:pos-cart'])->name('restaurant.waiter.pos.cart.remove');
    Route::get('/pos/cart', [RestaurantController::class, 'posGetCart'])->middleware('can:'.RestaurantPermission::ORDERS_CREATE)->name('restaurant.waiter.pos.cart.get');
    Route::post('/pos/order/submit', [RestaurantController::class, 'posSubmitOrder'])->middleware(['can:'.RestaurantPermission::ORDERS_CREATE, 'throttle:pos-order-submit'])->name('restaurant.waiter.pos.order.submit');
    Route::get('/pos/orders-data', [RestaurantController::class, 'posOrderData'])->middleware('can:'.RestaurantPermission::ORDERS_READ)->name('restaurant.waiter.pos.orders-data');

    // Shift routes
    Route::get('/shift/current', [RestaurantController::class, 'currentShift'])->middleware('can:'.RestaurantPermission::ORDERS_READ)->name('restaurant.waiter.shift.current');
    Route::post('/shift/start', [RestaurantController::class, 'startShift'])->middleware('can:'.RestaurantPermission::ORDERS_CREATE)->name('restaurant.waiter.shift.start');
    Route::post('/shift/end', [RestaurantController::class, 'endShift'])->middleware('can:'.RestaurantPermission::ORDERS_UPDATE)->name('restaurant.waiter.shift.end');

    // POS settings. This is the read-only pricing config the terminal needs to
    // price an order at all (VAT, service charge, discount ceiling, shift hours).
    // It is deliberately not behind settings.read, which is the manager-level
    // admin screen.
    Route::get('/pos/settings', [RestaurantController::class, 'posSettings'])->middleware('can:'.RestaurantPermission::ORDERS_READ)->name('restaurant.waiter.pos.settings');

    // Receipt print
    Route::get('/order/{order}/receipt', [RestaurantController::class, 'printReceipt'])->middleware('can:'.RestaurantPermission::ORDERS_READ)->name('restaurant.waiter.receipt');

    // Payment. Both move real money, so they are throttled on top of the
    // capability check. The controller also refuses order types the terminal
    // does not serve, which route binding alone would not.
    Route::post('/order/{order}/pay', [RestaurantController::class, 'processPayment'])->middleware(['can:'.RestaurantPermission::PAYMENTS_CREATE, 'throttle:pos-payment'])->name('restaurant.waiter.order.pay');
    Route::post('/order/{order}/refund', [RestaurantController::class, 'refundPayment'])->middleware(['can:'.RestaurantPermission::PAYMENTS_REFUND, 'throttle:pos-payment'])->name('restaurant.waiter.order.refund');
});

// Back office. access_restaurant_dashboard only decides whether the module
// shows up at all; every route below also names the specific capability it
// needs, so holding dashboard access no longer implies full control of the
// menu, floor plan, stock, customers, settings and reports.
Route::prefix('restaurant-admin')->middleware(['web', 'auth', 'can:access_restaurant_dashboard'])->group(function () {
    Route::get('/dashboard', [RestaurantController::class, 'adminDashboard'])
        ->middleware('can:'.RestaurantPermission::DASHBOARD_ACCESS)
        ->name('restaurant.admin.dashboard');

    // Menu Category CRUD
    Route::post('/dashboard/category/add', [RestaurantController::class, 'addMenuCategory'])->middleware('can:'.RestaurantPermission::MENU_CATEGORIES_CREATE)->name('restaurant.admin.add-category');
    Route::get('/dashboard/category/{category}/edit', [RestaurantController::class, 'editMenuCategory'])->middleware('can:'.RestaurantPermission::MENU_CATEGORIES_UPDATE)->name('restaurant.admin.edit-category');
    Route::post('/dashboard/category/{category}/update', [RestaurantController::class, 'updateMenuCategory'])->middleware('can:'.RestaurantPermission::MENU_CATEGORIES_UPDATE)->name('restaurant.admin.update-category');
    Route::post('/dashboard/category/{category}/delete', [RestaurantController::class, 'deleteMenuCategory'])->middleware('can:'.RestaurantPermission::MENU_CATEGORIES_DELETE)->name('restaurant.admin.delete-category');
    Route::get('/get-subcategories/{category}', [RestaurantController::class, 'getSubcategories'])->middleware('can:'.RestaurantPermission::MENU_CATEGORIES_READ)->name('restaurant.admin.get-subcategories');

    // Menu Item CRUD
    Route::post('/dashboard/item/add', [RestaurantController::class, 'addMenuItem'])->middleware('can:'.RestaurantPermission::MENU_ITEMS_CREATE)->name('restaurant.admin.add-item');
    Route::get('/dashboard/item/{item}/edit', [RestaurantController::class, 'editMenuItem'])->middleware('can:'.RestaurantPermission::MENU_ITEMS_UPDATE)->name('restaurant.admin.edit-item');
    Route::post('/dashboard/item/{item}/update', [RestaurantController::class, 'updateMenuItem'])->middleware('can:'.RestaurantPermission::MENU_ITEMS_UPDATE)->name('restaurant.admin.update-item');
    Route::post('/dashboard/item/{item}/delete', [RestaurantController::class, 'deleteMenuItem'])->middleware('can:'.RestaurantPermission::MENU_ITEMS_DELETE)->name('restaurant.admin.delete-item');

    // Restore soft-deleted items
    Route::post('/dashboard/trashed/category/{id}/restore', [RestaurantController::class, 'restoreMenuCategory'])->middleware('can:'.RestaurantPermission::MENU_CATEGORIES_RESTORE)->name('restaurant.admin.restore-category');
    Route::post('/dashboard/trashed/item/{id}/restore', [RestaurantController::class, 'restoreMenuItem'])->middleware('can:'.RestaurantPermission::MENU_ITEMS_RESTORE)->name('restaurant.admin.restore-item');

    // Order Management
    Route::get('/order/{order}', [RestaurantController::class, 'showOrder'])->middleware('can:'.RestaurantPermission::ORDERS_READ)->name('restaurant.admin.order.show');
    Route::post('/order/{order}/update', [RestaurantController::class, 'updateOrder'])->middleware('can:'.RestaurantPermission::ORDERS_UPDATE)->name('restaurant.admin.order.update');

    // Settings. Changing tax or service charge rewrites what every future
    // order means, so reading and writing are split from the rest.
    Route::get('/settings', [RestaurantController::class, 'adminSettings'])->middleware('can:'.RestaurantPermission::SETTINGS_READ)->name('restaurant.admin.settings');
    Route::post('/settings/update', [RestaurantController::class, 'updateSettings'])->middleware('can:'.RestaurantPermission::SETTINGS_UPDATE)->name('restaurant.admin.settings.update');

    // Reports. Reading a summary and exporting the whole dataset are separate
    // capabilities; a CSV carries every row, the screen does not.
    Route::get('/reports/sales', [RestaurantController::class, 'reportSales'])->middleware('can:'.RestaurantPermission::REPORTS_SALES_READ)->name('restaurant.admin.reports.sales');
    Route::get('/reports/sales/data', [RestaurantController::class, 'salesReport'])->middleware('can:'.RestaurantPermission::REPORTS_SALES_READ)->name('restaurant.admin.reports.sales.data');
    Route::get('/reports/sales/export', [RestaurantController::class, 'exportSalesCsv'])->middleware('can:'.RestaurantPermission::REPORTS_SALES_EXPORT)->name('restaurant.admin.reports.sales.export');
    Route::get('/reports/popular-items', [RestaurantController::class, 'popularItems'])->middleware('can:'.RestaurantPermission::REPORTS_POPULAR_ITEMS_READ)->name('restaurant.admin.reports.popular');
    Route::get('/reports/popular-items/export', [RestaurantController::class, 'exportPopularCsv'])->middleware('can:'.RestaurantPermission::REPORTS_POPULAR_ITEMS_EXPORT)->name('restaurant.admin.reports.popular.export');
    Route::get('/reports/waiter-performance', [RestaurantController::class, 'waiterPerformance'])->middleware('can:'.RestaurantPermission::REPORTS_WAITER_PERFORMANCE_READ)->name('restaurant.admin.reports.waiter');
    Route::get('/reports/shift/{shift}', [RestaurantController::class, 'shiftReport'])->middleware('can:'.RestaurantPermission::REPORTS_SHIFT_READ)->name('restaurant.admin.reports.shift');

    // Kitchen Display
    Route::get('/kitchen', [RestaurantController::class, 'kdsOrders'])->middleware('can:'.RestaurantPermission::KITCHEN_READ)->name('restaurant.admin.kitchen');
    Route::get('/kitchen/data', [RestaurantController::class, 'kdsData'])->middleware('can:'.RestaurantPermission::KITCHEN_READ)->name('restaurant.admin.kitchen.data');
    Route::post('/kitchen/order/{order}/accept', [RestaurantController::class, 'kdsAcceptOrder'])->middleware('can:'.RestaurantPermission::KITCHEN_UPDATE)->name('restaurant.admin.kitchen.accept');

    // Table Management
    Route::get('/tables', [RestaurantController::class, 'tableIndex'])->middleware('can:'.RestaurantPermission::TABLES_READ)->name('restaurant.admin.tables');
    Route::post('/tables/store', [RestaurantController::class, 'tableStore'])->middleware('can:'.RestaurantPermission::TABLES_CREATE)->name('restaurant.admin.tables.store');
    Route::post('/tables/{table}/update', [RestaurantController::class, 'tableUpdate'])->middleware('can:'.RestaurantPermission::TABLES_UPDATE)->name('restaurant.admin.tables.update');
    Route::post('/tables/{table}/delete', [RestaurantController::class, 'tableDestroy'])->middleware('can:'.RestaurantPermission::TABLES_DELETE)->name('restaurant.admin.tables.delete');

    // Stock / Inventory
    Route::get('/stock', [RestaurantController::class, 'stockIndex'])->middleware('can:'.RestaurantPermission::STOCK_READ)->name('restaurant.admin.stock.index');
    Route::post('/stock/store', [RestaurantController::class, 'stockStore'])->middleware('can:'.RestaurantPermission::STOCK_CREATE)->name('restaurant.admin.stock.store');
    Route::post('/stock/{stock_item}/update', [RestaurantController::class, 'stockUpdate'])->middleware('can:'.RestaurantPermission::STOCK_UPDATE)->name('restaurant.admin.stock.update');
    Route::post('/stock/{stock_item}/delete', [RestaurantController::class, 'stockDestroy'])->middleware('can:'.RestaurantPermission::STOCK_DELETE)->name('restaurant.admin.stock.destroy');
    Route::post('/stock/movement', [RestaurantController::class, 'stockMovementStore'])->middleware('can:'.RestaurantPermission::STOCK_MOVEMENTS)->name('restaurant.admin.stock.movement');
    Route::post('/menu-item/{menu_item}/recipe', [RestaurantController::class, 'recipeStore'])->middleware('can:'.RestaurantPermission::RECIPES_CREATE)->name('restaurant.admin.recipe.store');
    Route::post('/recipe/{recipe_item}/delete', [RestaurantController::class, 'recipeDestroy'])->middleware('can:'.RestaurantPermission::RECIPES_DELETE)->name('restaurant.admin.recipe.destroy');

    // Customers
    Route::get('/customers', [RestaurantController::class, 'customerIndex'])->middleware('can:'.RestaurantPermission::CUSTOMERS_READ)->name('restaurant.admin.customers');
    Route::get('/customers/{customer}', [RestaurantController::class, 'customerShow'])->middleware('can:'.RestaurantPermission::CUSTOMERS_READ)->name('restaurant.admin.customer.show');
    Route::post('/customers/store', [RestaurantController::class, 'customerStore'])->middleware('can:'.RestaurantPermission::CUSTOMERS_CREATE)->name('restaurant.admin.customer.store');
});
