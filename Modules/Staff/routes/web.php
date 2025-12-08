<?php

use Illuminate\Support\Facades\Route;
use Modules\Staff\Http\Controllers\StaffController;
use Modules\Staff\Http\Controllers\LeaveController; // Add this line
use App\Enums\RoleEnum; // Import the Enum

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::prefix('staff')
    ->middleware(['web', 'auth', 'can:access_staff_dashboard']) // Updated
    ->name('staff.')
    ->group(function () {
        Route::get('/dashboard', [StaffController::class, 'dashboard'])->name('dashboard');
        Route::resource('staff', StaffController::class)->names([
            'index'  => 'index',
            'create' => 'create',
            'store'  => 'store',
            'edit'   => 'edit',
            'update' => 'update',
            'destroy' => 'destroy',
        ])->middleware([
            'index'   => 'permission:staff-view',
            'create'  => 'permission:staff-create',
            'store'   => 'permission:staff-create',
            'edit'    => 'permission:staff-edit',
            'update'  => 'permission:staff-edit',
            'destroy' => 'permission:staff-delete',
        ]);


        // **Leave Management Routes** - NOW HANDLED BY LeaveController
        Route::prefix('leaves')->group(function () {
            // User Leave Routes
            Route::get('/', [LeaveController::class, 'leaveIndex']) // Changed
                ->name('leaves.index')->middleware('permission:staff-view-leaves');
            Route::get('/request', [LeaveController::class, 'leaveRequestForm']) // Changed
                ->name('leaves.request');
            Route::post('/request', [LeaveController::class, 'submitLeaveRequest']) // Changed
                ->name('leaves.submit');
            Route::post('/{id}/cancel', [LeaveController::class, 'cancelLeaveRequest'])
                ->name('leaves.cancel');
            // Leave Balance Route
            Route::get('admin/balance', [LeaveController::class, 'leaveBalance']) // Changed
                ->name('leaves.balance')
                ->middleware('permission:manage-leaves');
            Route::post('admin/balance', [LeaveController::class, 'leaveBalanceSubmit']) // Changed
                ->name('leaves.balance-submit')
                ->middleware('permission:manage-leaves');
            Route::get('/admin/balances', [LeaveController::class, 'showBalancesAdmin'])
                ->name('leaves.admin.balances')
                ->middleware('permission:manage-leave-balances');

            Route::post('/admin/balances', [LeaveController::class, 'updateBalanceAdmin'])
                ->name('leaves.admin.balances.update')
                ->middleware('permission:manage-leave-balances');

            Route::post('/admin/balances/{id}/reset', [LeaveController::class, 'resetBalance'])
                ->name('leaves.admin.balances.reset')
                ->middleware('permission:manage-leave-balances');

            Route::post('/admin/balances/{id}/delete', [LeaveController::class, 'deleteBalance'])
                ->name('leaves.admin.balances.delete')
                ->middleware('permission:manage-leave-balances');
            // Admin Leave Routes
            Route::get('/admin', [LeaveController::class, 'leaveAdminIndex']) // Changed
                ->name('leaves.admin')
                ->middleware('permission:approve-leaves');
            Route::post('/admin/approve/{id}', [LeaveController::class, 'approveLeave']) // Changed
                ->name('leaves.approve')
                ->middleware('permission:approve-leaves');
            Route::post('/admin/reject/{id}', [LeaveController::class, 'rejectLeave']) // Changed
                ->name('leaves.reject')
                ->middleware('permission:approve-leaves');
            Route::post('/admin/{id}/cancel', [LeaveController::class, 'adminCancelLeaveRequest'])
                ->name('leaves.admin.cancel')
                ->middleware('permission:approve-leaves');
            // Leave Report Route
            Route::get('/report', [LeaveController::class, 'leaveReport']) // Changed
                ->name('leaves.report')
                ->middleware('permission:leave-reports');
            // HR routes for applying on behalf of others
            Route::get('/admin/apply', [LeaveController::class, 'showApplyForOtherForm'])
                ->name('leaves.admin.apply')
                ->middleware('permission:apply-leave-for-others');

            Route::post('/admin/apply', [LeaveController::class, 'submitLeaveForOther'])
                ->name('leaves.admin.submit')
                ->middleware('permission:apply-leave-for-others');
            Route::get('/admin/history', [LeaveController::class, 'showLeaveHistory'])
                ->name('leaves.admin.history')
                ->middleware('permission:view-leave-history');
        });


        // ** Staff Approval Routes (Admin Only)**
        Route::prefix('approvals')->middleware('role:admin|hr')->group(function () {
            Route::get('/', [StaffController::class, 'approvalIndex'])
                ->name('approvals.index');
            Route::post('/approve/{id}', [StaffController::class, 'approve'])
                ->name('approve');
            Route::post('/reject/{id}', [StaffController::class, 'reject'])
                ->name('reject');
        });
    });

// **Public Routes**
Route::middleware('web')->group(function () {
    Route::get('/complete-registration', [StaffController::class, 'showCompleteRegistrationForm'])
        ->name('staff.complete-registration');
    Route::post('/complete-registration', [StaffController::class, 'completeRegistration'])
        ->name('staff.complete-registration.submit');
});
