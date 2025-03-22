<?php

use Illuminate\Support\Facades\Route;
use Modules\Staff\Http\Controllers\StaffController;

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

Route::middleware('auth')->group(
    function () {
        Route::group([], function () {
            Route::resource('staff', StaffController::class)->names('staff');
        });

        // Leave Management Routes
        Route::prefix('leaves')->group(function () {
            Route::get('/', [StaffController::class, 'leaveIndex'])->name('staff.leaves.index'); // Employee leave dashboard
            Route::get('/request', [StaffController::class, 'leaveRequestForm'])->name('staff.leaves.request');
            Route::post('/request', [StaffController::class, 'submitLeaveRequest'])->name('staff.leaves.submit');
            Route::get('/admin', [StaffController::class, 'leaveAdminIndex'])->name('staff.leaves.admin'); // Admin view
            Route::post('/admin/approve/{id}', [StaffController::class, 'approveLeave'])->name('staff.leaves.approve');
            Route::post('/admin/reject/{id}', [StaffController::class, 'rejectLeave'])->name('staff.leaves.reject');
            Route::get('/report', [StaffController::class, 'leaveReport'])->name('staff.leaves.report'); // Leave report
        });
    }
);

// Approval Routes (accessible only to HR/Admins)
Route::prefix('approvals')->group(function () {
    Route::get('/', [StaffController::class, 'approvalIndex'])->name('staff.approvals.index');
    Route::post('/approve/{id}', [StaffController::class, 'approve'])->name('staff.approve');
    Route::post('/reject/{id}', [StaffController::class, 'reject'])->name('staff.reject');
});

Route::get('/complete-registration', [StaffController::class, 'showCompleteRegistrationForm'])->name('staff.complete-registration');
Route::post('/complete-registration', [StaffController::class, 'completeRegistration'])->name('staff.complete-registration.submit');
