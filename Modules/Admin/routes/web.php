<?php

use Illuminate\Support\Facades\Route;
use Modules\Admin\Http\Controllers\AdminController;

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

Route::prefix('admin')->middleware(['web', 'auth', 'role:admin'])->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/roles', [AdminController::class, 'roles'])->name('admin.roles.index');
    Route::post('/roles', [AdminController::class, 'createRole'])->name('admin.roles.store');
    Route::get('/roles/{id}/edit', [AdminController::class, 'editRole'])->name('admin.roles.edit');
    Route::put('/roles/{id}', [AdminController::class, 'updateRole'])->name('admin.roles.update');
    Route::delete('/roles/{id}', [AdminController::class, 'destroyRole'])->name('admin.roles.destroy');
    Route::get('/permissions', [AdminController::class, 'permissions'])->name('admin.permissions.index');
    Route::post('/permissions', [AdminController::class, 'createPermission'])->name('admin.permissions.store');
    Route::get('/permissions/{id}/edit', [AdminController::class, 'editPermission'])->name('admin.permissions.edit');
    Route::put('/permissions/{id}', [AdminController::class, 'updatePermission'])->name('admin.permissions.update');
    Route::post('/permissions/{id}/roles', [AdminController::class, 'updatePermissionRoles'])->name('admin.permissions.update-roles');
    Route::delete('/permissions/{id}', [AdminController::class, 'destroyPermission'])->name('admin.permissions.destroy');
    Route::post('/permissions/assign-to-role', [AdminController::class, 'assignPermissionToRole'])->name('admin.permissions.assign-to-role');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users.index');
    Route::post('/users/assign-role', [AdminController::class, 'assignRole'])->name('admin.users.assign-role');
    Route::get('/employees/create-user', [AdminController::class, 'createUserFromEmployee'])->name('admin.employees.create-user');
    Route::post('/employees/store-user', [AdminController::class, 'storeUserFromEmployee'])->name('admin.employees.store-user');
});
