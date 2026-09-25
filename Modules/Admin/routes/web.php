<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->middleware(['web', 'auth', 'can:access_admin_dashboard'])
    ->group(function () {

        Route::redirect('/dashboard', '/admin')->name('dashboard');
    });
