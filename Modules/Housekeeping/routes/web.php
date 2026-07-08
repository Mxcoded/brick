<?php

use Illuminate\Support\Facades\Route;
use Modules\Housekeeping\Http\Controllers\HousekeepingController;

Route::prefix('housekeeping')->name('housekeeping.')->middleware(['web', 'auth', 'can:access_frontdesk_dashboard'])->group(function () {
    Route::get('/', [HousekeepingController::class, 'index'])->name('index');
    Route::post('/update-status', [HousekeepingController::class, 'updateStatus'])->name('update-status');
    Route::post('/bulk-update', [HousekeepingController::class, 'bulkUpdate'])->name('bulk-update');
    Route::get('/room/{id}', [HousekeepingController::class, 'getRoomStatus'])->name('room-status');
    Route::get('/logs', [HousekeepingController::class, 'logs'])->name('logs');
});
