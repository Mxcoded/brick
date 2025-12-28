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
        // **Leave Management Routes** - NOW HANDLED BY LeaveController
        Route::prefix('leaves')->group(function () {
            // User Leave Routes
            Route::get('/', [LeaveController::class, 'leaveIndex']) // Changed
                ->name('leaves.index');
            Route::get('/request', [LeaveController::class, 'leaveRequestForm']) // Changed
                ->name('leaves.request');
            Route::post('/request', [LeaveController::class, 'submitLeaveRequest']) // Changed
                ->name('leaves.submit');
            Route::post('/{id}/cancel', [LeaveController::class, 'cancelLeaveRequest'])
                ->name('leaves.cancel');
            // Leave Balance Route
            Route::get('admin/balance', [LeaveController::class, 'leaveBalance']) // Changed
                ->name('leaves.balance');
            Route::post('admin/balance', [LeaveController::class, 'leaveBalanceSubmit']) // Changed
                ->name('leaves.balance-submit');
            Route::get('/admin/balances', [LeaveController::class, 'showBalancesAdmin'])
                ->name('leaves.admin.balances');

            Route::post('/admin/balances', [LeaveController::class, 'updateBalanceAdmin'])
                ->name('leaves.admin.balances.update');


            Route::post('/admin/balances/{id}/reset', [LeaveController::class, 'resetBalance'])
                ->name('leaves.admin.balances.reset');


            Route::post('/admin/balances/{id}/delete', [LeaveController::class, 'deleteBalance'])
                ->name('leaves.admin.balances.delete');
            // Admin Leave Routes
            Route::get('/admin', [LeaveController::class, 'leaveAdminIndex']) // Changed
                ->name('leaves.admin');
            Route::post('/admin/approve/{id}', [LeaveController::class, 'approveLeave']) // Changed
                ->name('leaves.approve');
            Route::post('/admin/reject/{id}', [LeaveController::class, 'rejectLeave']) // Changed
                ->name('leaves.reject');
            Route::post('/admin/{id}/cancel', [LeaveController::class, 'adminCancelLeaveRequest'])
                ->name('leaves.admin.cancel');
            // Leave Report Route
            Route::get('/report', [LeaveController::class, 'leaveReport']) // Changed
                ->name('leaves.report');
            // HR routes for applying on behalf of others
            Route::get('/admin/apply', [LeaveController::class, 'showApplyForOtherForm'])
                ->name('leaves.admin.apply');


            Route::post('/admin/apply', [LeaveController::class, 'submitLeaveForOther'])
                ->name('leaves.admin.submit');

            Route::get('/admin/history', [LeaveController::class, 'showLeaveHistory'])
                ->name('leaves.admin.history');
        });
        // ** Staff Approval Routes (Admin Only)**
        Route::prefix('approvals')->group(function () {
            Route::get('/', [StaffController::class, 'approvalIndex'])
                ->name('approvals.index');
            Route::post('/approve/{id}', [StaffController::class, 'approve'])
                ->name('approve');
            Route::post('/reject/{id}', [StaffController::class, 'reject'])
                ->name('reject');
        });

        // ** NEW BIRTHDAY ROUTE **
        Route::get('/birthdays', [StaffController::class, 'birthdays'])->name('birthdays');
        Route::resource('/', StaffController::class)->names([
            'index'  => 'index',
            'create' => 'create',
            'store'  => 'store',
            'show'   => 'show', // <--- This was missing!
            'edit'   => 'edit',
            'update' => 'update',
            'destroy' => 'destroy',
        ])->parameters([
            '' => 'staff' // <--- THIS FIXES THE {} ISSUE
        ])->middleware([
            'index'   => 'permission:view_employees',
            'show'    => 'permission:view_employees',
            'create'  => 'permission:manage_employees',
            'store'   => 'permission:manage_employees',
            'edit'    => 'permission:manage_employees',
            'update'  => 'permission:manage_employees',
            'destroy' => 'permission:manage_employees',
        ]);
    });

// **Public Routes**
Route::middleware('web')->group(function () {
    Route::get('/complete-registration', [StaffController::class, 'showCompleteRegistrationForm'])
        ->name('staff.complete-registration');
    Route::post('/complete-registration', [StaffController::class, 'completeRegistration'])
        ->name('staff.complete-registration.submit');
});
