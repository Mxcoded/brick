<?php

use Illuminate\Support\Facades\Route;
use Modules\Frontdeskcrm\Http\Controllers\RegistrationController;
use Modules\Frontdeskcrm\Http\Controllers\BookingSourceController;
use Modules\Frontdeskcrm\Http\Controllers\GuestTypeController;

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

Route::middleware('auth')->prefix('frontdesk')->name('frontdesk.')->group(function () {
    // Registrations
    Route::get('/registrations', [RegistrationController::class, 'index'])->name('registrations.index');
    Route::get('/registrations/create', [RegistrationController::class, 'create'])->name('registrations.create');
    Route::post('/registrations', [RegistrationController::class, 'store'])->name('registrations.store');
    Route::get('/registrations/{registration}', [RegistrationController::class, 'show'])->name('registrations.show');
    Route::post('/registrations/{master}/add-member', [RegistrationController::class, 'addGroupMember'])->name('registrations.add-member');
    Route::get('/registrations/search', [RegistrationController::class, 'search'])->name('registrations.search');
    Route::get('/registrations/preview/{registration?}', [RegistrationController::class, 'preview'])
        ->where('registration', '[0-9]+')
        ->name('registrations.preview');

    Route::get('/registrations/{registration}/finalize', [RegistrationController::class, 'showFinishDraftForm'])->name('registrations.finish-draft.form'); // (Needs to be implemented)
    Route::post('/registrations/{registration}/finalize', [RegistrationController::class, 'finishDraft'])->name('registrations.finish-draft.store');
    Route::delete('/registrations/{registration}', [RegistrationController::class, 'destroy'])->name('registrations.destroy');
    // Booking Sources
    Route::resource('booking-sources', BookingSourceController::class);

    // Guest Types
    Route::resource('guest-types', GuestTypeController::class);
});
