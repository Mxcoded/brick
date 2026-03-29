<?php

use Illuminate\Support\Facades\Route;

// Public Controllers
use Modules\Website\Http\Controllers\WebsiteController;
use Modules\Website\Http\Controllers\GuestController;

// Admin Controllers (Aliased to prevent conflicts)
use Modules\Website\Http\Controllers\Admin\WebsiteAdminController;
use Modules\Website\Http\Controllers\Admin\RoomController as AdminRoomController;
use Modules\Website\Http\Controllers\Admin\RoomTypeController;
use Modules\Website\Http\Controllers\Admin\BookingController as AdminBookingController;
use Modules\Website\Http\Controllers\Admin\DiningController;
use Modules\Website\Http\Controllers\Admin\AmenityController;
use Modules\Website\Http\Controllers\Admin\SettingController;
use Modules\Website\Http\Controllers\Admin\ContactMessageController;
use Modules\Website\Http\Controllers\Admin\NewsletterController;
use Modules\Website\Http\Controllers\Admin\InventoryCalendarController;

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
        Route::get('/website', 'index')->name('website.home');
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
        
        // Booking Flow: /book (Step 1: Room Selection) -> /booking (Step 2: Guest Details)
        Route::get('/book', 'bookStep1')->name('website.book'); // Step 1: Select rooms with cart
        Route::get('/booking', 'booking')->name('website.booking'); // Step 2: Guest details / checkout
        Route::post('/booking', 'storeBooking')->name('website.booking.store');
        Route::get('/payment/callback', 'verifyPayment')->name('website.payment.callback');
        Route::get('/booking/confirmation/{ref?}', 'confirmation')->name('website.booking.confirmation');

        // Form Submission
        Route::post('/contact/send', 'sendMessage')->name('website.contact.send');
        Route::post('/check-email', 'checkEmail')->name('website.checkEmail');
        Route::post('/booking/resend', 'resendConfirmation')->name('website.booking.resend');
        Route::post('/newsletter/subscribe', 'subscribeNewsletter')->name('website.newsletter.subscribe');

        // Booking Cart API
        Route::get('/api/available-units', 'getAvailableUnits')->name('website.api.available-units');
        Route::get('/api/room-availability', 'getRoomAvailability')->name('website.api.room-availability');
        Route::post('/cart/add', 'cartAdd')->name('website.cart.add');
        Route::put('/cart/update', 'cartUpdate')->name('website.cart.update');
        Route::delete('/cart/remove/{roomTypeId}', 'cartRemove')->name('website.cart.remove');
        Route::delete('/cart/clear', 'cartClear')->name('website.cart.clear');
        Route::get('/cart', 'cartGet')->name('website.cart.get');

        // Guest Booking Management
        Route::get('/my-booking', 'bookingLogin')->name('website.booking.login');
        Route::post('/my-booking/find', 'findBooking')->name('website.booking.find');
    });

    // =========================================================================
    // 2. GUEST DASHBOARD (Authenticated Users)
    // =========================================================================
    // Route::middleware(['auth'])->prefix('guest')->name('website.guest.')->group(function () {
    //     Route::controller(GuestController::class)->group(function () {
    //         Route::get('/dashboard', 'dashboard')->name('dashboard');

    //         Route::get('/profile', 'profile')->name('profile');
    //         Route::put('/profile', 'updateProfile')->name('profile.update');

    //         Route::get('/my-bookings', 'bookings')->name('bookings.index');
    //         Route::get('/my-bookings/{ref}', 'bookingDetails')->name('bookings.show');
    //         Route::post('/my-bookings/{ref}/cancel', 'cancelBooking')->name('bookings.cancel');
    //     });
    // });
    Route::middleware(['auth'])->prefix('guest')->name('guest.')->group(function () {
        Route::get('/dashboard', [GuestController::class, 'dashboard'])->name('dashboard');
        Route::get('/bookings', [GuestController::class, 'bookings'])->name('bookings');
        Route::post('/bookings/{id}/cancel', [GuestController::class, 'cancelBooking'])->name('bookings.cancel');

        // Profile Routes
        Route::get('/profile', [GuestController::class, 'profile'])->name('profile');
        Route::put('/profile', [GuestController::class, 'updateProfile'])->name('profile.update'); // New Route
    });

    // =========================================================================
    // 3. ADMIN MANAGEMENT ROUTES
    // =========================================================================
    // Access: http://your-site.com/website/admin
    Route::middleware(['auth']) // Add 'can:manage_website' here in production
        ->prefix('website/admin')
        ->name('website.admin.')
        ->group(function () {

            // Dashboard
            Route::get('/', [WebsiteAdminController::class, 'index'])->name('dashboard');

            // Image Deletion Route
            Route::delete('/rooms/image/{id}', [AdminRoomController::class, 'deleteImage'])
                ->name('rooms.image.delete');

            // Room Resource Route (Handles index, store, update, destroy)
            // Resource Management
            Route::resource('rooms', AdminRoomController::class); // Legacy - will be deprecated
            Route::resource('bookings', AdminBookingController::class);
            Route::resource('amenities', AmenityController::class);
            Route::resource('settings', SettingController::class);
            Route::resource('dining', DiningController::class);

            // Room Types & Units Management (NEW)
            // IMPORTANT: This route MUST be before the resource to avoid conflict
            Route::delete('room-types/images/{imageId}', [RoomTypeController::class, 'deleteImage'])
                ->name('room-types.images.destroy');
            Route::resource('room-types', RoomTypeController::class);
            
            // Room Units
            Route::post('room-types/{roomType}/units', [RoomTypeController::class, 'storeUnit'])
                ->name('room-types.units.store');
            Route::post('room-types/{roomType}/units/bulk', [RoomTypeController::class, 'bulkStoreUnits'])
                ->name('room-types.units.bulk');
            Route::put('room-units/{unit}', [RoomTypeController::class, 'updateUnit'])
                ->name('room-units.update');
            Route::delete('room-units/{unit}', [RoomTypeController::class, 'destroyUnit'])
                ->name('room-units.destroy');
            Route::get('/api/room-status', [AdminRoomController::class, 'getRoomStatus'])->name('api.room.status');
            Route::get('/calendar', [AdminRoomController::class, 'calendar'])->name('rooms.calendar');
            Route::get('/api/calendar-data', [AdminRoomController::class, 'getCalendarData'])->name('api.calendar.data');

            // =========================================================================
            // INVENTORY CALENDAR (Expedia-Style)
            // =========================================================================
            Route::prefix('inventory')->name('inventory.')->group(function () {
                Route::get('/', [InventoryCalendarController::class, 'index'])->name('index');
                Route::get('/api/data', [InventoryCalendarController::class, 'getInventoryData'])->name('api.data');
                Route::get('/api/blocks', [InventoryCalendarController::class, 'getBlocks'])->name('api.blocks');
                Route::post('/block', [InventoryCalendarController::class, 'applyBlock'])->name('block');
                Route::delete('/block', [InventoryCalendarController::class, 'removeBlock'])->name('block.remove');
                Route::post('/restrict', [InventoryCalendarController::class, 'applyRestriction'])->name('restrict');
                Route::post('/bulk', [InventoryCalendarController::class, 'bulkUpdate'])->name('bulk');
                Route::post('/open', [InventoryCalendarController::class, 'openRooms'])->name('open');
                Route::post('/stop-sell', [InventoryCalendarController::class, 'stopSell'])->name('stop-sell');
            });
            // Contact Messages (Read Only / Reply)
            Route::resource('contact-messages', ContactMessageController::class)
                ->only(['index', 'show', 'destroy', 'update']);

            // Newsletter Subscribers Management
            Route::get('newsletter', [NewsletterController::class, 'index'])->name('newsletter.index');
            Route::get('newsletter/export', [NewsletterController::class, 'export'])->name('newsletter.export');
            Route::delete('newsletter/{subscriber}', [NewsletterController::class, 'destroy'])->name('newsletter.destroy');
            Route::post('newsletter/{subscriber}/toggle', [NewsletterController::class, 'toggleStatus'])->name('newsletter.toggle');

            // Manual specific routes if Resources don't cover everything
            Route::post('/rooms/image/upload', [AdminRoomController::class, 'uploadImage'])->name('rooms.image.upload');
            Route::delete('/rooms/image/{id}', [AdminRoomController::class, 'deleteImage'])->name('rooms.image.delete');

            Route::post('/bookings/{id}/confirm', [AdminBookingController::class, 'confirm'])->name('bookings.confirm');
            Route::post('/bookings/{id}/cancel', [AdminBookingController::class, 'cancel'])->name('bookings.cancel');
            Route::post('/bookings/{id}/resend', [AdminBookingController::class, 'resendConfirmation'])->name('bookings.resend');
            Route::post('/bookings/{id}/move', [AdminBookingController::class, 'moveRoom'])->name('bookings.move');
        });
});
