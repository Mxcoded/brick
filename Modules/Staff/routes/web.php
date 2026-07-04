<?php

use Illuminate\Support\Facades\Route;
use Modules\Staff\Http\Controllers\LeaveController;
use Modules\Staff\Http\Controllers\SharedDocumentController;
use Modules\Staff\Http\Controllers\StaffController;

// Import the Enum

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Pending route – must be BEFORE the permission-gated group to avoid 404 (resource catch-all)
Route::prefix('staff')
    ->middleware(['web', 'auth'])
    ->name('staff.')
    ->group(function () {
        Route::get('/pending', [StaffController::class, 'pending'])->name('pending');
    });

Route::prefix('staff')
    ->middleware(['web', 'auth', 'can:access_staff_dashboard']) // Updated
    ->name('staff.')
    ->group(function () {
        Route::get('/dashboard', [StaffController::class, 'dashboard'])->name('dashboard')->middleware('can:employees.read');
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

        // ** BIRTHDAY ROUTES **
        Route::get('/birthdays', [StaffController::class, 'birthdays'])->name('birthdays');

        // ** SMS SETTINGS ROUTES **
        Route::get('/settings', [StaffController::class, 'settings'])->name('settings');
        Route::post('/settings', [StaffController::class, 'updateSettings'])->name('settings.update');

        // ** EXPORT ROUTE **
        Route::get('/export', [StaffController::class, 'export'])
            ->name('export')
            ->middleware('permission:employees.read');

        // ** Shared Documents Routes **
        Route::prefix('documents')->group(function () {
            Route::get('/', [SharedDocumentController::class, 'index'])->name('documents.index');
            Route::get('/create', [SharedDocumentController::class, 'create'])->name('documents.create');
            Route::post('/', [SharedDocumentController::class, 'store'])->name('documents.store');
            Route::get('/{document}/download', [SharedDocumentController::class, 'download'])->name('documents.download');
            Route::post('/{document}/regenerate-link', [SharedDocumentController::class, 'regenerateShareLink'])->name('documents.regenerate-link');
            Route::delete('/{document}', [SharedDocumentController::class, 'destroy'])->name('documents.destroy');
        });

        Route::get('/', [StaffController::class, 'index'])->name('index')->middleware('permission:employees.read');
        Route::get('/create', [StaffController::class, 'create'])->name('create')->middleware('permission:employees.create');
        Route::post('/', [StaffController::class, 'store'])->name('store')->middleware('permission:employees.create');
        Route::get('/{staff}', [StaffController::class, 'show'])->name('show')->middleware('permission:employees.read');
        Route::get('/{staff}/edit', [StaffController::class, 'edit'])->name('edit')->middleware('permission:employees.update');
        Route::put('/{staff}', [StaffController::class, 'update'])->name('update')->middleware('permission:employees.update');
        Route::delete('/{staff}', [StaffController::class, 'destroy'])->name('destroy')->middleware('permission:employees.delete');
    });

// **Public Routes**
Route::middleware('web')->group(function () {
    Route::get('/staffqr', [StaffController::class, 'verifyForm'])
        ->name('staff.verify');
    Route::post('/staffqr', [StaffController::class, 'verifyLookup'])
        ->name('staff.verify.lookup');

    Route::get('/complete-registration', [StaffController::class, 'showCompleteRegistrationForm'])
        ->name('staff.complete-registration');
    Route::post('/complete-registration', [StaffController::class, 'completeRegistration'])
        ->name('staff.complete-registration.submit');

    // Public shared document download (no auth required)
    Route::get('/shared/d/{token}/{slug?}', [SharedDocumentController::class, 'publicDownload'])
        ->where('slug', '[\w\-\.]+')
        ->name('shared.documents.download');
});
