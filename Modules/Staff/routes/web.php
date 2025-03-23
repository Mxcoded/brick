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
Route::middleware(['web', 'auth'])->group(function () {
    // Staff Resource Routes
    Route::resource('staff', StaffController::class)
        ->names('staff')
        ->middleware('permission:view-staff');

    // Leave Management Routes
    Route::prefix('leaves')->group(function () {
        Route::get('/', [StaffController::class, 'leaveIndex'])
            ->name('staff.leaves.index')
            ->middleware('permission:manage-leaves');
        Route::get('/request', [StaffController::class, 'leaveRequestForm'])
            ->name('staff.leaves.request')
            ->middleware('permission:manage-leaves');
        Route::post('/request', [StaffController::class, 'submitLeaveRequest'])
            ->name('staff.leaves.submit')
            ->middleware('permission:manage-leaves');
        Route::get('/admin', [StaffController::class, 'leaveAdminIndex'])
            ->name('staff.leaves.admin')
            ->middleware('permission:approve-leaves');
        Route::post('/admin/approve/{id}', [StaffController::class, 'approveLeave'])
            ->name('staff.leaves.approve')
            ->middleware('permission:approve-leaves');
        Route::post('/admin/reject/{id}', [StaffController::class, 'rejectLeave'])
            ->name('staff.leaves.reject')
            ->middleware('permission:approve-leaves');
        Route::get('/report', [StaffController::class, 'leaveReport'])
            ->name('staff.leaves.report')
            ->middleware('permission:view-reports');
    });

    // Approval Routes (Admin only)
    Route::prefix('approvals')
        ->middleware('role:admin')
        ->group(function () {
            Route::get('/', [StaffController::class, 'approvalIndex'])
                ->name('staff.approvals.index');
            Route::post('/approve/{id}', [StaffController::class, 'approve'])
                ->name('staff.approve');
            Route::post('/reject/{id}', [StaffController::class, 'reject'])
                ->name('staff.reject');
        });
});

Route::middleware('web')->group(function () {
    Route::get('/complete-registration', [StaffController::class, 'showCompleteRegistrationForm'])
        ->name('staff.complete-registration');
    Route::post('/complete-registration', [StaffController::class, 'completeRegistration'])
        ->name('staff.complete-registration.submit');
});