<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file handles the "Entry Points" of the application.
| Module-specific routes (Admin, Gym, etc.) are loaded from /Modules.
|
*/

// 1. Public Landing Page
Route::get('/', function () {
    return view('welcome');
});

// 2. Authentication Routes (Login, Register, Password Reset)
// We keep 'verify' => true to ensure email verification is enforced
Auth::routes(['verify' => true]);

// 3. Authenticated Global Routes
Route::middleware(['auth', 'verified'])->group(function () {

    // The "Hub" Route: Redirects users to their specific dashboard (Admin/Staff/Guest)
    Route::get('/home', [HomeController::class, 'index'])->name('home');

    // User Profile (Shared across all user types)
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile', [ProfileController::class, 'update'])->name('profile.update');
});
