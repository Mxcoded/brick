<?php

use Illuminate\Support\Facades\Route;
use Modules\Restaurant\Http\Controllers\RestaurantController;

/*
 *--------------------------------------------------------------------------
 * API Routes
 *--------------------------------------------------------------------------
 *
 * Here is where you can register API routes for your application. These
 * routes are loaded by the RouteServiceProvider within a group which
 * is assigned the "api" middleware group.
 *
 * The former `apiResource('restaurant', RestaurantController::class)` has been
 * removed. That controller has no `store`, `show`, `update` or `destroy`
 * method, so every write route resolved to a missing method and answered 500,
 * while `index` did match and returned an HTML view enumerating every table
 * and room to anyone holding any Sanctum token. Nothing referenced the route
 * names. An API for this module should be built deliberately against real
 * resources, not by mapping a session-and-Blade controller.
 */

// Route::middleware(['auth:sanctum'])->prefix('v1')->group(function () {
//     // Intentionally empty for now.
// });
