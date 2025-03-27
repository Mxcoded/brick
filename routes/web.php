<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use Modules\Admin\Http\Controllers\AdminController;
use modules\Staff\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Auth;
Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home   ', [HomeController::class, 'index'])->name('home');
Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
Route::get('/staff', [StaffController::class, 'index'])->name('staff.dashboard');
