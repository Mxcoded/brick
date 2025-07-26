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

Route::get('/table/{table}/menu', [RestaurantController::class, 'index'])->name('restaurant.menu');
Route::post('/table/{table}/cart/add', [RestaurantController::class, 'addToCart']);
Route::get('/table/{table}/cart', [RestaurantController::class, 'viewCart']);
Route::post('/table/{table}/order/submit', [RestaurantController::class, 'submitOrder']);
