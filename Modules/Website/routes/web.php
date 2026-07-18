<?php

use Illuminate\Support\Facades\Route;
// Public Controllers
use Modules\Website\Http\Controllers\Admin\AmenityController;
use Modules\Website\Http\Controllers\Admin\BookingController as AdminBookingController;
// Admin Controllers (Aliased to prevent conflicts)
use Modules\Website\Http\Controllers\Admin\ContactMessageController;
use Modules\Website\Http\Controllers\Admin\DiningController;
use Modules\Website\Http\Controllers\Admin\FacilitiesPageController;
use Modules\Website\Http\Controllers\Admin\InventoryCalendarController;
use Modules\Website\Http\Controllers\Admin\MeetingPageController;
use Modules\Website\Http\Controllers\Admin\NewsletterController;
use Modules\Website\Http\Controllers\Admin\OffersPageController;
use Modules\Website\Http\Controllers\Admin\RoomController as AdminRoomController;
use Modules\Website\Http\Controllers\Admin\RoomTypeController;
use Modules\Website\Http\Controllers\Admin\SettingController;
use Modules\Website\Http\Controllers\Admin\TestimonialController;
use Modules\Website\Http\Controllers\Admin\WebsiteAdminController;
use Modules\Website\Http\Controllers\GuestController;
use Modules\Website\Http\Controllers\PreArrivalController;
use Modules\Website\Http\Controllers\WebsiteController;

/*
|--------------------------------------------------------------------------
| Website Module Routes
|--------------------------------------------------------------------------
|
| Prefix: /website (defined in RouteServiceProvider)
|
*/

// =========================================================================
// PAYSTACK WEBHOOK (Must be outside web middleware - no CSRF)
// =========================================================================
Route::post('/paystack/webhook', [WebsiteController::class, 'paystackWebhook'])
    ->name('website.paystack.webhook')
    ->withoutMiddleware(['web', 'csrf']);

Route::middleware(['web', 'website.property'])->group(function () {

    // =========================================================================
    // 1. PUBLIC WEBSITE ROUTES
    // =========================================================================
    Route::controller(WebsiteController::class)->group(function () {
        // Core Pages
        Route::get('/website', 'index')->name('website.home');
        Route::get('/about-us', 'about')->name('website.about');
        Route::get('/contact-us', 'contact')->name('website.contact');
        Route::get('/testimonials', 'testimonials')->name('website.testimonials');
        Route::post('/testimonials', 'storeTestimonial')->name('website.testimonials.store');
        Route::get('/location', 'location')->name('website.location');
        Route::get('/dining', 'dining')->name('website.dining');
        Route::get('/dining/{dining}/menu', 'diningMenu')->name('website.dining.menu');
        Route::get('/amenities', 'amenities')->name('website.amenities');
        Route::get('/facilities', 'facilities')->name('website.facilities');
        Route::get('/offers', 'offers')->name('website.offers');

        // Rooms & Booking
        Route::get('/rooms', 'rooms')->name('website.rooms.index');
        Route::get('/rooms/{slug}', 'roomDetails')->name('website.rooms.show');

        // Availability & Booking Logic
        Route::any('/check-availability', 'checkAvailability')->name('website.room.checkAvailability');

        // Booking Flow: /book (Step 1: Room Selection) -> /booking (Step 2: Guest Details)
        Route::get('/book', 'bookStep1')->name('website.book'); // Step 1: Select rooms with cart
        Route::get('/booking', 'booking')->name('website.booking'); // Step 2: Guest details / checkout
        Route::post('/booking', 'storeBooking')->name('website.booking.store')->middleware('throttle:5,60');
        Route::get('/payment/callback', 'verifyPayment')->name('website.payment.callback');
        Route::get('/booking/confirmation/{ref?}', 'confirmation')->name('website.booking.confirmation');

        // Meetings Landing Page (Banquet)
        Route::get('/meetings', 'meetings')->name('website.meetings');

        // Meeting Enquiry (Banquet Quote Request)
        Route::get('/meeting-enquiry', 'meetingEnquiry')->name('website.meeting-enquiry');
        Route::post('/meeting-enquiry', 'storeEnquiry')->name('website.meeting-enquiry.store');

        // Event Lead Capture (Public Form)
        Route::get('/event-interest/{slug}', 'eventLead')->name('website.event-lead');
        Route::post('/event-interest/{slug}', 'storeEventLead')->name('website.event-lead.store');

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

    // Sitemap
    Route::get('/sitemap.xml', [WebsiteController::class, 'sitemap'])->name('website.sitemap');

    // Public Newsletter Routes (No Auth Required)
    Route::get('/newsletter/unsubscribe/{token}', [NewsletterController::class, 'unsubscribe'])
        ->name('website.newsletter.unsubscribe');

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
    Route::middleware(['auth', 'can:access_website_dashboard'])
        ->prefix('website/admin')
        ->name('website.admin.')
        ->group(function () {

            // Helper: permission shortcut for website resource actions
            $p = fn ($resource, $action) => 'permission:access_website_dashboard|website.'.$resource.'.'.$action;

            // Dashboard
            Route::get('/', [WebsiteAdminController::class, 'index'])->name('dashboard')->middleware($p('dashboard', 'read'));

            // Bookings Management
            Route::get('bookings', [AdminBookingController::class, 'index'])->name('bookings.index')->middleware($p('bookings', 'read'));
            Route::get('bookings/create', [AdminBookingController::class, 'create'])->name('bookings.create')->middleware($p('bookings', 'create'));
            Route::post('bookings', [AdminBookingController::class, 'store'])->name('bookings.store')->middleware($p('bookings', 'create'));
            Route::get('bookings/{booking}', [AdminBookingController::class, 'show'])->name('bookings.show')->middleware($p('bookings', 'read'));
            Route::get('bookings/{booking}/edit', [AdminBookingController::class, 'edit'])->name('bookings.edit')->middleware($p('bookings', 'update'));
            Route::match(['put', 'patch'], 'bookings/{booking}', [AdminBookingController::class, 'update'])->name('bookings.update')->middleware($p('bookings', 'update'));
            Route::delete('bookings/{booking}', [AdminBookingController::class, 'destroy'])->name('bookings.destroy')->middleware($p('bookings', 'delete'));
            // Testimonials
            Route::post('testimonials/{testimonial}/toggle-approve', [TestimonialController::class, 'toggleApprove'])
                ->name('testimonials.toggle-approve')->middleware($p('testimonials', 'update'));
            Route::get('testimonials', [TestimonialController::class, 'index'])->name('testimonials.index')->middleware($p('testimonials', 'read'));
            Route::get('testimonials/create', [TestimonialController::class, 'create'])->name('testimonials.create')->middleware($p('testimonials', 'create'));
            Route::post('testimonials', [TestimonialController::class, 'store'])->name('testimonials.store')->middleware($p('testimonials', 'create'));
            Route::get('testimonials/{testimonial}', [TestimonialController::class, 'show'])->name('testimonials.show')->middleware($p('testimonials', 'read'));
            Route::get('testimonials/{testimonial}/edit', [TestimonialController::class, 'edit'])->name('testimonials.edit')->middleware($p('testimonials', 'update'));
            Route::match(['put', 'patch'], 'testimonials/{testimonial}', [TestimonialController::class, 'update'])->name('testimonials.update')->middleware($p('testimonials', 'update'));
            Route::delete('testimonials/{testimonial}', [TestimonialController::class, 'destroy'])->name('testimonials.destroy')->middleware($p('testimonials', 'delete'));
            // Amenities
            Route::get('amenities', [AmenityController::class, 'index'])->name('amenities.index')->middleware($p('amenities', 'read'));
            Route::get('amenities/create', [AmenityController::class, 'create'])->name('amenities.create')->middleware($p('amenities', 'create'));
            Route::post('amenities', [AmenityController::class, 'store'])->name('amenities.store')->middleware($p('amenities', 'create'));
            Route::get('amenities/{amenity}', [AmenityController::class, 'show'])->name('amenities.show')->middleware($p('amenities', 'read'));
            Route::get('amenities/{amenity}/edit', [AmenityController::class, 'edit'])->name('amenities.edit')->middleware($p('amenities', 'update'));
            Route::match(['put', 'patch'], 'amenities/{amenity}', [AmenityController::class, 'update'])->name('amenities.update')->middleware($p('amenities', 'update'));
            Route::delete('amenities/{amenity}', [AmenityController::class, 'destroy'])->name('amenities.destroy')->middleware($p('amenities', 'delete'));
            // Settings
            Route::get('settings', [SettingController::class, 'index'])->name('settings.index')->middleware($p('settings', 'read'));
            Route::get('settings/create', [SettingController::class, 'create'])->name('settings.create')->middleware($p('settings', 'create'));
            Route::post('settings', [SettingController::class, 'store'])->name('settings.store')->middleware($p('settings', 'create'));
            Route::get('settings/{setting}', [SettingController::class, 'show'])->name('settings.show')->middleware($p('settings', 'read'));
            Route::get('settings/{setting}/edit', [SettingController::class, 'edit'])->name('settings.edit')->middleware($p('settings', 'update'));
            Route::match(['put', 'patch'], 'settings/{setting}', [SettingController::class, 'update'])->name('settings.update')->middleware($p('settings', 'update'));
            Route::delete('settings/{setting}', [SettingController::class, 'destroy'])->name('settings.destroy')->middleware($p('settings', 'delete'));
            // Dining
            Route::get('dining', [DiningController::class, 'index'])->name('dining.index')->middleware($p('dining', 'read'));
            Route::get('dining/create', [DiningController::class, 'create'])->name('dining.create')->middleware($p('dining', 'create'));
            Route::post('dining', [DiningController::class, 'store'])->name('dining.store')->middleware($p('dining', 'create'));
            Route::get('dining/{dining}', [DiningController::class, 'show'])->name('dining.show')->middleware($p('dining', 'read'));
            Route::get('dining/{dining}/edit', [DiningController::class, 'edit'])->name('dining.edit')->middleware($p('dining', 'update'));
            Route::match(['put', 'patch'], 'dining/{dining}', [DiningController::class, 'update'])->name('dining.update')->middleware($p('dining', 'update'));
            Route::delete('dining/{dining}', [DiningController::class, 'destroy'])->name('dining.destroy')->middleware($p('dining', 'delete'));
            Route::get('dining/{dining}/delete-pdf', [DiningController::class, 'deletePdf'])->name('dining.delete-pdf')->middleware($p('dining', 'update'));
            Route::get('dining/{dining}/qr', [DiningController::class, 'qrCode'])->name('dining.qr')->middleware($p('dining', 'read'));

            // Meetings Page Editor
            Route::prefix('meeting')->name('meeting.')->group(function () use ($p) {
                Route::get('/', [MeetingPageController::class, 'edit'])->name('edit')->middleware($p('meeting', 'read'));
                Route::post('/hero', [MeetingPageController::class, 'updateHero'])->name('update-hero')->middleware($p('meeting', 'update'));
                Route::post('/equipment-catering', [MeetingPageController::class, 'updateEquipmentCatering'])->name('update-equipment')->middleware($p('meeting', 'update'));
                Route::post('/contact', [MeetingPageController::class, 'updateContact'])->name('update-contact')->middleware($p('meeting', 'update'));
                Route::post('/rooms', [MeetingPageController::class, 'storeRoom'])->name('rooms.store')->middleware($p('meeting', 'create'));
                Route::post('/rooms/{room}', [MeetingPageController::class, 'updateRoom'])->name('rooms.update')->middleware($p('meeting', 'update'));
                Route::delete('/rooms/{room}', [MeetingPageController::class, 'destroyRoom'])->name('rooms.destroy')->middleware($p('meeting', 'delete'));
                Route::post('/gallery', [MeetingPageController::class, 'storeGallery'])->name('gallery.store')->middleware($p('meeting', 'create'));
                Route::delete('/gallery/{gallery}', [MeetingPageController::class, 'destroyGallery'])->name('gallery.destroy')->middleware($p('meeting', 'delete'));
            });

            // Facilities Page Editor
            Route::prefix('facilities')->name('facilities.')->group(function () use ($p) {
                Route::get('/', [FacilitiesPageController::class, 'edit'])->name('edit')->middleware($p('facilities', 'read'));
                Route::post('/hero', [FacilitiesPageController::class, 'updateHero'])->name('update-hero')->middleware($p('facilities', 'update'));
                Route::post('/items', [FacilitiesPageController::class, 'storeItem'])->name('items.store')->middleware($p('facilities', 'create'));
                Route::post('/items/{item}', [FacilitiesPageController::class, 'updateItem'])->name('items.update')->middleware($p('facilities', 'update'));
                Route::delete('/items/{item}', [FacilitiesPageController::class, 'destroyItem'])->name('items.destroy')->middleware($p('facilities', 'delete'));
            });

            // Offers Page Editor
            Route::prefix('offers')->name('offers.')->group(function () use ($p) {
                Route::get('/', [OffersPageController::class, 'edit'])->name('edit')->middleware($p('offers', 'read'));
                Route::post('/hero', [OffersPageController::class, 'updateHero'])->name('update-hero')->middleware($p('offers', 'update'));
                Route::post('/offers', [OffersPageController::class, 'storeOffer'])->name('offers.store')->middleware($p('offers', 'create'));
                Route::post('/offers/{offer}', [OffersPageController::class, 'updateOffer'])->name('offers.update')->middleware($p('offers', 'update'));
                Route::delete('/offers/{offer}', [OffersPageController::class, 'destroyOffer'])->name('offers.destroy')->middleware($p('offers', 'delete'));
            });

            // Room Types & Units Management
            Route::delete('room-types/images/{imageId}', [RoomTypeController::class, 'deleteImage'])
                ->name('room-types.images.destroy')->middleware($p('room-types', 'update'));
            Route::get('room-types', [RoomTypeController::class, 'index'])->name('room-types.index')->middleware($p('room-types', 'read'));
            Route::get('room-types/create', [RoomTypeController::class, 'create'])->name('room-types.create')->middleware($p('room-types', 'create'));
            Route::post('room-types', [RoomTypeController::class, 'store'])->name('room-types.store')->middleware($p('room-types', 'create'));
            Route::get('room-types/{room_type}', [RoomTypeController::class, 'show'])->name('room-types.show')->middleware($p('room-types', 'read'));
            Route::get('room-types/{room_type}/edit', [RoomTypeController::class, 'edit'])->name('room-types.edit')->middleware($p('room-types', 'update'));
            Route::match(['put', 'patch'], 'room-types/{room_type}', [RoomTypeController::class, 'update'])->name('room-types.update')->middleware($p('room-types', 'update'));
            Route::delete('room-types/{room_type}', [RoomTypeController::class, 'destroy'])->name('room-types.destroy')->middleware($p('room-types', 'delete'));

            // Room Units
            Route::post('room-types/{roomType}/units', [RoomTypeController::class, 'storeUnit'])
                ->name('room-types.units.store')->middleware($p('room-types', 'create'));
            Route::post('room-types/{roomType}/units/bulk', [RoomTypeController::class, 'bulkStoreUnits'])
                ->name('room-types.units.bulk')->middleware($p('room-types', 'create'));
            Route::put('room-units/{unit}', [RoomTypeController::class, 'updateUnit'])
                ->name('room-units.update')->middleware($p('room-types', 'update'));
            Route::post('room-units/{unit}/move', [RoomTypeController::class, 'moveUnit'])
                ->name('room-units.move')->middleware($p('room-types', 'update'));
            Route::delete('room-units/{unit}', [RoomTypeController::class, 'destroyUnit'])
                ->name('room-units.destroy')->middleware($p('room-types', 'delete'));
            Route::get('/api/room-status', [AdminRoomController::class, 'getRoomStatus'])->name('api.room.status')->middleware($p('room-types', 'read'));
            Route::get('/calendar', [AdminRoomController::class, 'calendar'])->name('rooms.calendar')->middleware($p('room-types', 'read'));
            Route::get('/api/calendar-data', [AdminRoomController::class, 'getCalendarData'])->name('api.calendar.data')->middleware($p('room-types', 'read'));

            // =========================================================================
            // INVENTORY CALENDAR (Expedia-Style)
            // =========================================================================
            Route::prefix('inventory')->name('inventory.')->group(function () use ($p) {
                Route::get('/', [InventoryCalendarController::class, 'index'])->name('index')->middleware($p('inventory', 'read'));
                Route::get('/api/data', [InventoryCalendarController::class, 'getInventoryData'])->name('api.data')->middleware($p('inventory', 'read'));
                Route::get('/api/blocks', [InventoryCalendarController::class, 'getBlocks'])->name('api.blocks')->middleware($p('inventory', 'read'));
                Route::post('/block', [InventoryCalendarController::class, 'applyBlock'])->name('block')->middleware($p('inventory', 'create'));
                Route::delete('/block', [InventoryCalendarController::class, 'removeBlock'])->name('block.remove')->middleware($p('inventory', 'delete'));
                Route::post('/restrict', [InventoryCalendarController::class, 'applyRestriction'])->name('restrict')->middleware($p('inventory', 'update'));
                Route::post('/bulk', [InventoryCalendarController::class, 'bulkUpdate'])->name('bulk')->middleware($p('inventory', 'update'));
                Route::post('/open', [InventoryCalendarController::class, 'openRooms'])->name('open')->middleware($p('inventory', 'delete'));
                Route::post('/stop-sell', [InventoryCalendarController::class, 'stopSell'])->name('stop-sell')->middleware($p('inventory', 'update'));
            });
            // Contact Messages
            Route::get('contact-messages', [ContactMessageController::class, 'index'])->name('contact-messages.index')->middleware($p('contact-messages', 'read'));
            Route::get('contact-messages/{contact_message}', [ContactMessageController::class, 'show'])->name('contact-messages.show')->middleware($p('contact-messages', 'read'));
            Route::match(['put', 'patch'], 'contact-messages/{contact_message}', [ContactMessageController::class, 'update'])->name('contact-messages.update')->middleware($p('contact-messages', 'update'));
            Route::delete('contact-messages/{contact_message}', [ContactMessageController::class, 'destroy'])->name('contact-messages.destroy')->middleware($p('contact-messages', 'delete'));
            Route::get('contact-messages/{contact_message}/reply', [ContactMessageController::class, 'reply'])
                ->name('contact-messages.reply')->middleware($p('contact-messages', 'update'));
            Route::post('contact-messages/{contact_message}/reply', [ContactMessageController::class, 'sendReply'])
                ->name('contact-messages.send-reply')->middleware($p('contact-messages', 'update'));
            Route::post('contact-messages/{contact_message}/archive', [ContactMessageController::class, 'archive'])
                ->name('contact-messages.archive')->middleware($p('contact-messages', 'update'));
            Route::post('contact-messages/{contact_message}/restore', [ContactMessageController::class, 'restore'])
                ->name('contact-messages.restore')->middleware($p('contact-messages', 'update'));

            // =========================================================================
            // NEWSLETTER MANAGEMENT
            // =========================================================================

            // Newsletter Campaigns
            Route::prefix('newsletter/campaigns')->name('newsletter.campaigns.')->group(function () use ($p) {
                Route::get('/', [NewsletterController::class, 'index'])->name('index')->middleware($p('newsletter', 'read'));
                Route::get('/create', [NewsletterController::class, 'create'])->name('create')->middleware($p('newsletter', 'create'));
                Route::post('/', [NewsletterController::class, 'store'])->name('store')->middleware($p('newsletter', 'create'));
                Route::get('/{campaign}', [NewsletterController::class, 'show'])->name('show')->middleware($p('newsletter', 'read'));
                Route::get('/{campaign}/edit', [NewsletterController::class, 'edit'])->name('edit')->middleware($p('newsletter', 'update'));
                Route::match(['put', 'patch'], '/{campaign}', [NewsletterController::class, 'update'])->name('update')->middleware($p('newsletter', 'update'));
                Route::delete('/{campaign}', [NewsletterController::class, 'destroy'])->name('destroy')->middleware($p('newsletter', 'delete'));
                Route::get('/{campaign}/preview', [NewsletterController::class, 'preview'])->name('preview')->middleware($p('newsletter', 'read'));
                Route::post('/{campaign}/send', [NewsletterController::class, 'send'])->name('send')->middleware($p('newsletter', 'update'));
                Route::post('/{campaign}/duplicate', [NewsletterController::class, 'duplicate'])->name('duplicate')->middleware($p('newsletter', 'create'));
                Route::post('/{campaign}/test', [NewsletterController::class, 'sendTest'])->name('test')->middleware($p('newsletter', 'update'));
                Route::get('/{campaign}/delivery-status', [NewsletterController::class, 'deliveryStatus'])->name('delivery-status')->middleware($p('newsletter', 'read'));
                Route::get('/{campaign}/delivery-status/api', [NewsletterController::class, 'deliveryStatusApi'])->name('delivery-status.api')->middleware($p('newsletter', 'read'));
                Route::post('/{campaign}/retry-failed', [NewsletterController::class, 'retryFailed'])->name('retry-failed')->middleware($p('newsletter', 'update'));
            });

            // Newsletter Subscribers Management
            Route::get('newsletter/subscribers', [NewsletterController::class, 'subscribersIndex'])->name('newsletter.subscribers')->middleware($p('subscribers', 'read'));
            Route::get('newsletter/subscribers/export', [NewsletterController::class, 'exportSubscribers'])->name('newsletter.subscribers.export')->middleware($p('subscribers', 'read'));
            Route::post('newsletter/subscribers/import', [NewsletterController::class, 'importSubscribers'])->name('newsletter.subscribers.import')->middleware($p('subscribers', 'create'));
            Route::get('newsletter/subscribers/import/sample', [NewsletterController::class, 'downloadSampleImport'])->name('newsletter.subscribers.import.sample')->middleware($p('subscribers', 'read'));
            Route::delete('newsletter/subscribers/{subscriber}', [NewsletterController::class, 'destroySubscriber'])->name('newsletter.subscribers.destroy')->middleware($p('subscribers', 'delete'));
            Route::post('newsletter/subscribers/{subscriber}/toggle', [NewsletterController::class, 'toggleSubscriberStatus'])->name('newsletter.subscribers.toggle')->middleware($p('subscribers', 'update'));

            Route::post('/bookings/{id}/confirm', [AdminBookingController::class, 'confirm'])->name('bookings.confirm')->middleware($p('bookings', 'update'));
            Route::post('/bookings/{id}/cancel', [AdminBookingController::class, 'cancel'])->name('bookings.cancel')->middleware($p('bookings', 'update'));
            Route::post('/bookings/{id}/resend', [AdminBookingController::class, 'resendConfirmation'])->name('bookings.resend')->middleware($p('bookings', 'update'));
            Route::post('/bookings/{id}/assign-room', [AdminBookingController::class, 'assignRoom'])->name('bookings.assign-room')->middleware($p('bookings', 'update'));
            Route::post('/bookings/{id}/change-room-type', [AdminBookingController::class, 'changeRoomType'])->name('bookings.change-room-type')->middleware($p('bookings', 'update'));
            Route::post('/bookings/{id}/move', [AdminBookingController::class, 'moveRoom'])->name('bookings.move')->middleware($p('bookings', 'update'));
        });

    // =========================================================================
    // 3. GUEST PRE-ARRIVAL CHECK-IN
    // =========================================================================
    Route::prefix('guest')->name('guest.')->group(function () {
        Route::get('/pre-arrival', [PreArrivalController::class, 'index'])
            ->name('pre-arrival');
        Route::post('/pre-arrival/lookup', [PreArrivalController::class, 'lookup'])
            ->name('pre-arrival.lookup');
        Route::get('/pre-arrival/token/{token}', [PreArrivalController::class, 'token'])
            ->name('pre-arrival.token');
        Route::get('/pre-arrival/{registration}/details', [PreArrivalController::class, 'details'])
            ->name('pre-arrival.details');
        Route::put('/pre-arrival/{registration}/details', [PreArrivalController::class, 'updateDetails'])
            ->name('pre-arrival.update-details');
        Route::get('/pre-arrival/{registration}/documents', [PreArrivalController::class, 'documents'])
            ->name('pre-arrival.documents');
        Route::post('/pre-arrival/{registration}/documents/upload', [PreArrivalController::class, 'uploadDocument'])
            ->name('pre-arrival.upload-document');
        Route::delete('/pre-arrival/{registration}/documents/{document}', [PreArrivalController::class, 'deleteDocument'])
            ->name('pre-arrival.delete-document');
        Route::get('/pre-arrival/{registration}/signature', [PreArrivalController::class, 'signature'])
            ->name('pre-arrival.signature');
        Route::post('/pre-arrival/{registration}/signature', [PreArrivalController::class, 'submitSignature'])
            ->name('pre-arrival.submit-signature');
        Route::get('/pre-arrival/{registration}/confirmation', [PreArrivalController::class, 'confirmation'])
            ->name('pre-arrival.confirmation');
    });
});
