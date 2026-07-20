<?php

namespace Modules\Website\Http\Controllers;

use App\Enums\RoleEnum;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
// use Modules\Website\Models\GuestProfile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Str;
use Modules\Banquet\Mail\EventLeadConfirmation;
use Modules\Banquet\Models\BanquetEnquiry;
use Modules\Banquet\Models\EventLead;
use Modules\Banquet\Models\LeadEvent;
use Modules\Banquet\Notifications\NewEnquiryNotification;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Rules\ValidEmail;
use Modules\Frontdeskcrm\Rules\ValidPhoneNumber;
use Modules\Website\Emails\BookingConfirmation; // ✅ Import Contact Mail
use Modules\Website\Emails\ContactMessageReceived;
use Modules\Website\Emails\ReviewSubmitted;
use Modules\Website\Models\Amenity; // ✅ Import Booking Mail
use Modules\Website\Models\Booking;
use Modules\Website\Models\ContactMessage;
use Modules\Website\Models\Dining; // ✅ Import Contact Mail
use Modules\Website\Models\FacilitiesPage;
use Modules\Website\Models\MeetingPage;
use Modules\Website\Models\NewsletterSubscriber;
use Modules\Website\Models\OffersPage;
use Modules\Website\Models\Room;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\Settings;
use Modules\Website\Models\Testimonial;
use Modules\Website\Services\BookingCartService;
use Modules\Website\Services\GoogleReviewsService;
use Modules\Website\Services\PaymentGatewayManager;
use Modules\Website\Services\RoomAvailabilityService;

class WebsiteController extends Controller
{
    public function index(GoogleReviewsService $googleReviews)
    {
        $settings = Settings::pluck('value', 'key')->toArray();

        $featuredRooms = RoomType::where('is_featured', true)
            ->where('is_active', true)
            ->withCount('units')
            ->with('amenities')
            ->ordered()
            ->get();

        $stayReviews = Testimonial::approved()->stay()->latest()->get();
        $restaurantReviews = Testimonial::approved()->restaurant()->latest()->get();
        $eventReviews = Testimonial::approved()->event()->latest()->get();
        $testimonials = $stayReviews; // keep backward compat for testimonials section

        $dining = Dining::all();

        $googleReviewsData = $googleReviews->fetch();
        $averageRating = round($stayReviews->avg('rating'), 1);
        $reviewCount = $stayReviews->count();

        $meta_description = 'Brickspoint Boutique Aparthotel — the best boutique hotel in Asokoro, Abuja. Experience luxury short & long stays with world-class amenities, exceptional service, and a home away from home in Nigeria\'s capital.';
        $meta_keywords = 'best boutique hotel Asokoro Abuja, luxury apart-hotel Nigeria, Brickspoint Abuja, Asokoro hotel, short let Abuja, extended stay Abuja, corporate housing Abuja, Abuja aparthotel, premium accommodation Abuja';
        $og_title = config('app.name', 'Brickspoint Boutique Aparthotel').' — Best Boutique Hotel in Asokoro, Abuja';

        return view('website::index', compact('settings', 'featuredRooms', 'testimonials', 'restaurantReviews', 'eventReviews', 'dining', 'googleReviewsData', 'averageRating', 'reviewCount', 'meta_description', 'meta_keywords', 'og_title'));
    }

    /**
     * Display the rooms page with filtering (NOW uses RoomType).
     */
    public function rooms(Request $request)
    {
        // 1. Base Query - Room Types (not individual rooms)
        $query = RoomType::where('is_active', true)
            ->withCount('units')
            ->with(['amenities', 'units']);

        // 2. Search (Name/Description)
        $query->when($request->filled('search'), function ($q) use ($request) {
            $q->where(function ($sub) use ($request) {
                $sub->where('name', 'like', '%'.$request->search.'%')
                    ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        });

        // 3. Filter by Min Price
        $query->when($request->filled('min_price'), function ($q) use ($request) {
            $q->where('price', '>=', $request->min_price);
        });

        // 4. Filter by Max Price
        $query->when($request->filled('max_price'), function ($q) use ($request) {
            $q->where('price', '<=', $request->max_price);
        });

        // 5. Filter by Guests
        $query->when($request->filled('guests'), function ($q) use ($request) {
            $q->where('capacity', '>=', $request->guests);
        });

        // 6. Sorting
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                default:
                    $query->ordered();
                    break;
            }
        } else {
            $query->ordered();
        }

        // 7. Pagination
        $roomTypes = $query->paginate(10)->withQueryString();

        // 8. If dates provided, calculate availability for each type
        $checkIn = $request->check_in;
        $checkOut = $request->check_out;

        $meta_description = 'Browse our premium rooms, suites, and serviced apartments at Brickspoint Boutique Aparthotel in Asokoro, Abuja. Find the perfect accommodation — from deluxe rooms to presidential suites — for your stay in Nigeria\'s capital.';
        $meta_keywords = 'rooms Asokoro Abuja, suites Abuja, serviced apartments Abuja, luxury hotel rooms Abuja, Brickspoint suites, presidential suite Abuja, deluxe room Abuja';
        $og_title = 'Rooms & Suites — Brickspoint Boutique Aparthotel Asokoro, Abuja';

        return view('website::rooms', compact('roomTypes', 'checkIn', 'checkOut', 'meta_description', 'meta_keywords', 'og_title'));
    }

    /**
     * Show details for a specific room type.
     */
    public function roomDetails($slug)
    {
        $roomType = is_numeric($slug)
            ? RoomType::with(['amenities', 'images', 'units'])->findOrFail($slug)
            : RoomType::with(['amenities', 'images', 'units'])->where('slug', $slug)->firstOrFail();

        $relatedRooms = RoomType::where('id', '!=', $roomType->id)
            ->where('is_active', true)
            ->with('amenities')
            ->inRandomOrder()
            ->take(3)
            ->get();

        $meta_description = strip_tags($roomType->short_description ?? $roomType->description ?? '').' — Book the '.$roomType->name.' at Brickspoint Boutique Aparthotel, Abuja.';
        $meta_keywords = strtolower($roomType->name).', '.($roomType->amenities->pluck('name')->implode(', ') ?? 'luxury rooms Abuja');
        $og_title = $roomType->name.' — '.config('app.name', 'Brickspoint Boutique Aparthotel');
        $og_image = $roomType->images->first()?->url ?? asset('images/og-default.jpg');

        return view('website::room-details', compact('roomType', 'relatedRooms', 'meta_description', 'meta_keywords', 'og_title', 'og_image'));
    }

    /**
     * Show the Booking Form (GET) - Step 2: Guest Details
     * Supports both cart-based multi-room booking and legacy single-room booking.
     * Redirects to /book if no rooms are selected.
     */
    public function booking(Request $request)
    {
        $cartService = new BookingCartService;
        $cart = $cartService->getCartSummary();

        // Fetch existing guest profile for logged-in users
        $guest = null;
        if (Auth::check()) {
            $guest = Guest::where('user_id', Auth::id())->first() ?? new Guest;
        }

        $viewData = compact('guest');

        $meta_description = 'Book your stay at Brickspoint Boutique Aparthotel in Asokoro, Abuja — the best boutique hotel in Nigeria\'s capital. Secure your room, suite, or apartment with our easy online reservation system.';
        $meta_keywords = 'book hotel Abuja, apart-hotel reservation, online booking Abuja, Brickspoint booking, Asokoro hotel booking';
        $og_title = 'Book Your Stay — Brickspoint Boutique Aparthotel Asokoro, Abuja';
        $viewData['meta_description'] = $meta_description;
        $viewData['meta_keywords'] = $meta_keywords;
        $viewData['og_title'] = $og_title;

        // If cart has items, use cart-based booking flow
        if (! empty($cart['items'])) {
            // Validate cart availability before showing form
            $unavailable = $cartService->validateAvailability();
            if (! empty($unavailable)) {
                return redirect()->route('website.book')
                    ->with('error', 'Some rooms in your cart are no longer available. Please review your selection.');
            }

            return view('website::booking', $viewData + [
                'cart' => $cart,
                'roomTypes' => collect(),
                'selectedRoomType' => null,
                'useCart' => true,
            ]);
        }

        // Check if room_type_id is provided
        $roomTypeId = old('room_type_id', $request->room_type_id);

        // If no cart and no room selected, redirect to room selection page
        if (! $roomTypeId) {
            return redirect()->route('website.book')
                ->with('info', 'Please select your rooms first.');
        }

        // Legacy: Single room booking flow (direct link from room details page)
        $roomTypes = RoomType::where('is_active', true)
            ->withCount('units')
            ->ordered()
            ->get();

        $selectedRoomType = RoomType::find($roomTypeId);

        return view('website::booking', $viewData + [
            'cart' => $cart,
            'roomTypes' => $roomTypes,
            'selectedRoomType' => $selectedRoomType,
            'useCart' => false,
        ]);
    }

    public function checkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Check if email exists in the Users table
        $exists = User::where('email', $request->email)->exists();

        return response()->json(['exists' => $exists]);
    }

    /**
     * API: Get available units for a room type and dates.
     * Uses unified RoomAvailabilityService for comprehensive checking.
     */
    public function getAvailableUnits(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        $availabilityService = app(RoomAvailabilityService::class);
        $result = $availabilityService->checkRoomTypeAvailability(
            $validated['room_type_id'],
            $validated['check_in_date'],
            $validated['check_out_date']
        );

        // If not available due to restrictions, return error with reason
        if (! $result['available']) {
            return response()->json([
                'available' => false,
                'count' => 0,
                'units' => [],
                'message' => $result['message'],
                'reason' => $result['reason'] ?? 'unavailable',
            ]);
        }

        return response()->json([
            'available' => true,
            'units' => $result['units']->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'room_number' => $unit->room_number,
                    'floor' => $unit->floor,
                    'status' => $unit->status,
                ];
            }),
            'count' => $result['available_count'],
            'message' => $result['message'],
        ]);
    }

    /**
     * Handle Booking Submission (POST)
     * Supports both cart-based multi-room booking and legacy single-room booking.
     */
    public function storeBooking(Request $request)
    {
        $cartService = new BookingCartService;
        $cart = $cartService->getCartSummary();
        $useCart = ! empty($cart['items']);

        // 1. Validation - Guest details are always required
        $rules = [
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'guest_phone' => ['required', 'string', 'max:20', new ValidPhoneNumber],
            'guest_gender' => 'required|in:male,female,other',
            'guest_address' => 'required|string|max:500',
            'guest_nationality' => 'required|string|max:100',
            'guest_dob' => 'nullable|date',
            'guest_id_type' => 'required|string|max:50',
            'guest_id_number' => [
                'required', 'string', 'max:50',
                function ($attribute, $value, $fail) {
                    $type = request('guest_id_type');
                    if ($type === 'NIN' && ! preg_match('/^\d{11}$/', $value)) {
                        $fail('NIN must be exactly 11 digits (e.g., 12345678901).');
                    }
                    if ($type === 'International Passport' && ! preg_match('/^[A-Za-z]\d{7,9}$/', $value)) {
                        $fail('International Passport number must start with a letter followed by 7-9 digits (e.g., A01234567).');
                    }
                    if ($type === 'Drivers License' && ! preg_match('/^[A-Za-z]{3}\d{12}[A-Za-z]$/', $value)) {
                        $fail('Driver\'s License must be 3 letters + 12 digits + 1 letter (e.g., ABC123456789012X).');
                    }
                    if ($type === 'Voters Card' && ! preg_match('/^\d{19}$/', $value)) {
                        $fail('Voter\'s Card number must be 19 digits.');
                    }
                },
            ],
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'payment_method' => 'required|in:paystack,pay_on_arrival',
        ];

        // Legacy single-room validation (when not using cart)
        if (! $useCart) {
            $rules['room_type_id'] = 'required|exists:room_types,id';
            $rules['room_unit_id'] = 'nullable|exists:room_units,id';
            $rules['check_in_date'] = 'required|date|after_or_equal:today';
            $rules['check_out_date'] = 'required|date|after:check_in_date';
        }

        if (! Auth::check() && $request->has('create_account')) {
            $rules['password'] = 'required|string|min:8';
            $rules['guest_email'] = ['required', 'email', 'unique:users,email', new ValidEmail];
            $rules['website'] = 'nullable|string|max:0';
            $rules['register_time'] = 'required|integer';
        }

        $validated = $request->validate($rules);

        if ($request->has('create_account')) {
            $registerTime = (int) $request->input('register_time');
            if ($registerTime > 0 && time() - $registerTime < 3) {
                return back()->withErrors(['register_time' => 'Please wait a moment before submitting.'])->withInput();
            }
        }

        // 2. Validate Availability using unified RoomAvailabilityService
        $availabilityService = app(RoomAvailabilityService::class);

        if ($useCart) {
            // Cart-based: Check each room type in cart
            foreach ($cart['items'] as $item) {
                $result = $availabilityService->checkRoomTypeAvailability(
                    $item['room_type_id'],
                    $cart['check_in'],
                    $cart['check_out'],
                    $item['quantity']
                );

                if (! $result['available']) {
                    return back()->with('error', $item['room_type_name'].': '.$result['message'])->withInput();
                }
            }
        } else {
            // Legacy: Check single room availability with comprehensive checks
            $result = $availabilityService->checkRoomTypeAvailability(
                $validated['room_type_id'],
                $validated['check_in_date'],
                $validated['check_out_date']
            );

            if (! $result['available']) {
                return back()->with('error', $result['message'])->withInput();
            }

            // If specific unit selected, verify it's in the available list
            $selectedUnitId = $request->filled('room_unit_id') ? $validated['room_unit_id'] : null;
            if ($selectedUnitId && ! $result['units']->contains('id', $selectedUnitId)) {
                return back()->with('error', 'The selected room is no longer available. Please choose another.')->withInput();
            }
        }

        try {
            $result = DB::transaction(function () use ($validated, $request, $cart, $useCart, $cartService) {

                // ====================================================
                // SMART GUEST HANDLING
                // ====================================================
                $userId = Auth::id();

                // Handle "Create Account" Request
                if (! $userId && $request->has('create_account')) {
                    $newUser = User::create([
                        'name' => $validated['guest_name'],
                        'email' => $validated['guest_email'],
                        'password' => Hash::make($request->password),
                        'type' => 'guest',
                    ]);
                    $newUser->assignRole('guest');
                    $userId = $newUser->id;
                    Auth::login($newUser);
                }

                // Find or Create the Guest Profile
                $guest = Guest::where('email', $validated['guest_email'])
                    ->orWhere('contact_number', $validated['guest_phone'])
                    ->first();

                if ($guest) {
                    $guest->update([
                        'full_name' => $validated['guest_name'],
                        'gender' => $validated['guest_gender'],
                        'home_address' => $validated['guest_address'],
                        'nationality' => $validated['guest_nationality'],
                        'birthday' => $validated['guest_dob'] ?? null,
                        'identification_type' => $validated['guest_id_type'],
                        'identification_number' => $validated['guest_id_number'],
                        'user_id' => $userId ?? $guest->user_id,
                    ]);
                } else {
                    $guest = Guest::create([
                        'user_id' => $userId,
                        'full_name' => $validated['guest_name'],
                        'email' => $validated['guest_email'],
                        'contact_number' => $validated['guest_phone'],
                        'gender' => $validated['guest_gender'],
                        'home_address' => $validated['guest_address'],
                        'nationality' => $validated['guest_nationality'],
                        'birthday' => $validated['guest_dob'] ?? null,
                        'identification_type' => $validated['guest_id_type'],
                        'identification_number' => $validated['guest_id_number'],
                    ]);
                }

                // ====================================================
                // CREATE BOOKING(S)
                // ====================================================
                $bookings = [];
                $bookingGroupId = null;
                $totalAmount = 0;

                if ($useCart) {
                    // CART-BASED BOOKING: Create bookings from cart
                    // Calculate total rooms across all cart items
                    $totalRoomsInCart = array_sum(array_column($cart['items'], 'quantity'));

                    // Only generate group ID if booking more than 1 room
                    if ($totalRoomsInCart > 1) {
                        $bookingGroupId = 'GRP'.date('y').strtoupper(Str::random(6));
                    }

                    foreach ($cart['items'] as $item) {
                        // Create one booking per room quantity
                        for ($i = 0; $i < $item['quantity']; $i++) {
                            do {
                                $reference = 'BK'.date('y').strtoupper(Str::random(4));
                            } while (Booking::where('booking_reference', $reference)->exists());

                            $booking = Booking::create([
                                'booking_reference' => $reference,
                                'booking_group_id' => $bookingGroupId, // null for single room
                                'user_id' => $userId,
                                'guest_profile_id' => $guest->id,
                                'room_type_id' => $item['room_type_id'],
                                'room_unit_id' => null, // Assigned at check-in
                                'source' => 'website',
                                'guest_name' => $validated['guest_name'],
                                'guest_email' => $validated['guest_email'],
                                'guest_phone' => $validated['guest_phone'],
                                'check_in_date' => $cart['check_in'],
                                'check_out_date' => $cart['check_out'],
                                'adults' => $validated['adults'],
                                'children' => $validated['children'] ?? 0,
                                'total_amount' => $item['price_per_night'] * $item['nights'],
                                'payment_status' => 'pending',
                                'status' => 'pending',
                                'payment_method' => $validated['payment_method'],
                                'special_requests' => $validated['special_requests'] ?? null,
                            ]);

                            $bookings[] = $booking;
                            $totalAmount += $booking->total_amount;
                        }
                    }

                    // Clear cart after successful booking
                    $cartService->clear();
                } else {
                    // SINGLE-ROOM: Legacy booking flow
                    $roomType = RoomType::findOrFail($validated['room_type_id']);
                    $selectedUnitId = $request->filled('room_unit_id') ? $validated['room_unit_id'] : null;

                    do {
                        $reference = 'BK'.date('y').strtoupper(Str::random(4));
                    } while (Booking::where('booking_reference', $reference)->exists());

                    $days = Carbon::parse($validated['check_in_date'])->diffInDays($validated['check_out_date']) ?: 1;
                    $totalAmount = $roomType->price * $days;

                    $booking = Booking::create([
                        'booking_reference' => $reference,
                        'user_id' => $userId,
                        'guest_profile_id' => $guest->id,
                        'room_type_id' => $roomType->id,
                        'room_unit_id' => $selectedUnitId,
                        'source' => 'website',
                        'guest_name' => $validated['guest_name'],
                        'guest_email' => $validated['guest_email'],
                        'guest_phone' => $validated['guest_phone'],
                        'check_in_date' => $validated['check_in_date'],
                        'check_out_date' => $validated['check_out_date'],
                        'adults' => $validated['adults'],
                        'children' => $validated['children'] ?? 0,
                        'total_amount' => $totalAmount,
                        'payment_status' => 'pending',
                        'status' => 'pending',
                        'payment_method' => $validated['payment_method'],
                        'special_requests' => $validated['special_requests'] ?? null,
                    ]);

                    $bookings[] = $booking;
                }

                return [
                    'bookings' => $bookings,
                    'group_id' => $bookingGroupId,
                    'total_amount' => $totalAmount,
                    'primary_booking' => $bookings[0], // First booking for payment/email
                ];
            });

            $primaryBooking = $result['primary_booking'];

            // Payment or Confirmation
            if ($validated['payment_method'] === 'paystack') {
                // For multi-room, we'll charge the total and update all bookings
                if ($result['group_id']) {
                    session()->put('booking_group_id', $result['group_id']);
                }

                return $this->initializePaystackGrouped($result['bookings'], $result['total_amount']);
            }

            // Send confirmation emails
            foreach ($result['bookings'] as $booking) {
                $this->sendConfirmationEmail($booking);
            }

            // Store reference or group ID for confirmation page
            if ($result['group_id']) {
                session()->put('just_booked_group', $result['group_id']);
                session()->put('just_booked_ref', $primaryBooking->booking_reference);
            } else {
                session()->put('just_booked_ref', $primaryBooking->booking_reference);
            }

            return redirect()->route('website.booking.confirmation', $primaryBooking->booking_reference)
                ->with('success', 'Booking Reserved! Please pay upon arrival.');
        } catch (\Exception $e) {
            Log::error($e);

            return back()->with('error', 'Error: '.$e->getMessage())->withInput();
        }
    }

    /**
     * Initialize Paystack for grouped bookings (multi-room)
     */
    private function initializePaystackGrouped(array $bookings, float $totalAmount)
    {
        $primaryBooking = $bookings[0];

        try {
            // Generate a unique reference for the group payment
            $paymentRef = $primaryBooking->booking_group_id ?? $primaryBooking->booking_reference;

            $result = app(PaymentGatewayManager::class)->driver()->initialize(
                $primaryBooking->guest_email,
                $totalAmount,
                $paymentRef,
                route('website.payment.callback'),
                [
                    'booking_ids' => array_map(fn ($b) => $b->id, $bookings),
                    'booking_group_id' => $primaryBooking->booking_group_id,
                    'custom_fields' => [
                        ['display_name' => 'Guest Name', 'variable_name' => 'guest_name', 'value' => $primaryBooking->guest_name],
                        ['display_name' => 'Rooms', 'variable_name' => 'rooms_count', 'value' => count($bookings)],
                        ['display_name' => 'Primary Ref', 'variable_name' => 'primary_ref', 'value' => $primaryBooking->booking_reference],
                    ],
                ]
            );

            if ($result['status']) {
                return redirect($result['data']['authorization_url']);
            } else {
                return back()->with('error', 'Payment initialization failed: '.($result['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            Log::error('Paystack Init Error: '.$e->getMessage());

            return back()->with('error', 'Could not connect to payment gateway.');
        }
    }

    public function confirmation($ref)
    {
        $booking = Booking::with('roomType')->where('booking_reference', $ref)->firstOrFail();

        // Security Check
        $canView = false;

        if (session('just_booked_ref') === $ref) {
            $canView = true;
        } elseif ($booking->booking_group_id && session('just_booked_group') === $booking->booking_group_id) {
            $canView = true;
        } elseif (Auth::check() && $booking->user_id === Auth::id()) {
            $canView = true;
        }

        if (! $canView) {
            if (Auth::check()) {
                abort(403, 'Access denied.');
            }

            return redirect()->route('website.home')->with('error', 'You are not authorized to view this booking.');
        }

        // Get all bookings in the group (if this is a multi-room booking)
        $groupedBookings = collect([$booking]);
        if ($booking->booking_group_id) {
            $groupedBookings = Booking::with('roomType')
                ->where('booking_group_id', $booking->booking_group_id)
                ->get();
        }

        $totalAmount = $groupedBookings->sum('total_amount');
        $isGroupBooking = $groupedBookings->count() > 1;

        return view('website::booking-confirmation', compact('booking', 'groupedBookings', 'totalAmount', 'isGroupBooking'));
    }

    public function amenities()
    {
        $amenities = Amenity::all();
        $settings = Settings::pluck('value', 'key')->toArray();

        $meta_description = 'Discover the world-class amenities at Brickspoint Boutique Aparthotel in Asokoro, Abuja. From free Wi-Fi and fitness centre to restaurant, room service, and airport shuttle — everything you need for a perfect stay.';
        $meta_keywords = 'amenities Asokoro Abuja, hotel amenities Abuja, apart-hotel services, free Wi-Fi hotel Abuja, fitness centre Abuja, Brickspoint amenities';
        $og_title = 'Amenities — Brickspoint Boutique Aparthotel Asokoro, Abuja';

        return view('website::amenities', compact('amenities', 'settings', 'meta_description', 'meta_keywords', 'og_title'));
    }

    public function location()
    {
        $settings = $this->getSettings();

        $meta_description = 'Visit Brickspoint Boutique Aparthotel at 24 Jose Marti Crescent, Asokoro, Abuja — the best boutique hotel in Nigeria\'s capital. Find directions, map, and information about our prime location in the heart of Abuja.';
        $meta_keywords = 'Brickspoint location Asokoro Abuja, apart-hotel Abuja address, Asokoro hotel, map Abuja hotel, Abuja Nigeria hotel location, 24 Jose Marti Crescent';
        $og_title = 'Our Location — Brickspoint Boutique Aparthotel Asokoro, Abuja';

        return view('website::location', compact('settings', 'meta_description', 'meta_keywords', 'og_title'));
    }

    public function contact()
    {
        $settings = $this->getSettings();

        $meta_description = 'Get in touch with Brickspoint Boutique Aparthotel in Asokoro, Abuja — the best boutique hotel in Nigeria\'s capital. Contact us for reservations at +234 809 999 9627, enquiries, or special requests.';
        $meta_keywords = 'contact Brickspoint Asokoro, Abuja hotel contact, apart-hotel enquiries, book hotel Abuja, Brickspoint address Asokoro, 24 Jose Marti Crescent';
        $og_title = 'Contact Us — Brickspoint Boutique Aparthotel Asokoro, Abuja';

        return view('website::contact', compact('settings', 'meta_description', 'meta_keywords', 'og_title'));
    }

    public function sendMessage(Request $request)
    {
        $ip = $request->ip();
        $userAgent = $request->userAgent();

        // ==========================================
        // 1. HONEYPOT CHECKS (Multiple Hidden Fields)
        // ==========================================

        // Primary honeypot - if filled, it's a bot
        if ($request->filled('website_url')) {
            $this->logSpamAttempt($ip, 'honeypot_website_url', $request->all());

            return $this->fakeSuccessResponse();
        }

        // Secondary honeypot - "phone_number" field that should be empty
        if ($request->filled('phone_number')) {
            $this->logSpamAttempt($ip, 'honeypot_phone', $request->all());

            return $this->fakeSuccessResponse();
        }

        // ==========================================
        // 2. TIME-BASED VALIDATION
        // ==========================================

        // Check if form was submitted too quickly (less than 3 seconds)
        $formLoadedAt = $request->input('_form_token');
        if ($formLoadedAt) {
            try {
                $loadTime = decrypt($formLoadedAt);
                $timeTaken = time() - $loadTime;

                // If submitted in less than 3 seconds, likely a bot
                if ($timeTaken < 3) {
                    $this->logSpamAttempt($ip, 'too_fast_submission', ['time_taken' => $timeTaken]);

                    return $this->fakeSuccessResponse();
                }

                // If form token is older than 30 minutes, reject (stale form)
                if ($timeTaken > 1800) {
                    return redirect()->route('website.contact')
                        ->with('error', 'Your session has expired. Please try again.');
                }
            } catch (\Exception $e) {
                // Invalid token - could be manipulation attempt
                $this->logSpamAttempt($ip, 'invalid_form_token', []);

                return $this->fakeSuccessResponse();
            }
        }

        // ==========================================
        // 3. RATE LIMITING (Stricter)
        // ==========================================

        $cacheKey = 'contact_form_'.md5($ip);
        $submissions = cache($cacheKey, 0);

        // Max 3 submissions per hour
        if ($submissions >= 3) {
            $this->logSpamAttempt($ip, 'rate_limit_exceeded', ['submissions' => $submissions]);

            return redirect()->route('website.contact')
                ->with('error', 'Too many submissions. Please try again in an hour.');
        }

        // Also check daily limit (max 10 per day)
        $dailyCacheKey = 'contact_form_daily_'.md5($ip);
        $dailySubmissions = cache($dailyCacheKey, 0);

        if ($dailySubmissions >= 10) {
            $this->logSpamAttempt($ip, 'daily_limit_exceeded', ['daily_submissions' => $dailySubmissions]);

            return redirect()->route('website.contact')
                ->with('error', 'Daily submission limit reached. Please try again tomorrow.');
        }

        // ==========================================
        // 4. GOOGLE reCAPTCHA v3 VALIDATION
        // ==========================================

        $recaptchaToken = $request->input('g-recaptcha-response');
        if ($recaptchaToken && config('services.recaptcha.secret')) {
            $recaptchaValid = $this->verifyRecaptcha($recaptchaToken, $ip);
            if (! $recaptchaValid) {
                $this->logSpamAttempt($ip, 'recaptcha_failed', []);

                return redirect()->route('website.contact')
                    ->with('error', 'Security verification failed. Please try again.');
            }
        }

        // ==========================================
        // 5. FORM VALIDATION
        // ==========================================

        $validated = $request->validate([
            'name' => 'required|string|max:255|regex:/^[\pL\s\-\']+$/u',
            'email' => 'required|email:rfc,dns|max:255',
            'subject' => 'nullable|string|max:255',
            'message' => 'required|string|min:10|max:2000',
        ], [
            'name.regex' => 'Please enter a valid name.',
            'email.email' => 'Please enter a valid email address.',
            'message.min' => 'Your message must be at least 10 characters.',
        ]);

        // ==========================================
        // 6. SUSPICIOUS PATTERN DETECTION
        // ==========================================

        $spamCheck = $this->detectSpamPatterns($validated['name'], $validated['email'], $validated['message']);
        if ($spamCheck['is_spam']) {
            $this->logSpamAttempt($ip, 'spam_pattern_detected', [
                'reason' => $spamCheck['reason'],
                'data' => $validated,
            ]);

            return $this->fakeSuccessResponse();
        }

        // ==========================================
        // 7. SANITIZE & SAVE
        // ==========================================

        // Sanitize message content
        $validated['message'] = strip_tags($validated['message']);
        $validated['name'] = strip_tags($validated['name']);

        ContactMessage::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'subject' => $validated['subject'] ?? null,
            'message' => $validated['message'],
            'status' => 'unread',
        ]);

        // Increment rate limit counters
        cache([$cacheKey => $submissions + 1], now()->addHour());
        cache([$dailyCacheKey => $dailySubmissions + 1], now()->addDay());

        // Log successful submission
        Log::info('Contact form submission', [
            'ip' => $ip,
            'email' => $validated['email'],
            'name' => $validated['name'],
        ]);

        // Send contact email to admin
        try {
            $adminEmail = config('mail.from.address', 'info@brickspoint.com');
            Mail::to($adminEmail)->send(new ContactMessageReceived($validated));
        } catch (\Exception $e) {
            Log::error('Contact Email Failed: '.$e->getMessage());
        }

        return redirect()->route('website.contact')->with('success', 'Your message has been sent!');
    }

    /**
     * Verify Google reCAPTCHA v3 token
     */
    protected function verifyRecaptcha(string $token, string $ip): bool
    {
        try {
            $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => config('services.recaptcha.secret'),
                'response' => $token,
                'remoteip' => $ip,
            ]);

            $result = $response->json();

            // Check if successful and score is acceptable (0.5 or higher)
            if (($result['success'] ?? false) && ($result['score'] ?? 0) >= 0.5) {
                return true;
            }

            Log::warning('reCAPTCHA verification failed', [
                'ip' => $ip,
                'score' => $result['score'] ?? 'N/A',
                'error_codes' => $result['error-codes'] ?? [],
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('reCAPTCHA verification error: '.$e->getMessage());

            // If reCAPTCHA service fails, allow submission but log it
            return true;
        }
    }

    /**
     * Detect spam patterns in form data
     */
    protected function detectSpamPatterns(string $name, string $email, string $message): array
    {
        // 1. Check for too many URLs in message
        $urlCount = preg_match_all('/https?:\/\/|www\./i', $message);
        if ($urlCount > 2) {
            return ['is_spam' => true, 'reason' => 'too_many_urls'];
        }

        // 2. Check for suspicious keywords (common spam terms)
        $spamKeywords = [
            'viagra',
            'cialis',
            'casino',
            'lottery',
            'winner',
            'prize',
            'bitcoin',
            'cryptocurrency',
            'investment opportunity',
            'make money fast',
            'click here',
            'act now',
            'limited time',
            'free offer',
            'nigerian prince',
            'wire transfer',
            'western union',
            'sex',
            'porn',
            'xxx',
            'nude',
        ];

        $lowerMessage = strtolower($message.' '.$name);
        foreach ($spamKeywords as $keyword) {
            if (str_contains($lowerMessage, $keyword)) {
                return ['is_spam' => true, 'reason' => 'spam_keyword: '.$keyword];
            }
        }

        // 3. Check for repeated characters (e.g., "aaaaaa" or "!!!!!!!")
        if (preg_match('/(.)\1{5,}/', $message)) {
            return ['is_spam' => true, 'reason' => 'repeated_characters'];
        }

        // 4. Check for all caps message (shouting)
        $upperCount = preg_match_all('/[A-Z]/', $message);
        $letterCount = preg_match_all('/[a-zA-Z]/', $message);
        if ($letterCount > 20 && ($upperCount / $letterCount) > 0.7) {
            return ['is_spam' => true, 'reason' => 'excessive_caps'];
        }

        // 5. Check for suspicious email patterns
        $disposableDomains = [
            'tempmail.com',
            'throwaway.email',
            'guerrillamail.com',
            'mailinator.com',
            '10minutemail.com',
            'fakeinbox.com',
            'trashmail.com',
            'maildrop.cc',
            'dispostable.com',
        ];

        $emailDomain = strtolower(substr(strrchr($email, '@'), 1));
        if (in_array($emailDomain, $disposableDomains)) {
            return ['is_spam' => true, 'reason' => 'disposable_email'];
        }

        // 6. Check for Cyrillic or other non-Latin scripts in name (unless expected)
        if (preg_match('/[\x{0400}-\x{04FF}]/u', $name)) {
            return ['is_spam' => true, 'reason' => 'cyrillic_characters'];
        }

        // 7. Check for HTML tags in message
        if ($message !== strip_tags($message)) {
            return ['is_spam' => true, 'reason' => 'html_in_message'];
        }

        return ['is_spam' => false, 'reason' => null];
    }

    /**
     * Log spam attempt for monitoring
     */
    protected function logSpamAttempt(string $ip, string $reason, array $data): void
    {
        Log::channel('daily')->warning('Spam attempt blocked', [
            'ip' => $ip,
            'reason' => $reason,
            'data' => $data,
            'user_agent' => request()->userAgent(),
            'timestamp' => now()->toIso8601String(),
        ]);

        // Increment spam counter for this IP (for potential IP blocking)
        $spamCacheKey = 'spam_attempts_'.md5($ip);
        $spamAttempts = cache($spamCacheKey, 0);
        cache([$spamCacheKey => $spamAttempts + 1], now()->addDay());
    }

    /**
     * Return fake success response to not alert bots
     */
    protected function fakeSuccessResponse()
    {
        // Add a small random delay to mimic real processing
        usleep(rand(100000, 500000)); // 100-500ms

        return redirect()->route('website.contact')->with('success', 'Your message has been sent!');
    }

    public function about()
    {
        $settings = $this->getSettings();

        $meta_description = 'Learn about Brickspoint Boutique Aparthotel — the best boutique hotel in Asokoro, Abuja. Discover our story, our commitment to excellence, and why we are the premier choice for luxury short and long stays in Nigeria\'s capital.';
        $meta_keywords = 'about Brickspoint Abuja, boutique hotel Asokoro story, Abuja apart-hotel, luxury hotel Abuja, Brickspoint history';
        $og_title = 'About Us — Brickspoint Boutique Aparthotel Asokoro, Abuja';

        return view('website::about', compact('settings', 'meta_description', 'meta_keywords', 'og_title'));
    }

    public function testimonials(Request $request)
    {
        $settings = $this->getSettings();

        $type = $request->get('type', 'stay');
        if (! in_array($type, Testimonial::TYPES)) {
            $type = 'stay';
        }

        $reviews = Testimonial::approved()->where('type', $type)->latest()->get();
        $typeLabel = ucfirst($type);

        $stayCount = Testimonial::approved()->stay()->count();
        $restaurantCount = Testimonial::approved()->restaurant()->count();
        $eventCount = Testimonial::approved()->event()->count();
        $totalCount = $stayCount + $restaurantCount + $eventCount;

        $meta_description = "Read genuine $typeLabel reviews from guests at Brickspoint Boutique Aparthotel in Asokoro, Abuja. See why we are rated as the best boutique hotel in Nigeria's capital.";
        $meta_keywords = "Brickspoint reviews, Asokoro hotel reviews, $typeLabel reviews Abuja, boutique hotel Abuja reviews, guest testimonials Abuja";
        $og_title = "$typeLabel Reviews — Brickspoint Boutique Aparthotel Asokoro, Abuja";

        return view('website::testimonials', compact('settings', 'reviews', 'type', 'typeLabel', 'stayCount', 'restaurantCount', 'eventCount', 'totalCount', 'meta_description', 'meta_keywords', 'og_title'));
    }

    public function storeTestimonial(Request $request)
    {
        if ($request->filled('website')) {
            return redirect()->route('website.testimonials')
                ->with('success', 'Thank you for your feedback! Your review has been submitted and will appear after review.');
        }

        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'text' => 'required|string|max:2000',
            'rating' => 'required|integer|min:1|max:5',
            'type' => 'required|in:'.implode(',', Testimonial::TYPES),
            'stay_type' => 'nullable|string|max:255',
            'dining_venue' => 'nullable|string|max:255',
            'event_name' => 'nullable|string|max:255',
        ]);

        $testimonial = Testimonial::create([
            'guest_name' => $validated['guest_name'],
            'email' => $validated['email'] ?? null,
            'text' => $validated['text'],
            'rating' => $validated['rating'],
            'type' => $validated['type'],
            'stay_type' => $validated['stay_type'] ?? null,
            'dining_venue' => $validated['dining_venue'] ?? null,
            'event_name' => $validated['event_name'] ?? null,
            'approved' => false,
        ]);

        if ($testimonial->email) {
            try {
                Mail::to($testimonial->email)->send(new ReviewSubmitted($testimonial));
            } catch (\Exception $e) {
                Log::error('Review Confirmation Email Failed: '.$e->getMessage());
            }
        }

        return redirect()->route('website.testimonials')
            ->with('success', 'Thank you for your feedback! Your review has been submitted and will appear after review.');
    }

    public function blog()
    {
        $posts = [
            ['title' => 'Summer Deals', 'excerpt' => 'Check out our latest offers...', 'date' => '2025-03-29'],
            ['title' => 'Local Events', 'excerpt' => 'What’s happening nearby...', 'date' => '2025-03-25'],
        ];

        return view('website::blog', compact('posts'));
    }

    /**
     * Display the Dining & Menu page.
     */
    public function dining()
    {
        $settings = Settings::pluck('value', 'key')->toArray();

        $diningOptions = Dining::all();

        $restaurantReviews = Testimonial::approved()->restaurant()->latest()->get();

        $meta_description = 'Explore exquisite dining at Brickspoint Boutique Aparthotel in Asokoro, Abuja. Enjoy world-class cuisine at our on-site restaurant, bar, and dining venues — the best dining experience in Abuja.';
        $meta_keywords = 'dining Asokoro Abuja, restaurant Abuja, Brickspoint restaurant, fine dining Abuja, best restaurant Abuja, apart-hotel dining Abuja';
        $og_title = 'Dining — Brickspoint Boutique Aparthotel Asokoro, Abuja';

        return view('website::dining', compact('settings', 'diningOptions', 'restaurantReviews', 'meta_description', 'meta_keywords', 'og_title'));
    }

    public function diningMenu(Dining $dining)
    {
        $settings = Settings::pluck('value', 'key')->toArray();

        $meta_description = 'View the menu for '.$dining->name.' at Brickspoint Boutique Aparthotel in Asokoro, Abuja. Explore our carefully curated dishes and culinary offerings.';
        $meta_keywords = $dining->name.' menu, dining Asokoro Abuja, restaurant menu Abuja, Brickspoint dining';
        $og_title = $dining->name.' Menu — Brickspoint Boutique Aparthotel Asokoro';

        return view('website::menu', compact('settings', 'dining', 'meta_description', 'meta_keywords', 'og_title'));
    }

    public function offers()
    {
        $page = OffersPage::firstOrCreate(
            ['id' => 1],
            [
                'hero_title' => 'Exclusive Offers',
                'hero_subtitle' => 'Brickspoint ApartHotel',
                'intro_heading' => 'Special Packages & Deals',
                'intro_description' => 'Discover our latest offers and experience great savings on your stay.',
            ]
        );

        $page->load('offers');

        $settings = Settings::pluck('value', 'key')->toArray();

        $meta_description = 'Discover exclusive offers and special packages at Brickspoint Boutique Aparthotel in Asokoro, Abuja. Save on your next luxury stay at the best boutique hotel in Nigeria\'s capital.';
        $meta_keywords = 'hotel deals Abuja, apart-hotel offers, Brickspoint promotions, Abuja hotel packages, Asokoro hotel deals, luxury stay Abuja';
        $og_title = 'Offers & Deals — Brickspoint Boutique Aparthotel Asokoro, Abuja';

        return view('website::offers', compact('page', 'settings', 'meta_description', 'meta_keywords', 'og_title'));
    }

    public function facilities()
    {
        $page = FacilitiesPage::firstOrCreate(
            ['id' => 1],
            [
                'hero_title' => 'Our Facilities',
                'hero_subtitle' => 'Experience Luxury & Comfort',
                'intro_heading' => 'Amenities & Services',
                'intro_description' => 'Discover a wide range of facilities designed to make your stay unforgettable.',
            ]
        );

        $page->load('items');

        $settings = Settings::pluck('value', 'key')->toArray();

        $meta_description = 'Explore the premium facilities at Brickspoint Boutique Aparthotel in Asokoro, Abuja — state-of-the-art gym, exquisite restaurant, versatile meeting rooms, and world-class amenities. The best boutique hotel experience in Nigeria\'s capital.';
        $meta_keywords = 'hotel facilities Asokoro Abuja, apart-hotel amenities, Brickspoint gym, meeting rooms Abuja, Abuja hotel services, best hotel facilities Abuja, boutique hotel amenities';
        $og_title = 'Facilities — Brickspoint Boutique Aparthotel Asokoro, Abuja';

        return view('website::facilities', compact('page', 'settings', 'meta_description', 'meta_keywords', 'og_title'));
    }

    /**
     * Smart Availability Check
     * Uses unified RoomAvailabilityService for comprehensive checking:
     * - Website Bookings, Frontdesk Registrations
     * - Inventory Blocks (Stop Sell, Maintenance)
     * - Stay Restrictions (Min/Max Stay, CTA, CTD)
     */
    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        $availabilityService = app(RoomAvailabilityService::class);
        $roomType = RoomType::with('units')->findOrFail($validated['room_type_id']);
        $totalUnits = $roomType->units->count();

        // Check availability using unified service
        $result = $availabilityService->checkRoomTypeAvailability(
            $validated['room_type_id'],
            $validated['check_in_date'],
            $validated['check_out_date']
        );

        // Available - proceed to booking
        if ($result['available']) {
            $availableCount = $result['available_count'];

            if ($request->wantsJson()) {
                return response()->json([
                    'available' => true,
                    'message' => "{$availableCount} of {$totalUnits} rooms available!",
                    'available_count' => $availableCount,
                    'redirect_url' => route('website.book', [
                        'room_type_id' => $validated['room_type_id'],
                        'check_in' => $validated['check_in_date'],
                        'check_out' => $validated['check_out_date'],
                    ]),
                ]);
            }

            return redirect()->route('website.book', [
                'room_type_id' => $validated['room_type_id'],
                'check_in' => $validated['check_in_date'],
                'check_out' => $validated['check_out_date'],
            ])->with('success', 'Room type available! Please complete your booking.');
        }

        // Not available - return detailed message
        $message = $result['message'];
        $suggestion = null;

        // Only suggest alternative dates if the reason is insufficient inventory
        if (($result['reason'] ?? null) === 'insufficient_inventory') {
            // Find next available date
            $checkIn = Carbon::parse($validated['check_in_date']);
            $checkOut = Carbon::parse($validated['check_out_date']);
            $earliestAvailable = null;

            foreach ($roomType->units as $unit) {
                $latestBooking = Booking::where('room_unit_id', $unit->id)
                    ->whereNotIn('status', ['cancelled', 'no_show'])
                    ->where('check_in_date', '<', $checkOut)
                    ->where('check_out_date', '>', $checkIn)
                    ->orderBy('check_out_date')
                    ->first();

                if ($latestBooking) {
                    $unitFreeDate = Carbon::parse($latestBooking->check_out_date);
                    if (! $earliestAvailable || $unitFreeDate->lt($earliestAvailable)) {
                        $earliestAvailable = $unitFreeDate;
                    }
                }
            }

            if ($earliestAvailable) {
                $message .= ' Next available from '.$earliestAvailable->format('M j, Y').'.';
                $suggestion = [
                    'check_in' => $earliestAvailable->format('Y-m-d'),
                    'check_out' => $earliestAvailable->copy()->addDay()->format('Y-m-d'),
                ];
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'available' => false,
                'message' => $message,
                'reason' => $result['reason'] ?? 'unavailable',
                'suggestion' => $suggestion,
            ]);
        }

        return back()->withInput()->withErrors(['check_in_date' => $message]);
    }

    /**
     * Settings
     */
    protected function getSettings()
    {
        return Settings::pluck('value', 'key')->toArray();
    }

    /**
     * Resend Booking Confirmation Email
     * Allows guests to resend to the same email OR fix a typo.
     */
    public function resendConfirmation(Request $request)
    {
        $validated = $request->validate([
            'booking_reference' => 'required|string|exists:bookings,booking_reference',
            'email' => 'nullable|email|max:255', // Optional: Only if they want to change it
        ]);

        $booking = Booking::where('booking_reference', $validated['booking_reference'])->firstOrFail();

        // 🛑 Security Check: Ensure the user is authorized to modify this booking
        // 1. Is it the session from the booking they JUST made?
        // 2. OR is the logged-in user the owner?
        $isAuthorized = session('just_booked_ref') === $booking->booking_reference
            || (Auth::check() && $booking->user_id === Auth::id());

        if (! $isAuthorized) {
            abort(403, 'Unauthorized action.');
        }

        // 🔄 Update Email if provided (Fixing a typo)
        if ($request->filled('email') && $request->email !== $booking->guest_email) {
            $booking->update(['guest_email' => $request->email]);

            // If the user has a profile linked, we might want to update that too?
            // For now, let's just update the booking contact info.
        }

        // 📧 Resend Email
        try {
            Mail::to($booking->guest_email)->send(new BookingConfirmation($booking));

            return back()->with('success', 'Confirmation email sent to '.$booking->guest_email);
        } catch (\Exception $e) {
            Log::error('Resend Email Failed: '.$e->getMessage());

            return back()->with('error', 'Could not send email. Please contact support.');
        }
    }

    /**
     * ✅ Initialize Paystack Transaction
     */
    private function initializePaystack(Booking $booking)
    {
        try {
            $result = app(PaymentGatewayManager::class)->driver()->initialize(
                $booking->guest_email,
                $booking->total_amount,
                $booking->booking_reference, // Use our Ref as Paystack Ref
                route('website.payment.callback'),
                [
                    'booking_id' => $booking->id,
                    'custom_fields' => [
                        ['display_name' => 'Guest Name', 'variable_name' => 'guest_name', 'value' => $booking->guest_name],
                        ['display_name' => 'Booking Ref', 'variable_name' => 'booking_ref', 'value' => $booking->booking_reference],
                    ],
                ]
            );

            if ($result['status']) {
                // Redirect user to Paystack Payment Page
                return redirect($result['data']['authorization_url']);
            } else {
                return back()->with('error', 'Payment initialization failed: '.($result['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            Log::error('Paystack Init Error: '.$e->getMessage());

            return back()->with('error', 'Could not connect to payment gateway.');
        }
    }

    /**
     * ✅ Verify Paystack Transaction (Callback)
     * Supports both single booking and grouped multi-room bookings.
     */
    public function verifyPayment(Request $request)
    {
        $reference = $request->query('reference'); // Paystack returns this

        if (! $reference) {
            return redirect()->route('website.home')->with('error', 'No payment reference provided.');
        }

        try {
            // Verify with the active payment gateway
            $result = app(PaymentGatewayManager::class)->driver()->verify($reference);

            if ($result['status'] && $result['data']['status'] === 'success') {
                // Check if this is a group payment (reference starts with GRP)
                $isGroupPayment = str_starts_with($reference, 'GRP');

                if ($isGroupPayment) {
                    // Update all bookings in the group
                    $bookings = Booking::where('booking_group_id', $reference)->get();

                    if ($bookings->isEmpty()) {
                        return redirect()->route('website.booking')->with('error', 'Booking not found.');
                    }

                    foreach ($bookings as $booking) {
                        $booking->update([
                            'payment_status' => 'paid',
                            'amount_paid' => $booking->total_amount,
                            'status' => 'confirmed',
                        ]);
                        $this->sendConfirmationEmail($booking);
                    }

                    $primaryBooking = $bookings->first();
                    session()->put('just_booked_ref', $primaryBooking->booking_reference);
                    session()->put('just_booked_group', $reference);

                    return redirect()->route('website.booking.confirmation', $primaryBooking->booking_reference)
                        ->with('success', 'Payment successful! All '.$bookings->count().' rooms are confirmed.');
                } else {
                    // Single booking payment
                    $booking = Booking::where('booking_reference', $reference)->first();

                    if ($booking) {
                        $booking->update([
                            'payment_status' => 'paid',
                            'amount_paid' => $booking->total_amount,
                            'status' => 'confirmed',
                        ]);

                        $this->sendConfirmationEmail($booking);
                        session()->put('just_booked_ref', $booking->booking_reference);

                        return redirect()->route('website.booking.confirmation', $booking->booking_reference)
                            ->with('success', 'Payment successful! Your booking is confirmed.');
                    }
                }
            }

            return redirect()->route('website.booking')->with('error', 'Payment verification failed. Please try again.');
        } catch (\Exception $e) {
            Log::error('Paystack Verify Error: '.$e->getMessage());

            return redirect()->route('website.booking')->with('error', 'Payment verification error.');
        }
    }

    /**
     * ✅ Paystack Webhook Handler
     * Handles asynchronous payment notifications (bank transfers, delayed card payments, etc.)
     * Set your webhook URL in Paystack dashboard to: https://yourdomain.com/paystack/webhook
     */
    /**
     * ✅ Paystack Webhook Handler (legacy route alias)
     * Dispatches to the active Paystack gateway driver.
     */
    public function paystackWebhook(Request $request)
    {
        return $this->handleGatewayWebhook($request, 'paystack');
    }

    /**
     * ✅ Generic Payment Gateway Webhook Dispatcher
     * Route: POST /webhooks/payment/{gateway}
     * Verifies the signature, then delegates event handling to the gateway driver.
     */
    public function paymentWebhook(Request $request, string $gateway = 'paystack')
    {
        return $this->handleGatewayWebhook($request, $gateway);
    }

    private function handleGatewayWebhook(Request $request, string $gateway)
    {
        $driver = app(PaymentGatewayManager::class)->driver($gateway);

        $signature = $request->header($driver->webhookSignatureHeader());
        $payload = $request->getContent();

        if (! $driver->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Payment webhook: Invalid signature', ['gateway' => $gateway]);

            return response()->json(['status' => 'error', 'message' => 'Invalid signature'], 401);
        }

        $event = json_decode($payload, true) ?? [];

        Log::info('Payment webhook received', [
            'gateway' => $gateway,
            'event' => $event['event'] ?? null,
        ]);

        $driver->handleWebhook($event);

        // Always return 200 OK to acknowledge receipt
        return response()->json(['status' => 'success'], 200);
    }

    /**
     * Helper to send email (Keep code DRY)
     * Sends confirmation to guest and a copy to reservations team.
     */
    private function sendConfirmationEmail(Booking $booking)
    {
        try {
            // Send to guest
            Mail::to($booking->guest_email)->send(new BookingConfirmation($booking));

            // Send copy to reservations team if configured
            $reservationsEmail = config('mail.reservations_email');
            if ($reservationsEmail) {
                Mail::to($reservationsEmail)->send(new BookingConfirmation($booking, true)); // true = staff copy
            }
        } catch (\Exception $e) {
            Log::error('Email Failed: '.$e->getMessage());
        }
    }

    /**
     * Show the "Find My Booking" form
     */
    public function bookingLogin()
    {
        return view('website::booking-login');
    }

    /**
     * Authenticate Guest via Reference & Email
     * Supports both individual booking references (BK...) and group references (GRP...)
     */
    public function findBooking(Request $request)
    {
        $request->validate([
            'booking_reference' => 'required|string',
            'email' => 'required|email',
        ]);

        $reference = strtoupper(trim($request->booking_reference));
        $email = $request->email;

        // Check if searching by group reference (GRP...)
        if (str_starts_with($reference, 'GRP')) {
            // Find any booking in the group with matching email
            $booking = Booking::where('booking_group_id', $reference)
                ->where('guest_email', $email)
                ->first();

            if (! $booking) {
                return back()->with('error', 'No booking found with this group reference. Please check your details.');
            }

            // Grant access to the entire group
            session()->put('just_booked_ref', $booking->booking_reference);
            session()->put('just_booked_group', $reference);

            return redirect()->route('website.booking.confirmation', $booking->booking_reference)
                ->with('success', 'Group booking found! Showing all '.
                    Booking::where('booking_group_id', $reference)->count().' rooms.');
        }

        // Standard individual booking reference (BK...)
        $booking = Booking::where('booking_reference', $reference)
            ->where('guest_email', $email)
            ->first();

        if (! $booking) {
            return back()->with('error', 'No booking found with these details. Please check your reference code.');
        }

        // ✅ SECURITY: Grant temporary access via session
        session()->put('just_booked_ref', $booking->booking_reference);

        // If this booking belongs to a group, also grant group access
        if ($booking->booking_group_id) {
            session()->put('just_booked_group', $booking->booking_group_id);
        }

        return redirect()->route('website.booking.confirmation', $booking->booking_reference)
            ->with('success', 'Booking found!');
    }

    // =========================================================================
    // MULTI-ROOM BOOKING CART METHODS
    // =========================================================================

    /**
     * Step 1: Room Selection Page
     * Uses unified RoomAvailabilityService for comprehensive availability checking.
     */
    public function bookStep1(Request $request)
    {
        $cartService = new BookingCartService;
        $availabilityService = app(RoomAvailabilityService::class);

        // Get dates from request or cart
        $cartDates = $cartService->getDates();
        $checkIn = $request->check_in ?? $cartDates['check_in'] ?? date('Y-m-d');
        $checkOut = $request->check_out ?? $cartDates['check_out'] ?? date('Y-m-d', strtotime('+1 day'));
        $adults = $request->adults ?? 1;
        $children = $request->children ?? 0;

        // If a specific room_type_id is passed (from room-details availability check), auto-add to cart
        if ($request->filled('room_type_id')) {
            $roomType = RoomType::find($request->room_type_id);
            if ($roomType) {
                $result = $cartService->add($roomType->id, 1, $checkIn, $checkOut);
                if (! $result['success']) {
                    return redirect()->route('website.book.step1')
                        ->with('error', $result['message']);
                }
            }
        }

        // Get all active room types with comprehensive availability info
        $roomTypes = RoomType::where('is_active', true)
            ->with(['amenities', 'units'])
            ->withCount('units')
            ->ordered()
            ->get()
            ->map(function ($roomType) use ($checkIn, $checkOut, $availabilityService) {
                $availability = $availabilityService->checkRoomTypeAvailability($roomType->id, $checkIn, $checkOut);
                $roomType->available_count = $availability['available_count'] ?? 0;
                $roomType->is_available = $availability['available'];
                $roomType->availability_message = $availability['message'] ?? null;
                $roomType->availability_reason = $availability['reason'] ?? null;

                return $roomType;
            });

        $cart = $cartService->getCartSummary();

        return view('website::book', compact('roomTypes', 'checkIn', 'checkOut', 'adults', 'children', 'cart'));
    }

    /**
     * API: Get room availability for dates
     * Uses unified RoomAvailabilityService for comprehensive checking.
     */
    public function getRoomAvailability(Request $request)
    {
        $validated = $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
        ]);

        $availabilityService = app(RoomAvailabilityService::class);

        $roomTypes = RoomType::where('is_active', true)
            ->with('amenities')
            ->withCount('units')
            ->ordered()
            ->get()
            ->map(function ($roomType) use ($validated, $availabilityService) {
                $availability = $availabilityService->checkRoomTypeAvailability(
                    $roomType->id,
                    $validated['check_in'],
                    $validated['check_out']
                );

                return [
                    'id' => $roomType->id,
                    'name' => $roomType->name,
                    'slug' => $roomType->slug,
                    'price' => (float) $roomType->price,
                    'capacity' => $roomType->capacity,
                    'image_url' => $roomType->image_url,
                    'bed_type' => $roomType->bed_type,
                    'description' => $roomType->description,
                    'total_units' => $roomType->units_count,
                    'available_count' => $availability['available_count'] ?? 0,
                    'is_available' => $availability['available'],
                    'availability_message' => $availability['message'] ?? null,
                    'availability_reason' => $availability['reason'] ?? null,
                    'amenities' => $roomType->amenities->map(fn ($a) => [
                        'name' => $a->name,
                        'icon' => $a->icon,
                    ]),
                ];
            });

        return response()->json([
            'success' => true,
            'room_types' => $roomTypes,
            'check_in' => $validated['check_in'],
            'check_out' => $validated['check_out'],
        ]);
    }

    /**
     * Cart: Add room to cart
     */
    public function cartAdd(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'quantity' => 'required|integer|min:1|max:10',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
        ]);

        $cartService = new BookingCartService;
        $result = $cartService->add(
            $validated['room_type_id'],
            $validated['quantity'],
            $validated['check_in'],
            $validated['check_out']
        );

        return response()->json($result);
    }

    /**
     * Cart: Update quantity
     */
    public function cartUpdate(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'quantity' => 'required|integer|min:0|max:10',
        ]);

        $cartService = new BookingCartService;
        $result = $cartService->update($validated['room_type_id'], $validated['quantity']);

        return response()->json($result);
    }

    /**
     * Cart: Remove room type from cart
     */
    public function cartRemove($roomTypeId)
    {
        $cartService = new BookingCartService;
        $result = $cartService->remove((int) $roomTypeId);

        return response()->json($result);
    }

    /**
     * Cart: Clear all items
     */
    public function cartClear()
    {
        $cartService = new BookingCartService;
        $result = $cartService->clear();

        return response()->json($result);
    }

    /**
     * Cart: Get cart contents
     */
    public function cartGet()
    {
        $cartService = new BookingCartService;

        return response()->json([
            'success' => true,
            'cart' => $cartService->getCartSummary(),
        ]);
    }

    /**
     * Newsletter: Subscribe email
     */
    public function subscribeNewsletter(Request $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'required|email:rfc,dns|max:255',
        ], [
            'email.email' => 'Please enter a valid email address.',
            'email.required' => 'Please enter your email address.',
        ]);

        // Check if already subscribed
        $existing = NewsletterSubscriber::where('email', $validated['email'])->first();

        if ($existing) {
            if ($existing->is_active) {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already subscribed to our newsletter.',
                ], 200);
            }

            // Reactivate subscription
            $existing->update([
                'name' => $validated['name'] ?? $existing->name,
                'is_active' => true,
                'subscribed_at' => now(),
                'unsubscribed_at' => null,
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Welcome back! Your subscription has been reactivated.',
            ]);
        }

        // Create new subscriber
        NewsletterSubscriber::create([
            'name' => $validated['name'] ?? null,
            'email' => $validated['email'],
            'is_active' => true,
            'subscribed_at' => now(),
        ]);

        $greeting = ! empty($validated['name']) ? "Thank you, {$validated['name']}!" : 'Thank you for subscribing to our newsletter!';

        return response()->json([
            'success' => true,
            'message' => $greeting,
        ]);
    }

    public function meetings()
    {
        /** @var MeetingPage|null $page */
        $page = MeetingPage::with(['rooms', 'gallery'])->first();

        if (! $page) {
            $page = MeetingPage::create([
                'hero_title' => 'Meetings & Events Space',
                'hero_subtitle' => 'Brickspoint ApartHotel',
                'hero_description' => 'Discover our versatile meeting and event spaces, equipped with modern facilities and dedicated service.',
                'is_published' => true,
            ]);
        }

        $settings = Settings::pluck('value', 'key')->toArray();

        $meta_description = 'Host your meetings and events at Brickspoint Boutique Aparthotel in Asokoro, Abuja. Versatile event spaces, modern facilities, and dedicated service for conferences, weddings, and private events in Nigeria\'s capital.';
        $meta_keywords = 'meeting rooms Asokoro Abuja, event venue Abuja, conference facilities Abuja, Brickspoint meetings, wedding venue Abuja, corporate events Abuja';
        $og_title = 'Meetings & Events — Brickspoint Boutique Aparthotel Asokoro, Abuja';

        return view('website::meetings', compact('page', 'settings', 'meta_description', 'meta_keywords', 'og_title'));
    }

    public function meetingEnquiry()
    {
        $settings = Settings::pluck('value', 'key')->toArray();

        return view('website::meeting-enquiry', compact('settings'));
    }

    public function storeEnquiry(Request $request)
    {
        $ip = $request->ip();

        // ==========================================
        // 1. HONEYPOT / TIMING CHECKS
        // ==========================================

        // Honeypot fields must be empty
        if (! empty($request->input('website_url')) || ! empty($request->input('phone_number'))) {
            $this->logSpamAttempt($ip, 'honeypot_triggered', []);

            return $this->fakeSuccessResponse();
        }

        // Validate encrypted form token
        if ($token = $request->input('_form_token')) {
            try {
                $timeTaken = now()->diffInSeconds(now()->subSeconds(decrypt($token)) ?? now());

                // Submissions faster than 3 seconds are likely bots
                if ($timeTaken < 3) {
                    $this->logSpamAttempt($ip, 'too_fast_submission', ['time_taken' => $timeTaken]);

                    return $this->fakeSuccessResponse();
                }

                // If form token is older than 30 minutes, reject (stale form)
                if ($timeTaken > 1800) {
                    return redirect()->route('website.meeting-enquiry')
                        ->with('error', 'Your session has expired. Please try again.');
                }
            } catch (\Exception $e) {
                $this->logSpamAttempt($ip, 'invalid_form_token', []);

                return $this->fakeSuccessResponse();
            }
        }

        // ==========================================
        // 2. RATE LIMITING
        // ==========================================

        $cacheKey = 'enquiry_form_'.md5($ip);
        $submissions = cache($cacheKey, 0);

        if ($submissions >= 3) {
            $this->logSpamAttempt($ip, 'rate_limit_exceeded', ['submissions' => $submissions]);

            return redirect()->route('website.meeting-enquiry')
                ->with('error', 'Too many submissions. Please try again in an hour.');
        }

        $dailyCacheKey = 'enquiry_form_daily_'.md5($ip);
        $dailySubmissions = cache($dailyCacheKey, 0);

        if ($dailySubmissions >= 10) {
            $this->logSpamAttempt($ip, 'daily_limit_exceeded', ['daily_submissions' => $dailySubmissions]);

            return redirect()->route('website.meeting-enquiry')
                ->with('error', 'Daily submission limit reached. Please try again tomorrow.');
        }

        // ==========================================
        // 3. GOOGLE reCAPTCHA v3 VALIDATION
        // ==========================================

        $recaptchaToken = $request->input('g-recaptcha-response');
        if ($recaptchaToken && config('services.recaptcha.secret')) {
            $recaptchaValid = $this->verifyRecaptcha($recaptchaToken, $ip);
            if (! $recaptchaValid) {
                $this->logSpamAttempt($ip, 'recaptcha_failed', []);

                return redirect()->route('website.meeting-enquiry')
                    ->with('error', 'Security verification failed. Please try again.');
            }
        }

        // ==========================================
        // 4. FORM VALIDATION
        // ==========================================

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => ['required', 'string', 'max:20', new ValidPhoneNumber],
            'company' => 'nullable|string|max:255',
            'event_type' => 'required|string|in:Meeting,Conference,Wedding,Banquet,Party,Other',
            'event_date' => 'required|date|after_or_equal:today',
            'guest_count' => 'required|integer|min:1|max:9999',
            'start_time' => 'nullable|date_format:H:i',
            'end_time' => 'nullable|date_format:H:i|after:start_time',
            'setup_style' => 'nullable|string|max:255',
            'catering_option' => 'required|string|in:Full Catering,Corkage',
            'accommodation_required' => 'nullable|boolean',
            'rooms_required' => 'nullable|integer|min:1|max:100',
            'arrival_date' => 'nullable|date|after_or_equal:today',
            'departure_date' => 'nullable|date|after:arrival_date',
            'parking_required' => 'nullable|boolean',
            'site_inspection_required' => 'nullable|boolean',
            'hear_about_us' => 'nullable|string|max:255',
            'special_requirements' => 'nullable|string|max:2000',
            'venue_interest' => 'nullable|string|max:255',
        ]);

        $validated['accommodation_required'] = $request->boolean('accommodation_required');
        $validated['parking_required'] = $request->boolean('parking_required');
        $validated['site_inspection_required'] = $request->boolean('site_inspection_required');

        // ==========================================
        // 5. SAVE & NOTIFY
        // ==========================================

        $enquiry = BanquetEnquiry::create($validated);

        cache([$cacheKey => $submissions + 1], now()->addHour());
        cache([$dailyCacheKey => $dailySubmissions + 1], now()->addDay());

        $managers = User::role(RoleEnum::ADMIN->value)
            ->orWhere(function ($q) {
                $q->where('type', 'staff')
                    ->whereHas('permissions', fn ($p) => $p->where('name', 'banquet.update'));
            })
            ->get();

        if ($managers->isNotEmpty()) {
            Notification::send($managers, new NewEnquiryNotification($enquiry));
        }

        return redirect()->route('website.meeting-enquiry')
            ->with('success', 'Thank you! Your enquiry has been submitted successfully. Our team will contact you shortly.');
    }

    // =========================================================================
    // EVENT LEAD CAPTURE (Public Form — Dynamic by Event Slug)
    // =========================================================================

    public function eventLead($slug)
    {
        $event = LeadEvent::where('slug', $slug)->where('is_active', true)->firstOrFail();
        $settings = Settings::pluck('value', 'key')->toArray();

        return view('website::event-lead', compact('event', 'settings'));
    }

    public function storeEventLead(Request $request, $slug)
    {
        $event = LeadEvent::where('slug', $slug)->where('is_active', true)->firstOrFail();

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone' => ['required', 'string', 'max:20', new ValidPhoneNumber],
            'company' => 'nullable|string|max:255',
        ]);

        $existing = EventLead::where('event_id', $event->id)
            ->where('email', $validated['email'])
            ->first();

        if ($existing) {
            return redirect()->route('website.event-lead', $slug)
                ->with('info', 'You have already registered your interest for this event. We will be in touch!');
        }

        $lead = EventLead::create([
            'event_id' => $event->id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'company' => $validated['company'] ?? null,
            'source' => 'Website Form',
            'status' => 'New',
        ]);

        if ($event->confirmation_email_body) {
            Mail::to($lead->email)->send(new EventLeadConfirmation($lead, $event));
        }

        return redirect()->route('website.event-lead', $slug)
            ->with('success', $event->getThankYouMessageOrDefault());
    }

    public function sitemap()
    {
        $pages = [
            ['loc' => route('website.home'), 'priority' => '1.0', 'changefreq' => 'weekly'],
            ['loc' => route('website.rooms.index'), 'priority' => '0.9', 'changefreq' => 'weekly'],
            ['loc' => route('website.about'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => route('website.contact'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => route('website.location'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => route('website.dining'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => route('website.amenities'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => route('website.facilities'), 'priority' => '0.8', 'changefreq' => 'monthly'],
            ['loc' => route('website.offers'), 'priority' => '0.8', 'changefreq' => 'weekly'],
            ['loc' => route('website.meetings'), 'priority' => '0.7', 'changefreq' => 'monthly'],
            ['loc' => route('website.testimonials'), 'priority' => '0.6', 'changefreq' => 'monthly'],
        ];

        $roomTypes = RoomType::where('is_active', true)->get();
        foreach ($roomTypes as $room) {
            $pages[] = [
                'loc' => route('website.rooms.show', $room->slug ?? $room->id),
                'priority' => '0.7',
                'changefreq' => 'weekly',
            ];
        }

        $diningItems = Dining::all();
        foreach ($diningItems as $item) {
            $pages[] = [
                'loc' => route('website.dining.menu', $item),
                'priority' => '0.6',
                'changefreq' => 'monthly',
            ];
        }

        return response()
            ->view('website::sitemap', compact('pages'))
            ->header('Content-Type', 'application/xml');
    }
}
