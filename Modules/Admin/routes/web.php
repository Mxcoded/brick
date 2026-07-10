<?php

use App\Http\Controllers\Admin\LoginLogController;
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

        Route::get('/', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/roles', [AdminController::class, 'roles'])->name('roles.index');
        Route::post('/roles', [AdminController::class, 'createRole'])->name('roles.store');
        Route::post('/roles/mass-destroy', [AdminController::class, 'massDestroyRole'])->name('roles.mass_destroy');
        Route::get('/roles/{id}/edit', [AdminController::class, 'editRole'])->name('roles.edit');
        Route::put('/roles/{id}', [AdminController::class, 'updateRole'])->name('roles.update');
        Route::delete('/roles/{id}', [AdminController::class, 'destroyRole'])->name('roles.destroy');

        Route::get('/permissions', [AdminController::class, 'permissions'])->name('permissions.index');
        Route::post('/permissions', [AdminController::class, 'createPermission'])->name('permissions.store');
        Route::get('/permissions/{id}/edit', [AdminController::class, 'editPermission'])->name('permissions.edit');
        Route::put('/permissions/{id}', [AdminController::class, 'updatePermission'])->name('permissions.update');
        Route::post('/permissions/{id}/roles', [AdminController::class, 'updatePermissionRoles'])->name('permissions.update-roles');
        Route::delete('/permissions/{id}', [AdminController::class, 'destroyPermission'])->name('permissions.destroy');
        Route::post('/permissions/assign-to-role', [AdminController::class, 'assignPermissionToRole'])->name('permissions.assign-to-role');

        Route::get('/users', [AdminController::class, 'users'])->name('users.index');
        Route::post('/users/assign-role', [AdminController::class, 'assignRole'])->name('users.assign-role');
        Route::post('users/{user}/remove-role', [AdminController::class, 'removeRole'])->name('users.remove-role');
        Route::get('/users/{user}/password', [AdminController::class, 'editPassword'])->name('users.password.edit');
        Route::put('/users/{user}/password', [AdminController::class, 'updatePassword'])->name('users.password.update');
        Route::get('/users/guest/create', [AdminController::class, 'createGuest'])->name('users.guest.create');
        Route::post('/users/guest', [AdminController::class, 'storeGuest'])->name('users.guest.store');
        Route::patch('/users/{user}/type', [AdminController::class, 'updateType'])->name('users.type.update');
        Route::post('/users/{user}/toggle-status', [AdminController::class, 'toggleUserStatus'])->name('users.toggle-status');
        Route::post('/users/{user}/resend-credentials', [AdminController::class, 'resendCredentials'])->name('users.resend-credentials');

        Route::get('/employees/create-user', [AdminController::class, 'createUserFromEmployee'])->name('employees.create-user');
        Route::post('/employees/store-user', [AdminController::class, 'storeUserFromEmployee'])->name('employees.store-user');

        Route::get('/modules', [AdminController::class, 'modules'])->name('modules.index');
        Route::post('/modules/{name}/toggle', [AdminController::class, 'toggleModule'])->name('modules.toggle');

        Route::get('/activity-logs', [AdminController::class, 'activityLogs'])->name('activity-logs.index');

        Route::get('/appearance', [AdminController::class, 'appearance'])->name('appearance');
        Route::put('/appearance', [AdminController::class, 'updateAppearance'])->name('appearance.update');
        Route::put('/appearance/logo', [AdminController::class, 'updateLogo'])->name('appearance.logo');
        Route::delete('/appearance/logo', [AdminController::class, 'removeLogo'])->name('appearance.logo.remove');

        // Login Logs / User Activity Tracking
        Route::prefix('login-logs')->name('login-logs.')->group(function () {
            Route::get('/', [LoginLogController::class, 'index'])->name('index');
            Route::get('/datatable', [LoginLogController::class, 'datatable'])->name('datatable');
            Route::get('/active-sessions', [LoginLogController::class, 'activeSessions'])->name('active-sessions');
            Route::get('/user/{userId}', [LoginLogController::class, 'userHistory'])->name('user-history');
            Route::get('/export', [LoginLogController::class, 'export'])->name('export');
        });
    });
