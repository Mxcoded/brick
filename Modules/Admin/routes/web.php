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
    Route::get('/permissions', [AdminController::class, 'permissions'])->name('admin.permissions.index');
    Route::post('/permissions', [AdminController::class, 'createPermission'])->name('admin.permissions.store');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users.index');
    Route::post('/users/assign-role', [AdminController::class, 'assignRole'])->name('admin.users.assign-role');
    // New routes for creating user accounts from employees
    Route::get('/employees/create-user', [AdminController::class, 'createUserFromEmployee'])->name('admin.employees.create-user');
    Route::post('/employees/store-user', [AdminController::class, 'storeUserFromEmployee'])->name('admin.employees.store-user');
});
