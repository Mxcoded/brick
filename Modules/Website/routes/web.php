<?php

use Illuminate\Support\Facades\Route;
use Modules\Website\Http\Controllers\WebsiteController;
use Modules\Website\Http\Controllers\GuestController;
use Modules\Website\Http\Controllers\Admin\WebsiteAdminController;
use Modules\Website\Http\Controllers\Admin\RoomController;
use Modules\Website\Http\Controllers\Admin\SettingController;
use Modules\Website\Http\Controllers\Admin\ContactMessageController;
use Modules\Website\Http\Controllers\Admin\AmenityController;
use Modules\Website\Http\Controllers\Admin\BookingController;

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

Route::prefix('website')->group(function () {
    // Homepage
    Route::get('/', [WebsiteController::class, 'index'])->name('website.home');

    // Rooms and Suites
    Route::get('/rooms', [WebsiteController::class, 'rooms'])->name('website.rooms');
    Route::get('/rooms/{room}', [WebsiteController::class, 'roomDetails'])->name('website.room.details');

    // Booking
    Route::get('/booking', [WebsiteController::class, 'bookingForm'])->name('website.booking.form');
    Route::post('/booking', [WebsiteController::class, 'submitBooking'])->name('website.booking.submit');
    Route::get('/booking/confirmation/{booking}', [WebsiteController::class, 'bookingConfirmation'])->name('website.booking.confirmation');
    Route::get('/rooms/{room}/check-availability', [WebsiteController::class, 'checkAvailability'])->name('website.room.checkAvailability');

    // Amenities
    Route::get('/amenities', [WebsiteController::class, 'amenities'])->name('website.amenities');

    // Location
    Route::get('/location', [WebsiteController::class, 'location'])->name('website.location');

    // Contact
    Route::get('/contact', [WebsiteController::class, 'contact'])->name('website.contact');
    Route::post('/contact', [WebsiteController::class, 'submitContact'])->name('website.contact.submit');

    // About Us
    Route::get('/about', [WebsiteController::class, 'about'])->name('website.about');

    // Testimonials
    Route::get('/testimonials', [WebsiteController::class, 'testimonials'])->name('website.testimonials');

    // Blog/News
    Route::get('/blog', [WebsiteController::class, 'blog'])->name('website.blog');

    // Guest-specific routes
    Route::middleware(['auth', 'role:guest'])->group(function () {
        Route::get('/guest/dashboard', [GuestController::class, 'index'])->name('website.guest.dashboard');
        Route::get('/guest/bookings', [GuestController::class, 'bookings'])->name('website.guest.bookings');
        Route::post('/guest/claim-booking', [GuestController::class, 'claimBooking'])->name('website.guest.claim-booking');
        Route::get('/guest/profile', [GuestController::class, 'profile'])->name('website.guest.profile');
        Route::post('/guest/profile', [GuestController::class, 'updateProfile'])->name('website.guest.profile.update');
    });

    // Admin-specific routes
    Route::prefix('admin')->middleware(['auth', 'role:admin'])->group(function () {
        Route::get('/dashboard', [WebsiteAdminController::class, 'dashboard'])->name('website.admin.dashboard');

        // Rooms routes
        Route::get('rooms', [RoomController::class, 'index'])->name('website.admin.rooms.index');
        Route::get('rooms/create', [RoomController::class, 'create'])->name('website.admin.rooms.create');
        Route::post('rooms', [RoomController::class, 'store'])->name('website.admin.rooms.store');
        Route::get('rooms/{room}', [RoomController::class, 'show'])->name('website.admin.rooms.show');
        Route::get('rooms/{room}/edit', [RoomController::class, 'edit'])->name('website.admin.rooms.edit');
        Route::put('rooms/{room}', [RoomController::class, 'update'])->name('website.admin.rooms.update');
        Route::delete('rooms/{room}', [RoomController::class, 'destroy'])->name('website.admin.rooms.destroy');
        Route::delete('rooms/{room}/images/delete/{image}', [RoomController::class, 'destroyImage'])->name('website.admin.rooms.images.destroy');
        Route::delete('rooms/{room}/video/delete', [RoomController::class, 'destroyVideo'])->name('website.admin.rooms.video.destroy');

        // Settings routes
        Route::get('settings', [SettingController::class, 'index'])->name('website.admin.settings.index');
        Route::get('settings/create', [SettingController::class, 'create'])->name('website.admin.settings.create');
        Route::post('settings', [SettingController::class, 'store'])->name('website.admin.settings.store');
        Route::get('settings/{setting}', [SettingController::class, 'show'])->name('website.admin.settings.show');
        Route::get('settings/{setting}/edit', [SettingController::class, 'edit'])->name('website.admin.settings.edit');
        Route::put('settings/{setting}', [SettingController::class, 'update'])->name('website.admin.settings.update');
        Route::delete('settings/{setting}', [SettingController::class, 'destroy'])->name('website.admin.settings.destroy');

        Route::get('amenities', [AmenityController::class, 'index'])->name('website.admin.amenities.index');
        Route::get('amenities/create', [AmenityController::class, 'create'])->name('website.admin.amenities.create');
        Route::post('amenities', [AmenityController::class, 'store'])->name('website.admin.amenities.store');
        Route::get('amenities/{amenity}', [AmenityController::class, 'show'])->name('website.admin.amenities.show');
        Route::get('amenities/{amenity}/edit', [AmenityController::class, 'edit'])->name('website.admin.amenities.edit');
        Route::put('amenities/{amenity}', [AmenityController::class, 'update'])->name('website.admin.amenities.update');
        Route::delete('amenities/{amenity}', [AmenityController::class, 'destroy'])->name('website.admin.amenities.destroy');

        // Other resource routes (commented out until implemented)

        Route::get('bookings', [BookingController::class, 'index'])->name('website.admin.bookings.index');
        Route::get('bookings/create', [BookingController::class, 'create'])->name('website.admin.bookings.create');
        Route::post('bookings', [BookingController::class, 'store'])->name('website.admin.bookings.store');
        Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('website.admin.bookings.show');
        Route::get('bookings/{booking}/edit', [BookingController::class, 'edit'])->name('website.admin.bookings.edit');
        Route::put('bookings/{booking}', [BookingController::class, 'update'])->name('website.admin.bookings.update');
        Route::delete('bookings/{booking}', [BookingController::class, 'destroy'])->name('website.admin.bookings.destroy');
        Route::patch('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('website.admin.bookings.cancel');
        
        Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('website.admin.contact-messages.index');
        Route::get('contact-messages/create', [ContactMessageController::class, 'create'])->name('website.admin.contact-messages.create');
        Route::post('contact-messages', [ContactMessageController::class, 'store'])->name('website.admin.contact-messages.store');
        Route::get('contact-messages/{contactMessage}', [ContactMessageController::class, 'show'])->name('website.admin.contact-messages.show');
        Route::get('contact-messages/{contactMessage}/edit', [ContactMessageController::class, 'edit'])->name('website.admin.contact-messages.edit');
        Route::put('contact-messages/{contactMessage}', [ContactMessageController::class, 'update'])->name('website.admin.contact-messages.update');
        Route::delete('contact-messages/{contactMessage}', [ContactMessageController::class, 'destroy'])->name('website.admin.contact-messages.destroy');
    });
});
