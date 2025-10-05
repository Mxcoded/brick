<?php

use Illuminate\Support\Facades\Route;
use Modules\Frontdeskcrm\Http\Controllers\RegistrationController;
use Modules\Frontdeskcrm\Http\Controllers\BookingSourceController;
use Modules\Frontdeskcrm\Http\Controllers\GuestTypeController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// --- 1. PUBLIC GUEST ROUTES (NO AUTH REQUIRED) ---
// The store route must be public for guest drafts.
Route::post('registrations', [RegistrationController::class, 'store'])->name('frontdesk.registrations.store');

// Guest Draft Form (public GET for unauthenticated access)
Route::get('registrations/create', [RegistrationController::class, 'showGuestDraftForm'])->name('frontdesk.registrations.create');

// --- 2. AUTHENTICATED FRONTDESK ROUTES ---
// Prefix all URLs with 'frontdesk' and all names with 'frontdesk.'
Route::middleware('auth')->prefix('frontdesk')->name('frontdesk.')->group(function () {

    // REGISTRATIONS (GUEST/BOOKING MANAGEMENT)
    // All URLs are prefixed with '/frontdesk/registrations' and named 'frontdesk.registrations.'
    Route::prefix('registrations')->name('registrations.')->group(function () {

        // CORE MANAGEMENT
        Route::get('/', [RegistrationController::class, 'index'])->name('index');
        Route::get('/{registration}', [RegistrationController::class, 'show'])->name('show');
        Route::delete('/{registration}', [RegistrationController::class, 'destroy'])->name('destroy');

        // AGENT CHECK-IN FLOW
        // NOTE: Removed the 'create' redirect route that was causing conflicts.
        // The sidebar link MUST now point directly to frontdesk.registrations.agent-checkin

        // 1. The actual Agent-facing form with search bar
        // The URL is /frontdesk/registrations/agent-checkin
        Route::get('/agent-checkin', [RegistrationController::class, 'showAgentCheckinForm'])->name('agent-checkin');

        // DRAFT FINALIZATION FLOW
        Route::get('/{registration}/finalize', [RegistrationController::class, 'showFinishDraftForm'])->name('finish-draft.form');
        Route::post('/{registration}/finalize', [RegistrationController::class, 'finishDraft'])->name('finish-draft.store');

        // UTILITY ROUTES
        Route::get('/search', [RegistrationController::class, 'search'])->name('search');
        Route::get('/preview/{registration?}', [RegistrationController::class, 'preview'])
            ->where('registration', '[0-9]+')
            ->name('preview');
        Route::get('/print/{registration}', [RegistrationController::class, 'print'])->name('print');
        Route::post('/{master}/add-member', [RegistrationController::class, 'addGroupMember'])->name('add-member');
    });

    // MASTER DATA MANAGEMENT
    Route::resource('booking-sources', BookingSourceController::class)->except(['show']);
    Route::resource('guest-types', GuestTypeController::class)->except(['show']);
});
