<?php

use Illuminate\Support\Facades\Route;
use Modules\Frontdeskcrm\Http\Controllers\FrontdeskcrmController;
use Modules\Frontdeskcrm\Http\Controllers\RegistrationController;
use Modules\Frontdeskcrm\Http\Controllers\BookingSourceController;

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
    Route::resource('frontdeskcrm', FrontdeskcrmController::class)->names('frontdeskcrm');
});



Route::middleware('auth')->prefix('frontdesk')->name('frontdesk.')->group(function () {
    Route::get('/registrations/create', [RegistrationController::class, 'create'])->name('registrations.create');
    Route::post('/registrations', [RegistrationController::class, 'store'])->name('registrations.store');
    Route::get('/registrations', [RegistrationController::class, 'index'])->name('registrations.index'); // For listing later
    Route::resource('booking-sources', BookingSourceController::class)->except(['show']); // Or include show
});
