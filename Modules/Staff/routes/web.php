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

// **Staff Resource Routes**
// URLs: /staff, /staff/{id}, /staff/create, etc.
// Purpose: CRUD operations for managing staff records
// Accessible to authenticated users with 'admin' or 'staff' role and 'view-staff' permission
Route::middleware(['web', 'auth'])
    ->resource('staff', StaffController::class)
    ->names('staff')->middleware('permission:staff-view');

// **Staff Functionality Routes**
// Base URL: /staff/*
// Purpose: Staff user actions like dashboard, leave management, and approvals
Route::middleware(['web', 'auth', 'role:staff|admin'])->group(function () {    
    // Staff Dashboard
    // URL: /staff/dashboard
  Route::get('/dashboard', [StaffController::class, 'dashboard'])->name('staff.dashboard');
    // **Leave Management Routes**
    // Base URL: /staff/leaves/*
    Route::prefix('leaves')->group(function () {
        // User Leave Routes (for staff to manage their own leaves)
        Route::get('/', [StaffController::class, 'leaveIndex'])
            ->name('staff.leaves.index')->middleware('permission:staff-view');
        Route::get('/request', [StaffController::class, 'leaveRequestForm'])
            ->name('staff.leaves.request');
        Route::post('/request', [StaffController::class, 'submitLeaveRequest'])
            ->name('staff.leaves.submit');
    

         // Leave Balance Route
        Route::get('/balance', [StaffController::class, 'leaveBalance'])
            ->name('staff.leaves.balance')
            ->middleware('permission:manage-leaves');
        Route::post('/balance', [StaffController::class, 'leaveBalanceSubmit'])
            ->name('staff.leaves.balance-submit')
            ->middleware('permission:manage-leaves');

        // Admin Leave Routes (for managing all leaves)
        Route::get('/admin', [StaffController::class, 'leaveAdminIndex'])
            ->name('staff.leaves.admin')
            ->middleware('permission:approve-leaves');
        Route::post('/admin/approve/{id}', [StaffController::class, 'approveLeave'])
            ->name('staff.leaves.approve')
            ->middleware('permission:approve-leaves');
        Route::post('/admin/reject/{id}', [StaffController::class, 'rejectLeave'])
            ->name('staff.leaves.reject')
            ->middleware('permission:approve-leaves');

        // Leave Report Route
        Route::get('/report', [StaffController::class, 'leaveReport'])
            ->name('staff.leaves.report')
            ->middleware('permission:leave-reports');

    });

    // **Approval Routes (Admin Only)**
    // Base URL: /staff/approvals/*
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
// Purpose: Routes accessible without authentication
Route::middleware('web')->group(function () {
    Route::get('/complete-registration', [StaffController::class, 'showCompleteRegistrationForm'])
        ->name('staff.complete-registration');
    Route::post('/complete-registration', [StaffController::class, 'completeRegistration'])
        ->name('staff.complete-registration.submit');
});
