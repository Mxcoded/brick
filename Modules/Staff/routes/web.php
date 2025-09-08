<?php

use Illuminate\Support\Facades\Route;
use Modules\Staff\Http\Controllers\StaffController;
use Modules\Staff\Http\Controllers\LeaveController; // Add this line

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// **Staff Resource Routes**
Route::middleware(['web', 'auth'])
    ->resource('staff', StaffController::class)
    ->names('staff')->middleware('permission:staff-view');

// **Staff Functionality Routes**
Route::middleware(['web', 'auth', 'role:staff|admin'])->group(function () {
    // Staff Dashboard
    Route::get('/dashboard', [StaffController::class, 'dashboard'])->name('staff.dashboard');

    // **Leave Management Routes** - NOW HANDLED BY LeaveController
    Route::prefix('leaves')->group(function () {
        // User Leave Routes
        Route::get('/', [LeaveController::class, 'leaveIndex']) // Changed
            ->name('staff.leaves.index')->middleware('permission:staff-view');
        Route::get('/request', [LeaveController::class, 'leaveRequestForm']) // Changed
            ->name('staff.leaves.request');
        Route::post('/request', [LeaveController::class, 'submitLeaveRequest']) // Changed
            ->name('staff.leaves.submit');
        Route::post('/{id}/cancel', [LeaveController::class, 'cancelLeaveRequest'])
            ->name('staff.leaves.cancel');
            // Leave Balance Route
        Route::get('admin/balance', [LeaveController::class, 'leaveBalance']) // Changed
            ->name('staff.leaves.balance')
            ->middleware('permission:manage-leaves');
        Route::post('admin/balance', [LeaveController::class, 'leaveBalanceSubmit']) // Changed
            ->name('staff.leaves.balance-submit')
            ->middleware('permission:manage-leaves');
        Route::get('/admin/balances', [LeaveController::class, 'showBalancesAdmin'])
            ->name('staff.leaves.admin.balances')
            ->middleware('permission:manage-leave-balances');

        Route::post('/admin/balances', [LeaveController::class, 'updateBalanceAdmin'])
            ->name('staff.leaves.admin.balances.update')
            ->middleware('permission:manage-leave-balances');

        Route::post('/admin/balances/{id}/reset', [LeaveController::class, 'resetBalance'])
            ->name('staff.leaves.admin.balances.reset')
            ->middleware('permission:manage-leave-balances');

        Route::post('/admin/balances/{id}/delete', [LeaveController::class, 'deleteBalance'])
            ->name('staff.leaves.admin.balances.delete')
            ->middleware('permission:manage-leave-balances');
            // Admin Leave Routes
        Route::get('/admin', [LeaveController::class, 'leaveAdminIndex']) // Changed
            ->name('staff.leaves.admin')
            ->middleware('permission:approve-leaves');
        Route::post('/admin/approve/{id}', [LeaveController::class, 'approveLeave']) // Changed
            ->name('staff.leaves.approve')
            ->middleware('permission:approve-leaves');
        Route::post('/admin/reject/{id}', [LeaveController::class, 'rejectLeave']) // Changed
            ->name('staff.leaves.reject')
            ->middleware('permission:approve-leaves');
        Route::post('/admin/{id}/cancel', [LeaveController::class, 'adminCancelLeaveRequest'])
            ->name('staff.leaves.admin.cancel')
            ->middleware('permission:approve-leaves');
            // Leave Report Route
        Route::get('/report', [LeaveController::class, 'leaveReport']) // Changed
            ->name('staff.leaves.report')
            ->middleware('permission:leave-reports');
        // HR routes for applying on behalf of others
        Route::get('/admin/apply', [LeaveController::class, 'showApplyForOtherForm'])
            ->name('staff.leaves.admin.apply')
            ->middleware('permission:apply-leave-for-others');

        Route::post('/admin/apply', [LeaveController::class, 'submitLeaveForOther'])
            ->name('staff.leaves.admin.submit')
            ->middleware('permission:apply-leave-for-others');
        Route::get('/admin/history', [LeaveController::class, 'showLeaveHistory'])
            ->name('staff.leaves.admin.history')
            ->middleware('permission:view-leave-history');
        });


    // ** Staff Approval Routes (Admin Only)**
    Route::prefix('approvals')->middleware('role:admin|hr')->group(function () {
        Route::get('/', [StaffController::class, 'approvalIndex'])
            ->name('staff.approvals.index');
        Route::post('/approve/{id}', [StaffController::class, 'approve'])
            ->name('staff.approve');
        Route::post('/reject/{id}', [StaffController::class, 'reject'])
            ->name('staff.reject');
    });
});

// **Public Routes**
Route::middleware('web')->group(function () {
    Route::get('/complete-registration', [StaffController::class, 'showCompleteRegistrationForm'])
        ->name('staff.complete-registration');
    Route::post('/complete-registration', [StaffController::class, 'completeRegistration'])
        ->name('staff.complete-registration.submit');
});
