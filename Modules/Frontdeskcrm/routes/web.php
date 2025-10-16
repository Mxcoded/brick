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
| This file defines all the routes for the Frontdesk CRM module.
| The routes are separated into public (guest-facing) and authenticated
| (agent-facing) groups for clarity and security.
|
*/

// =====================================================================
// 1. PUBLIC GUEST ROUTES (ACCESSIBLE WITHOUT LOGIN)
// =====================================================================

Route::prefix('checkin')->name('frontdesk.registrations.')->group(function () {

    // The starting point for a guest at a kiosk/tablet (e.g., yourhotel.com/checkin).
    // Shows the initial search form.
    Route::get('/', [RegistrationController::class, 'create'])->name('create');

    // Processes the guest's initial search for their profile.
    Route::post('/search', [RegistrationController::class, 'handleGuestSearch'])->name('handle-search');

    // Stores the guest's submitted draft registration.
    Route::post('/', [RegistrationController::class, 'store'])->name('store');

    // The "Thank You" page shown to the guest after they submit their draft.
    Route::get('/thank-you', [RegistrationController::class, 'thankYou'])->name('thank-you');
});


// =====================================================================
// 2. AUTHENTICATED AGENT ROUTES (REQUIRES LOGIN)
// =====================================================================

Route::middleware('auth')->prefix('frontdesk')->name('frontdesk.')->group(function () {

    // --- REGISTRATION MANAGEMENT ---
    Route::prefix('registrations')->name('registrations.')->group(function () {

        // Agent's main dashboard showing all registrations.
        Route::get('/', [RegistrationController::class, 'index'])->name('index');

        // Shows the form for an agent to finalize a guest's draft.
        Route::get('/{registration}/finalize', [RegistrationController::class, 'showFinalizeForm'])->name('finalize.form');

        // Processes the agent's submission of the finalization form.
        Route::post('/{registration}/finalize', [RegistrationController::class, 'finalize'])->name('finalize');

        // Displays the details of a single, completed registration.
        Route::get('/{registration}', [RegistrationController::class, 'show'])->name('show');

        // Generates a printable PDF of a registration.
        Route::get('/{registration}/print', [RegistrationController::class, 'print'])->name('print');
        // ** ADD THIS NEW ROUTE FOR CHECKOUT **
        Route::post('/{registration}/checkout', [RegistrationController::class, 'checkout'])->name('checkout');
    });

    // --- MASTER DATA MANAGEMENT ---

    // Routes for managing Booking Sources (e.g., Walk-in, Booking.com).
    Route::resource('booking-sources', BookingSourceController::class)->except(['show']);

    // Routes for managing Guest Types (e.g., Corporate, VIP).
    Route::resource('guest-types', GuestTypeController::class)->except(['show']);
});
