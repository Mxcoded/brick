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

Route::group([], function () {
    Route::resource('staff', StaffController::class)->names('staff');
});

// Approval Routes (accessible only to HR/Admins)
Route::prefix('approvals')->group(function () {
    Route::get('/', [StaffController::class, 'approvalIndex'])->name('staff.approvals.index');
    Route::post('/approve/{id}', [StaffController::class, 'approve'])->name('staff.approve');
    Route::post('/reject/{id}', [StaffController::class, 'reject'])->name('staff.reject');
});

Route::get('/complete-registration', [StaffController::class, 'showCompleteRegistrationForm'])->name('staff.complete-registration');
Route::post('/complete-registration', [StaffController::class, 'completeRegistration'])->name('staff.complete-registration.submit');
