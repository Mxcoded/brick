<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AdminController;
use App\Enums\RoleEnum; // Import the Enum

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Use the Enum value for the role check
Route::prefix('admin')
    ->middleware(['web', 'auth', 'role:' . RoleEnum::ADMIN->value])
    ->name('admin.') // Prefix names too for cleaner code (e.g. admin.dashboard)
    ->group(function () {

        Route::get('/', [AdminController::class, 'index'])->name('dashboard');
        Route::get('/roles', [AdminController::class, 'roles'])->name('roles.index');
        Route::post('/roles', [AdminController::class, 'createRole'])->name('roles.store');
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

        Route::get('/employees/create-user', [AdminController::class, 'createUserFromEmployee'])->name('employees.create-user');
        Route::post('/employees/store-user', [AdminController::class, 'storeUserFromEmployee'])->name('employees.store-user');
    });
