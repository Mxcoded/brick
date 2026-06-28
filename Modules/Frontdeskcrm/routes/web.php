<?php

use Illuminate\Support\Facades\Route;
use Modules\Frontdeskcrm\Http\Controllers\BookingSourceController;
use Modules\Frontdeskcrm\Http\Controllers\ChannelController;
use Modules\Frontdeskcrm\Http\Controllers\ChargeTypeController;
use Modules\Frontdeskcrm\Http\Controllers\CityLedgerController;
use Modules\Frontdeskcrm\Http\Controllers\GroupController;
use Modules\Frontdeskcrm\Http\Controllers\GuestController;
use Modules\Frontdeskcrm\Http\Controllers\GuestTypeController;
use Modules\Frontdeskcrm\Http\Controllers\KioskController;
use Modules\Frontdeskcrm\Http\Controllers\NightAuditController;
use Modules\Frontdeskcrm\Http\Controllers\PropertyController;
use Modules\Frontdeskcrm\Http\Controllers\PreArrivalDashboardController;
use Modules\Frontdeskcrm\Http\Controllers\RateCalendarController;
use Modules\Frontdeskcrm\Http\Controllers\RateCodeController;
use Modules\Frontdeskcrm\Http\Controllers\RegistrationController;
use Modules\Frontdeskcrm\Http\Controllers\ReportController;

// Import the Enum

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

Route::prefix('frontdesk')
    ->middleware(['web', 'auth', 'can:access_frontdesk_dashboard']) // Updated
    ->name('frontdesk.')
    ->group(function () {
        // Route to show the "Confirm Check-in" screen for an online booking
        Route::get('/bookings/{ref}/checkin', [RegistrationController::class, 'checkinFromBooking'])
            ->name('bookings.checkin');
        // Route to process the conversion
        Route::post('/bookings/{ref}/process', [RegistrationController::class, 'processBookingCheckin'])
            ->name('bookings.process');
        // Route to mark a booking as no-show directly
        Route::post('/bookings/{booking}/no-show', [RegistrationController::class, 'markBookingNoShow'])
            ->name('bookings.no-show');
        // records a payment made against a registration.
        Route::post('/registrations/{registration}/payment', [RegistrationController::class, 'storePayment'])
            ->name('registrations.payment.store');
        // --- REGISTRATION MANAGEMENT ---
        Route::prefix('registrations')->name('registrations.')->group(function () {

            // Agent's main dashboard showing all registrations.
            Route::get('/', [RegistrationController::class, 'index'])->name('dashboard');
            Route::get('registrations', [RegistrationController::class, 'index'])->name('index');
            // --- NEW "WALK-IN" ROUTE (Feature) ---
            Route::get('/lookup-guest', [RegistrationController::class, 'lookupGuest'])->name('lookup');
            Route::get('/create-walkin', [RegistrationController::class, 'createWalkin'])->name('createWalkin');
            Route::post('/store-walkin', [RegistrationController::class, 'storeWalkin'])->name('storeWalkin');

            // Shows the form for an agent to finalize a guest's draft.
            Route::get('/{registration}/finalize', [RegistrationController::class, 'showFinalizeForm'])->name('finalize.form');

            // Processes the agent's submission of the finalization form.
            Route::post('/{registration}/finalize', [RegistrationController::class, 'finalize'])->name('finalize');

            // Displays the details of a single, completed registration.
            Route::get('/{registration}', [RegistrationController::class, 'show'])->name('show');

            // Adjusts the stay details (e.g., extending checkout date) for a registration.
            Route::put('/{registration}/adjust-stay', [RegistrationController::class, 'adjustStay'])->name('adjust-stay');

            // Retrieves active group members for a group registration.
            Route::get('/{registration}/active-members', [RegistrationController::class, 'getActiveMembers'])->name('active-members');
            // Add a new member to an existing group
            Route::post('/{registration}/add-member', [RegistrationController::class, 'addMember'])->name('add-member');
            // Generates a printable PDF of a registration.
            Route::get('/{registration}/print', [RegistrationController::class, 'print'])->name('print');
            // ** ADD THIS NEW ROUTE FOR CHECKOUT **
            Route::post('/{registration}/checkout', [RegistrationController::class, 'checkout'])->name('checkout');
            // --- NO-SHOW ROUTE ---
            Route::post('/{registration}/no-show', [RegistrationController::class, 'markNoShow'])->name('no-show');

            // --- REOPEN ROUTE (From No-Show or Checked-Out) ---
            Route::post('/{registration}/reopen', [RegistrationController::class, 'reopen'])->name('reopen');

            // --- NEW "DELETE DRAFT" ROUTE (Feature) ---
            Route::delete('/{registration}', [RegistrationController::class, 'destroy'])->name('destroy');

            // --- SECURITY DEPOSIT & PRE-AUTH ---
            Route::post('/{registration}/security-deposit/refund', [RegistrationController::class, 'refundSecurityDeposit'])->name('security-deposit.refund');
            Route::post('/{registration}/security-deposit/forfeit', [RegistrationController::class, 'forfeitSecurityDeposit'])->name('security-deposit.forfeit');
            Route::post('/{registration}/pre-auth/{action}', [RegistrationController::class, 'updatePreAuthStatus'])->name('pre-auth.update');

            // --- ROOM UPGRADE ---
            Route::get('/{registration}/upgrade-options', [RegistrationController::class, 'getUpgradeOptions'])->name('upgrade-options');
            Route::post('/{registration}/upgrade', [RegistrationController::class, 'processUpgrade'])->name('upgrade');
        });

        // --- PRE-ARRIVAL DASHBOARD (Digital Guest Journey) ---
        Route::prefix('pre-arrivals')->name('pre-arrivals.')->group(function () {
            Route::get('/', [PreArrivalDashboardController::class, 'index'])->name('index');
            Route::get('/datatable', [PreArrivalDashboardController::class, 'datatable'])->name('datatable');
            Route::get('{registration}', [PreArrivalDashboardController::class, 'show'])->name('show');
            Route::post('{registration}/documents/{document}/verify', [PreArrivalDashboardController::class, 'verifyDocument'])->name('documents.verify');
            Route::post('{registration}/documents/{document}/reject', [PreArrivalDashboardController::class, 'rejectDocument'])->name('documents.reject');
            Route::post('{registration}/approve', [PreArrivalDashboardController::class, 'approve'])->name('approve');
            Route::post('{registration}/send-reminder', [PreArrivalDashboardController::class, 'sendReminder'])->name('send-reminder');
        });

        // --- CHANNEL MANAGER ---
        Route::resource('channels', ChannelController::class)->except(['show']);
        Route::get('channels/{channel}', [ChannelController::class, 'show'])->name('channels.show');
        Route::post('channels/{channel}/sync', [ChannelController::class, 'sync'])->name('channels.sync');

        // --- MASTER DATA MANAGEMENT ---

        // Routes for managing Booking Sources (e.g., Walk-in, Booking.com).
        Route::resource('booking-sources', BookingSourceController::class);

        // Routes for managing Guest Types (e.g., Corporate, VIP).
        Route::resource('guest-types', GuestTypeController::class);

        // --- GUEST DIRECTORY MANAGEMENT ---
        Route::prefix('guests')->name('guests.')->group(function () {
            Route::get('/', [GuestController::class, 'index'])->name('index');
            Route::get('/datatable', [GuestController::class, 'datatable'])->name('datatable');
            Route::get('/create', [GuestController::class, 'create'])->name('create');
            Route::post('/', [GuestController::class, 'store'])->name('store');
            Route::get('/import', [GuestController::class, 'showImportForm'])->name('import');
            Route::post('/import', [GuestController::class, 'import'])->name('import.process');
            Route::get('/{guest}', [GuestController::class, 'show'])->name('show');
            Route::get('/{guest}/edit', [GuestController::class, 'edit'])->name('edit');
            Route::put('/{guest}', [GuestController::class, 'update'])->name('update');
            Route::delete('/{guest}', [GuestController::class, 'destroy'])->name('destroy');
        });

        Route::prefix('rooms')->name('rooms.')->group(function () {
            // 1. The Visual Room Rack (Box View)
            Route::get('/rack', [RegistrationController::class, 'roomRack'])->name('rack');

            // 2. The Calendar View (Timeline)
            Route::get('/schedule', [RegistrationController::class, 'schedule'])->name('schedule');
        });

        // --- RATE CODE MANAGEMENT ---
        Route::resource('rate-codes', RateCodeController::class);

        // --- RATE CALENDAR ---
        Route::prefix('rate-calendar')->name('rate-calendar.')->group(function () {
            Route::get('/', [RateCalendarController::class, 'index'])->name('index');
            Route::post('/update', [RateCalendarController::class, 'update'])->name('update');
            Route::post('/bulk-update', [RateCalendarController::class, 'bulkUpdate'])->name('bulk-update');
            Route::get('/day', [RateCalendarController::class, 'getDay'])->name('day');
        });

        // --- CHARGE TYPES ---
        Route::resource('charge-types', ChargeTypeController::class)->except(['show']);

        // --- FOLIO (charges posted to a registration) ---
        Route::prefix('registrations/{registration}/charges')->name('registrations.charges.')->group(function () {
            Route::post('/', [RegistrationController::class, 'storeCharge'])->name('store');
        });

        // --- INVOICE ---
        Route::get('registrations/{registration}/invoice', [RegistrationController::class, 'invoice'])->name('registrations.invoice');

        // --- NIGHT AUDIT ---
        Route::prefix('audit')->name('audit.')->group(function () {
            Route::get('/', [NightAuditController::class, 'index'])->name('index');
            Route::get('/create', [NightAuditController::class, 'create'])->name('create');
            Route::post('/', [NightAuditController::class, 'store'])->name('store');
            Route::get('/{audit}', [NightAuditController::class, 'show'])->name('show');
            Route::post('/{audit}/rollback', [NightAuditController::class, 'rollback'])->name('rollback');
        });

        // --- REPORTS ---
        Route::prefix('reports')->name('reports.')->group(function () {
            Route::get('/', [ReportController::class, 'index'])->name('index');
            Route::get('/occupancy', [ReportController::class, 'occupancy'])->name('occupancy');
            Route::get('/revenue', [ReportController::class, 'revenue'])->name('revenue');
            Route::get('/forecast', [ReportController::class, 'forecast'])->name('forecast');
            Route::get('/sources', [ReportController::class, 'sources'])->name('sources');
            Route::get('/demographics', [ReportController::class, 'demographics'])->name('demographics');
        });

        // --- PROPERTIES (Multi-Property) ---
        Route::prefix('properties')->name('properties.')->group(function () {
            Route::get('/', [PropertyController::class, 'index'])->name('index');
            Route::get('/create', [PropertyController::class, 'create'])->name('create');
            Route::post('/', [PropertyController::class, 'store'])->name('store');
            Route::get('/{property}', [PropertyController::class, 'show'])->name('show');
            Route::get('/{property}/edit', [PropertyController::class, 'edit'])->name('edit');
            Route::put('/{property}', [PropertyController::class, 'update'])->name('update');
            Route::delete('/{property}', [PropertyController::class, 'destroy'])->name('destroy');
            Route::post('/{property}/switch', [PropertyController::class, 'switch'])->name('switch');
            Route::get('/{property}/users', [PropertyController::class, 'manageUsers'])->name('users');
            Route::post('/{property}/users', [PropertyController::class, 'assignUser'])->name('assignUser');
            Route::delete('/{property}/users/{user}', [PropertyController::class, 'removeUser'])->name('removeUser');
        });

        // --- CITY LEDGER (Corporate Accounts) ---
        Route::prefix('city-ledger')->name('city-ledger.')->group(function () {
            Route::get('/', [CityLedgerController::class, 'index'])->name('index');
            Route::get('/create', [CityLedgerController::class, 'create'])->name('create');
            Route::post('/', [CityLedgerController::class, 'store'])->name('store');
            Route::get('/{corporateAccount}', [CityLedgerController::class, 'show'])->name('show');
            Route::get('/{corporateAccount}/edit', [CityLedgerController::class, 'edit'])->name('edit');
            Route::put('/{corporateAccount}', [CityLedgerController::class, 'update'])->name('update');
            Route::post('/{corporateAccount}/payment', [CityLedgerController::class, 'recordPayment'])->name('payment');
            Route::post('/{corporateAccount}/charge', [CityLedgerController::class, 'chargeRegistration'])->name('charge');
            Route::get('/{corporateAccount}/transactions', [CityLedgerController::class, 'transactions'])->name('transactions');
        });

        // --- GROUP BOOKINGS MANAGEMENT ---
        Route::prefix('groups')->name('groups.')->group(function () {
            Route::get('/', [GroupController::class, 'index'])->name('index');
            Route::get('/{registration}', [GroupController::class, 'show'])->name('show');
            Route::post('/{registration}/bulk-checkin', [GroupController::class, 'bulkCheckin'])->name('bulk-checkin');
            Route::post('/{registration}/bulk-checkout', [GroupController::class, 'bulkCheckout'])->name('bulk-checkout');
            Route::delete('/{registration}', [GroupController::class, 'destroy'])->name('destroy');
        });
    });

// =====================================================================
// 3. PUBLIC KIOSK ROUTE (Guest Signature Collection)
// =====================================================================
Route::prefix('kiosk')->name('frontdesk.kiosk.')->group(function () {
    Route::get('/sign', [KioskController::class, 'signForm'])->name('sign');
    Route::post('/sign/lookup', [KioskController::class, 'lookupBooking'])->name('sign.lookup');
    Route::post('/sign', [KioskController::class, 'submitSignature'])->name('sign.submit');
});

// =====================================================================
// 4. FAST TRACK CHECK-IN ROUTES (SIMPLE FLOW FOR GUESTS)
// =====================================================================
Route::prefix('fast-track')->name('frontdesk.fasttrack.')->group(function () {
    // The Landing Page (Scan QR lands here)
    Route::get('/', [RegistrationController::class, 'fastTrackIndex'])->name('index');

    // Lookup (Find Booking)
    Route::post('/lookup', [RegistrationController::class, 'fastTrackLookup'])->name('lookup');

    // Submit Data (Save Profile + Signature)
    Route::post('/submit', [RegistrationController::class, 'fastTrackStore'])->name('store');

    // Success Page
    Route::get('/done', function () {
        return view('frontdeskcrm::fasttrack.done');
    })->name('done');
});
