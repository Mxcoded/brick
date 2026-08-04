<?php

use Illuminate\Support\Facades\Route;
use Modules\Staff\Http\Controllers\AttendanceController;
use Modules\Staff\Http\Controllers\LeaveController;
use Modules\Staff\Http\Controllers\PerformanceController;
use Modules\Staff\Http\Controllers\ReportsController;
use Modules\Staff\Http\Controllers\SharedDocumentController;
use Modules\Staff\Http\Controllers\StaffController;
use Modules\Staff\Http\Controllers\TrainingController;

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
            Route::get('admin/balance', [LeaveController::class, 'leaveBalance'])
                ->name('leaves.balance')->middleware('can:leaves.approve');
            Route::post('admin/balance', [LeaveController::class, 'leaveBalanceSubmit'])
                ->name('leaves.balance-submit')->middleware('can:leaves.approve');
            Route::get('/admin/balances', [LeaveController::class, 'showBalancesAdmin'])
                ->name('leaves.admin.balances')->middleware('can:leaves.manage');

            Route::post('/admin/balances', [LeaveController::class, 'updateBalanceAdmin'])
                ->name('leaves.admin.balances.update')->middleware('can:leaves.manage');

            Route::post('/admin/balances/{id}/reset', [LeaveController::class, 'resetBalance'])
                ->name('leaves.admin.balances.reset')->middleware('can:leaves.manage');

            Route::post('/admin/balances/{id}/delete', [LeaveController::class, 'deleteBalance'])
                ->name('leaves.admin.balances.delete')->middleware('can:leaves.manage');
            // Admin Leave Routes
            Route::get('/admin', [LeaveController::class, 'leaveAdminIndex'])
                ->name('leaves.admin')->middleware('can:leaves.approve');
            Route::post('/admin/approve/{id}', [LeaveController::class, 'approveLeave'])
                ->name('leaves.approve')->middleware('can:leaves.approve');
            Route::post('/admin/reject/{id}', [LeaveController::class, 'rejectLeave'])
                ->name('leaves.reject')->middleware('can:leaves.approve');
            Route::post('/admin/{id}/cancel', [LeaveController::class, 'adminCancelLeaveRequest'])
                ->name('leaves.admin.cancel')->middleware('can:leaves.approve');
            // Leave Report Route
            Route::get('/report', [LeaveController::class, 'leaveReport'])
                ->name('leaves.report')->middleware('can:leaves.approve');
            // HR routes for applying on behalf of others
            Route::get('/admin/apply', [LeaveController::class, 'showApplyForOtherForm'])
                ->name('leaves.admin.apply')->middleware('can:leaves.apply-for-others');

            Route::post('/admin/apply', [LeaveController::class, 'submitLeaveForOther'])
                ->name('leaves.admin.submit')->middleware('can:leaves.apply-for-others');

            Route::get('/admin/history', [LeaveController::class, 'showLeaveHistory'])
                ->name('leaves.admin.history')->middleware('can:leaves.approve');

            Route::get('/calendar', [LeaveController::class, 'leaveCalendar'])
                ->name('leaves.calendar')->middleware('can:leaves.approve');
        });
        // ** Attendance Routes **
        Route::prefix('attendance')->group(function () {
            Route::get('/', [AttendanceController::class, 'index'])->name('attendance.index');
            Route::get('/clock', [AttendanceController::class, 'clockInForm'])->name('attendance.clock');
            Route::post('/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock-in');
            Route::post('/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock-out');
            Route::get('/report', [AttendanceController::class, 'report'])->name('attendance.report');
            Route::post('/hikvision-webhook', [AttendanceController::class, 'hikvisionWebhook'])->name('attendance.hikvision-webhook');
            Route::post('/hikvision-test', [AttendanceController::class, 'hikvisionTest'])->name('attendance.hikvision-test');
        });

        // ** Performance & Training Routes **
        Route::prefix('performance')->group(function () {
            Route::get('/', [PerformanceController::class, 'index'])->name('performance.index');
            Route::get('/create', [PerformanceController::class, 'create'])->name('performance.create');
            Route::post('/', [PerformanceController::class, 'store'])->name('performance.store');

            // Skills routes must come BEFORE the wildcard {performanceReview} route
            Route::get('/skills', [PerformanceController::class, 'skillsIndex'])->name('performance.skills');
            Route::get('/skills/create', [PerformanceController::class, 'skillsCreate'])->name('performance.skills-create');
            Route::post('/skills', [PerformanceController::class, 'skillsStore'])->name('performance.skills-store');
            Route::delete('/skills/{employeeSkill}', [PerformanceController::class, 'skillsDestroy'])->name('performance.skills-destroy');

            Route::get('/{performanceReview}', [PerformanceController::class, 'show'])->name('performance.show');
            Route::get('/{performanceReview}/edit', [PerformanceController::class, 'edit'])->name('performance.edit');
            Route::put('/{performanceReview}', [PerformanceController::class, 'update'])->name('performance.update');
        });

        Route::prefix('training')->group(function () {
            Route::get('/', [TrainingController::class, 'index'])->name('training.index');
            Route::get('/create', [TrainingController::class, 'create'])->name('training.create');
            Route::post('/', [TrainingController::class, 'store'])->name('training.store');
            Route::get('/{trainingRecord}/edit', [TrainingController::class, 'edit'])->name('training.edit');
            Route::put('/{trainingRecord}', [TrainingController::class, 'update'])->name('training.update');
            Route::delete('/{trainingRecord}', [TrainingController::class, 'destroy'])->name('training.destroy');
        });

        // ** Reports Routes **
        Route::prefix('reports')->group(function () {
            Route::get('/', [ReportsController::class, 'index'])->name('reports.index')->middleware('permission:employees.read');
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
        // Streams the stored certificate through PHP instead of the public/storage
        // symlink (cPanel returns 403 on junction-linked assets).
        Route::get('/education/{education}/certificate', [StaffController::class, 'downloadCertificate'])
            ->name('education.certificate')
            ->middleware('permission:employees.read');
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
