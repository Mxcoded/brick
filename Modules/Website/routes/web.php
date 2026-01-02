<?php

use Illuminate\Support\Facades\Route;
use Modules\Website\Http\Controllers\WebsiteController;
use Modules\Website\Http\Controllers\GuestController;

/*
|--------------------------------------------------------------------------
| Website Module Routes
|--------------------------------------------------------------------------
|
| These routes handle the public-facing hotel website, booking flow,
| and authenticated guest dashboard.
|
*/

Route::middleware(['web'])->group(function () {

    // =========================================================================
    // 1. Public Pages (SEO & Information)
    // =========================================================================
    Route::controller(WebsiteController::class)->group(function () {
        // Homepage
        Route::get('/', 'index')->name('website.home');

        // Static Pages
        Route::get('/about-us', 'about')->name('website.about');
        Route::get('/contact-us', 'contact')->name('website.contact');
        Route::get('/location', 'location')->name('website.location');
        Route::get('/dining', 'dining')->name('website.dining');
        Route::get('/amenities', 'amenities')->name('website.amenities');

        // Room Listings & Details
        Route::get('/rooms', 'rooms')->name('website.rooms.index');
        // Supports both ID (legacy) and Slug (SEO)
        Route::get('/rooms/{slug}', 'roomDetails')->name('website.rooms.show');

        // Booking Process (Public)
        Route::get('/booking', 'booking')->name('website.booking');
        Route::post('/booking', 'storeBooking')->name('website.booking.store'); // The fixed transaction-safe method
        Route::any('/check-availability', 'checkAvailability')->name('website.room.checkAvailability'); // FIX: Add the missing route for availability checking
        Route::get('/booking/confirmation/{ref?}', 'confirmation')->name('website.booking.confirmation');

        // Legal & Utility
        Route::post('/contact/send', 'sendMessage')->name('website.contact.send');
    });

    // =========================================================================
    // 2. Guest Area (Authenticated)
    // =========================================================================
    Route::middleware(['auth'])->prefix('guest')->name('website.guest.')->group(function () {
        Route::controller(GuestController::class)->group(function () {
            // Dashboard
            Route::get('/dashboard', 'dashboard')->name('dashboard');

            // Profile Management
            Route::get('/profile', 'profile')->name('profile');
            Route::put('/profile', 'updateProfile')->name('profile.update');

            // Booking History
            Route::get('/my-bookings', 'bookings')->name('bookings.index');
            Route::get('/my-bookings/{ref}', 'bookingDetails')->name('bookings.show');
            Route::post('/my-bookings/{ref}/cancel', 'cancelBooking')->name('bookings.cancel');
        });
    });
});
