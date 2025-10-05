<?php

use Illuminate\Support\Facades\Route;
use Modules\Restaurant\Http\Controllers\RestaurantController;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;

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

Route::prefix('restaurant-waiter')->middleware(['web'])->group(function () {
    Route::get('/dashboard', [RestaurantController::class, 'waiterDashboard'])->name('restaurant.waiter.dashboard');
    Route::post('/order/{order}/accept', [RestaurantController::class, 'acceptOrder'])->name('restaurant.waiter.accept');
    Route::post('/order/{order}/update-status', [RestaurantController::class, 'updateOrderStatus'])->name('restaurant.waiter.update-status');
    Route::post('/order/{order}/reject', [RestaurantController::class, 'rejectOrder'])->name('restaurant.waiter.reject');
    Route::post('/order/{order}/void', [RestaurantController::class, 'voidOrder'])->name('restaurant.waiter.void');
});

Route::prefix('restaurant-admin')->middleware(['web', 'auth'])->group(function () {
    Route::get('/dashboard', [RestaurantController::class, 'adminDashboard'])->name('dashboard');

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

    // Order Management
    Route::post('/order/{order}/update', [RestaurantController::class, 'updateOrder'])->name('restaurant.admin.order.update');
});
