<?php

use Illuminate\Support\Facades\Route;
use Modules\Restaurant\Http\Controllers\RestaurantController;

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
    Route::post('/select-table', [RestaurantController::class, 'selectTable'])->name('restaurant.select-table');
    Route::get('/table/{table}/menu', [RestaurantController::class, 'menu'])->name('restaurant.menu');
    Route::post('/table/{table}/cart/add', [RestaurantController::class, 'addToCart'])->name('restaurant.cart.add');
    Route::get('/table/{table}/cart', [RestaurantController::class, 'viewCart'])->name('restaurant.cart');
    Route::post('/table/{table}/cart/update', [RestaurantController::class, 'updateCart'])->name('restaurant.cart.update');
    Route::post('/table/{table}/cart/remove', [RestaurantController::class, 'removeFromCart'])->name('restaurant.cart.remove');
    Route::post('/table/{table}/order/submit', [RestaurantController::class, 'submitOrder'])->name('restaurant.order.submit');
    Route::get('/table/{table}/order/confirm/{order}', [RestaurantController::class, 'confirmOrder'])->name('restaurant.order.confirm');
});
Route::prefix('restaurant-waiter')->middleware(['web'])->group(function () {
    Route::get('/dashboard', [RestaurantController::class, 'waiterDashboard'])->name('restaurant.waiter.dashboard');
    Route::post('/order/{order}/accept', [RestaurantController::class, 'acceptOrder'])->name('restaurant.waiter.accept');
});

Route::prefix('restaurant-admin')->middleware(['web'])->group(function () {
    Route::get('/dashboard', [RestaurantController::class, 'adminDashboard'])->name('dashboard');
    Route::post('/dashboard/category/add', [RestaurantController::class, 'addMenuCategory'])->name('restaurant.admin.add-category');
    Route::get('/table/{table}/menu', [RestaurantController::class, 'index'])->name('restaurant.admin.menu');

});
