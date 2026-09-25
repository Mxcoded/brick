<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AdminController;

// Import the Enum

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Use the Enum value for the role check
Route::prefix('admin')
    ->middleware(['web', 'auth', 'can:access_admin_dashboard']) // Updated
    ->name('admin.')
    ->group(function () {

        Route::post('/users/assign-role', [AdminController::class, 'assignRole'])->name('users.assign-role');
        Route::post('users/{user}/remove-role', [AdminController::class, 'removeRole'])->name('users.remove-role');
        Route::get('/users/{user}/password', [AdminController::class, 'editPassword'])->name('users.password.edit');
        Route::put('/users/{user}/password', [AdminController::class, 'updatePassword'])->name('users.password.update');
        Route::get('/users/guest/create', [AdminController::class, 'createGuest'])->name('users.guest.create');
        Route::post('/users/guest', [AdminController::class, 'storeGuest'])->name('users.guest.store');
        Route::patch('/users/{user}/type', [AdminController::class, 'updateType'])->name('users.type.update');
        Route::post('/users/{user}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/resend-credentials', [AdminController::class, 'resendCredentials'])->name('users.resend-credentials');

        Route::redirect('/dashboard', '/admin')->name('dashboard');

        Route::get('/employees/create-user', [AdminController::class, 'createUserFromEmployee'])->name('employees.create-user');
        Route::post('/employees/store-user', [AdminController::class, 'storeUserFromEmployee'])->name('employees.store-user');
    });
