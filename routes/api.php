<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\Api\UserController;
use Modules\Finance\Http\Controllers\Api\PaymentController;
use Modules\Frontdeskcrm\Http\Controllers\Api\GuestController;
use Modules\Frontdeskcrm\Http\Controllers\Api\RegistrationController;
use Modules\Housekeeping\Http\Controllers\Api\RoomController;
use Modules\Housekeeping\Http\Controllers\Api\RoomUnitController;
use Modules\Restaurant\Http\Controllers\Api\MenuItemController;
use Modules\Restaurant\Http\Controllers\Api\OrderController;
use Modules\Restaurant\Http\Controllers\Api\TableController;
use Modules\Staff\Http\Controllers\Api\EmployeeController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Public routes (no authentication required)
|
*/

Route::post('/v1/login', [AuthController::class, 'login']);
Route::post('/v1/register', [AuthController::class, 'register']);

/*
|--------------------------------------------------------------------------
| Protected API Routes
|--------------------------------------------------------------------------
|
| All routes below require a valid Sanctum token and property context.
|
*/

Route::middleware(['auth:sanctum', 'api.property'])->prefix('v1')->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/switch-property', [AuthController::class, 'switchProperty']);

    // Frontdeskcrm
    Route::apiResource('registrations', RegistrationController::class);
    Route::post('/registrations/{registration}/checkin', [RegistrationController::class, 'checkin']);
    Route::post('/registrations/{registration}/checkout', [RegistrationController::class, 'checkout']);
    Route::apiResource('guests', GuestController::class);

    // Restaurant
    Route::apiResource('orders', OrderController::class);
    Route::post('/orders/{order}/status', [OrderController::class, 'updateStatus']);
    Route::apiResource('menu-items', MenuItemController::class);
    Route::apiResource('tables', TableController::class);

    // Housekeeping
    Route::apiResource('rooms', RoomController::class);
    Route::apiResource('room-units', RoomUnitController::class);
    Route::post('/room-units/{roomUnit}/status', [RoomUnitController::class, 'updateStatus']);

    // Finance
    Route::apiResource('payments', PaymentController::class)->only(['index', 'store', 'show']);

    // Staff
    Route::apiResource('employees', EmployeeController::class);

    // Users (admin only)
    Route::apiResource('users', UserController::class);
});
