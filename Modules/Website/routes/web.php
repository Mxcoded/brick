<?php

use Illuminate\Support\Facades\Route;

// Public Controllers
use Modules\Website\Http\Controllers\WebsiteController;
use Modules\Website\Http\Controllers\GuestController;

// Admin Controllers (Aliased to prevent conflicts)
use Modules\Website\Http\Controllers\Admin\WebsiteAdminController;
use Modules\Website\Http\Controllers\Admin\RoomController as AdminRoomController;
use Modules\Website\Http\Controllers\Admin\BookingController as AdminBookingController;
use Modules\Website\Http\Controllers\Admin\AmenityController;
use Modules\Website\Http\Controllers\Admin\SettingController;
use Modules\Website\Http\Controllers\Admin\ContactMessageController;

/*
|--------------------------------------------------------------------------
| Website Module Routes
|--------------------------------------------------------------------------
|
| Prefix: /website (defined in RouteServiceProvider)
|
*/

Route::middleware(['web'])->group(function () {

    // =========================================================================
    // 1. PUBLIC WEBSITE ROUTES
    // =========================================================================
    Route::controller(WebsiteController::class)->group(function () {
        // Core Pages
        Route::get('/', 'index')->name('website.home');
        Route::get('/about-us', 'about')->name('website.about');
        Route::get('/contact-us', 'contact')->name('website.contact');
        Route::get('/location', 'location')->name('website.location');
        Route::get('/dining', 'dining')->name('website.dining');
        Route::get('/amenities', 'amenities')->name('website.amenities');

        // Rooms & Booking
        Route::get('/rooms', 'rooms')->name('website.rooms.index');
        Route::get('/rooms/{slug}', 'roomDetails')->name('website.rooms.show');

        // Availability & Booking Logic
        Route::any('/check-availability', 'checkAvailability')->name('website.room.checkAvailability');
        Route::get('/booking', 'booking')->name('website.booking');
        Route::post('/booking', 'storeBooking')->name('website.booking.store');
        Route::get('/booking/confirmation/{ref?}', 'confirmation')->name('website.booking.confirmation');

        // Form Submission
        Route::post('/contact/send', 'sendMessage')->name('website.contact.send');
    });

    // =========================================================================
    // 2. GUEST DASHBOARD (Authenticated Users)
    // =========================================================================
    Route::middleware(['auth'])->prefix('guest')->name('website.guest.')->group(function () {
        Route::controller(GuestController::class)->group(function () {
            Route::get('/dashboard', 'dashboard')->name('dashboard');

            Route::get('/profile', 'profile')->name('profile');
            Route::put('/profile', 'updateProfile')->name('profile.update');

            Route::get('/my-bookings', 'bookings')->name('bookings.index');
            Route::get('/my-bookings/{ref}', 'bookingDetails')->name('bookings.show');
            Route::post('/my-bookings/{ref}/cancel', 'cancelBooking')->name('bookings.cancel');
        });
    });

    // =========================================================================
    // 3. ADMIN MANAGEMENT ROUTES
    // =========================================================================
    // Access: http://your-site.com/website/admin
    Route::middleware(['auth']) // Add 'can:manage_website' here in production
        ->prefix('admin')
        ->name('website.admin.')
        ->group(function () {

            // Dashboard
            Route::get('/', [WebsiteAdminController::class, 'index'])->name('dashboard');

            // Resource Management
            Route::resource('rooms', AdminRoomController::class);
            Route::resource('bookings', AdminBookingController::class);
            Route::resource('amenities', AmenityController::class);
            Route::resource('settings', SettingController::class);

            // Contact Messages (Read Only / Reply)
            Route::resource('messages', ContactMessageController::class)->only(['index', 'show', 'destroy']);

            // Manual specific routes if Resources don't cover everything
            Route::post('/rooms/image/upload', [AdminRoomController::class, 'uploadImage'])->name('rooms.image.upload');
            Route::delete('/rooms/image/{id}', [AdminRoomController::class, 'deleteImage'])->name('rooms.image.delete');

            Route::post('/bookings/{id}/confirm', [AdminBookingController::class, 'confirm'])->name('bookings.confirm');
            Route::post('/bookings/{id}/cancel', [AdminBookingController::class, 'cancel'])->name('bookings.cancel');
        });
});
