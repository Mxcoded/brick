<?php

use Illuminate\Support\Facades\Route;
use Modules\Website\Http\Controllers\WebsiteController;

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
    Route::get('/rooms/{room}/check-availability', [WebsiteController::class, 'checkAvailability'])
        ->name('website.room.checkAvailability');

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
});
