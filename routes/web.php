<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use Modules\Admin\Http\Controllers\AdminController;
use Modules\Staff\Http\Controllers\StaffController;
use Illuminate\Support\Facades\Auth;
Route::get('/', function () {
    return view('welcome');
});

Auth::routes();

Route::get('/home   ', [HomeController::class, 'index'])->name('home');
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
// Route::get('/admin', [AdminController::class, 'index'])->name('admin.dashboard');
// Remove or comment out the conflicting staff.dashboard
// Route::get('/staff', [StaffController::class, 'index'])->name('staff.dashboard');
