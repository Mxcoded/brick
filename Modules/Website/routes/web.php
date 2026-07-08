<?php

use Illuminate\Support\Facades\Route;
// Public Controllers
use Modules\Website\Http\Controllers\Admin\AmenityController;
use Modules\Website\Http\Controllers\Admin\BookingController as AdminBookingController;
// Admin Controllers (Aliased to prevent conflicts)
use Modules\Website\Http\Controllers\Admin\DiningController;
use Modules\Website\Http\Controllers\Admin\FacilitiesPageController;
use Modules\Website\Http\Controllers\Admin\MeetingPageController;
use Modules\Website\Http\Controllers\Admin\OffersPageController;
use Modules\Website\Http\Controllers\Admin\RoomController as AdminRoomController;
use Modules\Website\Http\Controllers\Admin\RoomTypeController;
use Modules\Website\Http\Controllers\Admin\SettingController;
use Modules\Website\Http\Controllers\Admin\ContactMessageController;
use Modules\Website\Http\Controllers\Admin\NewsletterController;
use Modules\Website\Http\Controllers\Admin\InventoryCalendarController;
use Inertia\Inertia;
use Modules\Website\Http\Controllers\Admin\TestimonialController;
use Modules\Website\Http\Controllers\Admin\WebsiteAdminController;
use Modules\Website\Http\Controllers\GuestController;
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

Route::prefix('frontend')->group(function () {
  
    Route::get('/', [WebsiteController::class, 'index2'])->name('frontend.home');
    Route::get('/room', [WebsiteController::class, 'rooms2'])->name('room.new');

    Route::get('/about', function () {
        return Inertia::render('About');
    })->name('about.us');
    Route::get('/contact', function () {
        return Inertia::render('Contact');
    })->name('contact.us');
    Route::get('/spa', function () {
        return Inertia::render('Spa');
    })->name('spa.us');
    
    Route::get('/gallery', function () {
        return Inertia::render('Gallery');
    })->name('gallery.us');
});

     

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

            // Dashboard
            Route::get('/', [WebsiteAdminController::class, 'index'])->name('dashboard');

            // Bookings Management
            Route::resource('bookings', AdminBookingController::class)->middleware('permission:access_website_dashboard|website.bookings');
            Route::post('testimonials/{testimonial}/toggle-approve', [TestimonialController::class, 'toggleApprove'])
                ->name('testimonials.toggle-approve')
                ->middleware('permission:access_website_dashboard|website.testimonials');
            Route::resource('testimonials', TestimonialController::class)->middleware('permission:access_website_dashboard|website.testimonials');
            Route::resource('amenities', AmenityController::class)->middleware('permission:access_website_dashboard|website.amenities');
            Route::resource('settings', SettingController::class)->middleware('permission:access_website_dashboard|website.settings');
            Route::resource('dining', DiningController::class)->middleware('permission:access_website_dashboard|website.dining');
            Route::get('dining/{dining}/delete-pdf', [DiningController::class, 'deletePdf'])->name('dining.delete-pdf');
            Route::get('dining/{dining}/qr', [DiningController::class, 'qrCode'])->name('dining.qr');

            // Meetings Page Editor
            Route::prefix('meeting')->name('meeting.')->middleware('permission:access_website_dashboard|website.meeting')->group(function () {
                Route::get('/', [MeetingPageController::class, 'edit'])->name('edit');
                Route::post('/hero', [MeetingPageController::class, 'updateHero'])->name('update-hero');
                Route::post('/equipment-catering', [MeetingPageController::class, 'updateEquipmentCatering'])->name('update-equipment');
                Route::post('/contact', [MeetingPageController::class, 'updateContact'])->name('update-contact');
                Route::post('/rooms', [MeetingPageController::class, 'storeRoom'])->name('rooms.store');
                Route::post('/rooms/{room}', [MeetingPageController::class, 'updateRoom'])->name('rooms.update');
                Route::delete('/rooms/{room}', [MeetingPageController::class, 'destroyRoom'])->name('rooms.destroy');
                Route::post('/gallery', [MeetingPageController::class, 'storeGallery'])->name('gallery.store');
                Route::delete('/gallery/{gallery}', [MeetingPageController::class, 'destroyGallery'])->name('gallery.destroy');
            });

            // Facilities Page Editor
            Route::prefix('facilities')->name('facilities.')->middleware('permission:access_website_dashboard|website.facilities')->group(function () {
                Route::get('/', [FacilitiesPageController::class, 'edit'])->name('edit');
                Route::post('/hero', [FacilitiesPageController::class, 'updateHero'])->name('update-hero');
                Route::post('/items', [FacilitiesPageController::class, 'storeItem'])->name('items.store');
                Route::post('/items/{item}', [FacilitiesPageController::class, 'updateItem'])->name('items.update');
                Route::delete('/items/{item}', [FacilitiesPageController::class, 'destroyItem'])->name('items.destroy');
            });

            // Offers Page Editor
            Route::prefix('offers')->name('offers.')->middleware('permission:access_website_dashboard|website.offers')->group(function () {
                Route::get('/', [OffersPageController::class, 'edit'])->name('edit');
                Route::post('/hero', [OffersPageController::class, 'updateHero'])->name('update-hero');
                Route::post('/offers', [OffersPageController::class, 'storeOffer'])->name('offers.store');
                Route::post('/offers/{offer}', [OffersPageController::class, 'updateOffer'])->name('offers.update');
                Route::delete('/offers/{offer}', [OffersPageController::class, 'destroyOffer'])->name('offers.destroy');
            });

            // Room Types & Units Management
            Route::delete('room-types/images/{imageId}', [RoomTypeController::class, 'deleteImage'])
                ->name('room-types.images.destroy');
            Route::resource('room-types', RoomTypeController::class)->middleware('permission:access_website_dashboard|website.room-types');

            // Room Units
            Route::middleware('permission:access_website_dashboard|website.room-types')->group(function () {
                Route::post('room-types/{roomType}/units', [RoomTypeController::class, 'storeUnit'])
                    ->name('room-types.units.store');
                Route::post('room-types/{roomType}/units/bulk', [RoomTypeController::class, 'bulkStoreUnits'])
                    ->name('room-types.units.bulk');
                Route::put('room-units/{unit}', [RoomTypeController::class, 'updateUnit'])
                    ->name('room-units.update');
                Route::post('room-units/{unit}/move', [RoomTypeController::class, 'moveUnit'])
                    ->name('room-units.move');
                Route::delete('room-units/{unit}', [RoomTypeController::class, 'destroyUnit'])
                    ->name('room-units.destroy');
                Route::get('/api/room-status', [AdminRoomController::class, 'getRoomStatus'])->name('api.room.status');
                Route::get('/calendar', [AdminRoomController::class, 'calendar'])->name('rooms.calendar');
                Route::get('/api/calendar-data', [AdminRoomController::class, 'getCalendarData'])->name('api.calendar.data');
            });

            // =========================================================================
            // INVENTORY CALENDAR (Expedia-Style)
            // =========================================================================
            Route::prefix('inventory')->name('inventory.')->middleware('permission:access_website_dashboard|website.inventory')->group(function () {
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
                ->only(['index', 'show', 'destroy', 'update'])
                ->middleware('permission:access_website_dashboard|website.contact-messages');

            // Contact Message Conversation Routes
            Route::middleware('permission:access_website_dashboard|website.contact-messages')->group(function () {
                Route::get('contact-messages/{contact_message}/reply', [ContactMessageController::class, 'reply'])
                    ->name('contact-messages.reply');
                Route::post('contact-messages/{contact_message}/reply', [ContactMessageController::class, 'sendReply'])
                    ->name('contact-messages.send-reply');
                Route::post('contact-messages/{contact_message}/archive', [ContactMessageController::class, 'archive'])
                    ->name('contact-messages.archive');
                Route::post('contact-messages/{contact_message}/restore', [ContactMessageController::class, 'restore'])
                    ->name('contact-messages.restore');
            });

            // =========================================================================
            // NEWSLETTER MANAGEMENT
            // =========================================================================

            // Newsletter Campaigns
            Route::prefix('newsletter/campaigns')->name('newsletter.campaigns.')->middleware('permission:access_website_dashboard|website.newsletter')->group(function () {
                Route::get('/', [NewsletterController::class, 'index'])->name('index');
                Route::get('/create', [NewsletterController::class, 'create'])->name('create');
                Route::post('/', [NewsletterController::class, 'store'])->name('store');
                Route::get('/{campaign}', [NewsletterController::class, 'show'])->name('show');
                Route::get('/{campaign}/edit', [NewsletterController::class, 'edit'])->name('edit');
                Route::put('/{campaign}', [NewsletterController::class, 'update'])->name('update');
                Route::delete('/{campaign}', [NewsletterController::class, 'destroy'])->name('destroy');
                Route::get('/{campaign}/preview', [NewsletterController::class, 'preview'])->name('preview');
                Route::post('/{campaign}/send', [NewsletterController::class, 'send'])->name('send');
                Route::post('/{campaign}/duplicate', [NewsletterController::class, 'duplicate'])->name('duplicate');
                Route::post('/{campaign}/test', [NewsletterController::class, 'sendTest'])->name('test');

                // Real-time delivery status
                Route::get('/{campaign}/delivery-status', [NewsletterController::class, 'deliveryStatus'])->name('delivery-status');
                Route::get('/{campaign}/delivery-status/api', [NewsletterController::class, 'deliveryStatusApi'])->name('delivery-status.api');
                Route::post('/{campaign}/retry-failed', [NewsletterController::class, 'retryFailed'])->name('retry-failed');
            });

            // Newsletter Subscribers Management
            Route::middleware('permission:access_website_dashboard|website.newsletter')->group(function () {
                Route::get('newsletter/subscribers', [NewsletterController::class, 'subscribersIndex'])->name('newsletter.subscribers');
                Route::get('newsletter/subscribers/export', [NewsletterController::class, 'exportSubscribers'])->name('newsletter.subscribers.export');
                Route::post('newsletter/subscribers/import', [NewsletterController::class, 'importSubscribers'])->name('newsletter.subscribers.import');
                Route::get('newsletter/subscribers/import/sample', [NewsletterController::class, 'downloadSampleImport'])->name('newsletter.subscribers.import.sample');
                Route::delete('newsletter/subscribers/{subscriber}', [NewsletterController::class, 'destroySubscriber'])->name('newsletter.subscribers.destroy');
                Route::post('newsletter/subscribers/{subscriber}/toggle', [NewsletterController::class, 'toggleSubscriberStatus'])->name('newsletter.subscribers.toggle');
            });

            Route::post('/bookings/{id}/confirm', [AdminBookingController::class, 'confirm'])->name('bookings.confirm');
            Route::post('/bookings/{id}/cancel', [AdminBookingController::class, 'cancel'])->name('bookings.cancel');
            Route::post('/bookings/{id}/resend', [AdminBookingController::class, 'resendConfirmation'])->name('bookings.resend');
            Route::post('/bookings/{id}/assign-room', [AdminBookingController::class, 'assignRoom'])->name('bookings.assign-room');
            Route::post('/bookings/{id}/change-room-type', [AdminBookingController::class, 'changeRoomType'])->name('bookings.change-room-type');
            Route::post('/bookings/{id}/move', [AdminBookingController::class, 'moveRoom'])->name('bookings.move');
        });

        // =========================================================================
        // 3. GUEST PRE-ARRIVAL CHECK-IN
        // =========================================================================
        Route::prefix('guest')->name('guest.')->group(function () {
            Route::get('/pre-arrival', [\Modules\Website\Http\Controllers\PreArrivalController::class, 'index'])
                ->name('pre-arrival');
            Route::post('/pre-arrival/lookup', [\Modules\Website\Http\Controllers\PreArrivalController::class, 'lookup'])
                ->name('pre-arrival.lookup');
            Route::get('/pre-arrival/token/{token}', [\Modules\Website\Http\Controllers\PreArrivalController::class, 'token'])
                ->name('pre-arrival.token');
            Route::get('/pre-arrival/{registration}/details', [\Modules\Website\Http\Controllers\PreArrivalController::class, 'details'])
                ->name('pre-arrival.details');
            Route::put('/pre-arrival/{registration}/details', [\Modules\Website\Http\Controllers\PreArrivalController::class, 'updateDetails'])
                ->name('pre-arrival.update-details');
            Route::get('/pre-arrival/{registration}/documents', [\Modules\Website\Http\Controllers\PreArrivalController::class, 'documents'])
                ->name('pre-arrival.documents');
            Route::post('/pre-arrival/{registration}/documents/upload', [\Modules\Website\Http\Controllers\PreArrivalController::class, 'uploadDocument'])
                ->name('pre-arrival.upload-document');
            Route::delete('/pre-arrival/{registration}/documents/{document}', [\Modules\Website\Http\Controllers\PreArrivalController::class, 'deleteDocument'])
                ->name('pre-arrival.delete-document');
            Route::get('/pre-arrival/{registration}/signature', [\Modules\Website\Http\Controllers\PreArrivalController::class, 'signature'])
                ->name('pre-arrival.signature');
            Route::post('/pre-arrival/{registration}/signature', [\Modules\Website\Http\Controllers\PreArrivalController::class, 'submitSignature'])
                ->name('pre-arrival.submit-signature');
            Route::get('/pre-arrival/{registration}/confirmation', [\Modules\Website\Http\Controllers\PreArrivalController::class, 'confirmation'])
                ->name('pre-arrival.confirmation');
        });
    });
