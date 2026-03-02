<?php

namespace Modules\Website\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Website\Models\Room;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\Testimonial;
use Modules\Website\Models\Dining;
use Modules\Website\Models\Booking;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Website\Models\ContactMessage;
use Illuminate\Support\Facades\Auth;
// use Modules\Website\Models\GuestProfile;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Modules\Website\Models\Settings;
use Modules\Website\Models\Amenity;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Modules\Website\Http\Requests\StoreBookingRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail; // ✅ Import Mail Facade
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Website\Emails\BookingConfirmation; // ✅ Import Booking Mail
use Modules\Website\Emails\ContactMessageReceived; // ✅ Import Contact Mail
use Modules\Website\Services\BookingCartService;

class WebsiteController extends Controller
{
    public function index()
    {
        // 1. Settings can remain an array (accessed by key)
        $settings = \Modules\Website\Models\Settings::pluck('value', 'key')->toArray();

        // 2. Featured Room Types (NEW architecture)
        $featuredRooms = RoomType::where('is_featured', true)
            ->where('is_active', true)
            ->withCount('units')
            ->with('amenities')
            ->ordered()
            ->get();

        $testimonials = Testimonial::where('approved', true)
            ->latest()
            ->get();

        $dining = Dining::all();

        return view('website::index', compact('settings', 'featuredRooms', 'testimonials', 'dining'));
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
                $sub->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
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

        return view('website::rooms', compact('roomTypes', 'checkIn', 'checkOut'));
    }
    /**
     * Show details for a specific room type.
     */
    public function roomDetails($slug)
    {
        // 1. Fetch the room type by Slug or ID
        $roomType = is_numeric($slug)
            ? RoomType::with(['amenities', 'images', 'units'])->findOrFail($slug)
            : RoomType::with(['amenities', 'images', 'units'])->where('slug', $slug)->firstOrFail();

        // 2. Fetch Related Room Types
        $relatedRooms = RoomType::where('id', '!=', $roomType->id)
            ->where('is_active', true)
            ->with('amenities')
            ->inRandomOrder()
            ->take(3)
            ->get();

        return view('website::room-details', compact('roomType', 'relatedRooms'));
    }
    
    /**
     * Show the Booking Form (GET) - Step 2: Guest Details
     * Supports both cart-based multi-room booking and legacy single-room booking.
     * Redirects to /book if no rooms are selected.
     */
    public function booking(Request $request)
    {
        $cartService = new BookingCartService();
        $cart = $cartService->getCartSummary();

        // If cart has items, use cart-based booking flow
        if (!empty($cart['items'])) {
            // Validate cart availability before showing form
            $unavailable = $cartService->validateAvailability();
            if (!empty($unavailable)) {
                return redirect()->route('website.book')
                    ->with('error', 'Some rooms in your cart are no longer available. Please review your selection.');
            }

            return view('website::booking', [
                'cart' => $cart,
                'roomTypes' => collect(),
                'selectedRoomType' => null,
                'useCart' => true,
            ]);
        }

        // Check if room_type_id is provided (legacy direct booking from room details)
        $roomTypeId = old('room_type_id', $request->room_type_id ?? $request->room_id);
        
        // If no cart and no room selected, redirect to room selection page
        if (!$roomTypeId) {
            return redirect()->route('website.book')
                ->with('info', 'Please select your rooms first.');
        }

        // Legacy: Single room booking flow (direct link from room details page)
        $roomTypes = RoomType::where('is_active', true)
            ->withCount('units')
            ->ordered()
            ->get();

        $selectedRoomType = RoomType::find($roomTypeId);

        return view('website::booking', [
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
        $exists = \App\Models\User::where('email', $request->email)->exists();

        return response()->json(['exists' => $exists]);
    }

    /**
     * API: Get available units for a room type and dates.
     */
    public function getAvailableUnits(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        $roomType = RoomType::with('units')->findOrFail($validated['room_type_id']);
        $availableUnits = $roomType->getAvailableUnitsForDates(
            $validated['check_in_date'],
            $validated['check_out_date']
        );

        return response()->json([
            'units' => $availableUnits->map(function ($unit) {
                return [
                    'id' => $unit->id,
                    'room_number' => $unit->room_number,
                    'floor' => $unit->floor,
                    'status' => $unit->status,
                ];
            }),
            'count' => $availableUnits->count(),
        ]);
    }

    /**
     * Handle Booking Submission (POST)
     * Supports both cart-based multi-room booking and legacy single-room booking.
     */
    public function storeBooking(Request $request)
    {
        $cartService = new BookingCartService();
        $cart = $cartService->getCartSummary();
        $useCart = !empty($cart['items']);

        // 1. Validation - Guest details are always required
        $rules = [
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'guest_phone' => 'required|string|max:20',
            'guest_gender' => 'required|in:male,female,other',
            'guest_address' => 'required|string|max:500',
            'guest_nationality' => 'required|string|max:100',
            'guest_dob' => 'nullable|date',
            'guest_id_type' => 'required|string|max:50',
            'guest_id_number' => 'required|string|max:50',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'payment_method' => 'required|in:paystack,pay_on_arrival',
        ];

        // Legacy single-room validation (when not using cart)
        if (!$useCart) {
            $rules['room_type_id'] = 'required|exists:room_types,id';
            $rules['room_unit_id'] = 'nullable|exists:room_units,id';
            $rules['check_in_date'] = 'required|date|after_or_equal:today';
            $rules['check_out_date'] = 'required|date|after:check_in_date';
        }

        if (!Auth::check() && $request->has('create_account')) {
            $rules['password'] = 'required|string|min:8';
            $rules['guest_email'] = 'required|email|unique:users,email';
        }

        $validated = $request->validate($rules);

        // 2. Validate Cart Availability (if using cart)
        if ($useCart) {
            $unavailable = $cartService->validateAvailability();
            if (!empty($unavailable)) {
                $message = 'Some rooms are no longer available: ';
                $message .= implode(', ', array_map(fn($u) => $u['name'] . ' - ' . $u['message'], $unavailable));
                return back()->with('error', $message)->withInput();
            }
        } else {
            // Legacy: Check single room availability
            $roomType = RoomType::with('units')->findOrFail($validated['room_type_id']);
            $availableUnits = $roomType->getAvailableUnitsForDates(
                $validated['check_in_date'],
                $validated['check_out_date']
            );

            if ($availableUnits->isEmpty()) {
                return back()->with('error', 'Sorry, no rooms of this type are available for these dates.')->withInput();
            }

            $selectedUnitId = $request->filled('room_unit_id') ? $validated['room_unit_id'] : null;
            if ($selectedUnitId && !$availableUnits->contains('id', $selectedUnitId)) {
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
                if (!$userId && $request->has('create_account')) {
                    $newUser = User::create([
                        'name' => $validated['guest_name'],
                        'email' => $validated['guest_email'],
                        'password' => Hash::make($request->password),
                    ]);
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
                        'birthday' => $validated['guest_dob'],
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
                        'birthday' => $validated['guest_dob'],
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
                        $bookingGroupId = 'GRP' . date('y') . strtoupper(Str::random(6));
                    }

                    foreach ($cart['items'] as $item) {
                        // Create one booking per room quantity
                        for ($i = 0; $i < $item['quantity']; $i++) {
                            do {
                                $reference = 'BK' . date('y') . strtoupper(Str::random(4));
                            } while (Booking::where('booking_reference', $reference)->exists());

                            $booking = Booking::create([
                                'booking_reference' => $reference,
                                'booking_group_id' => $bookingGroupId, // null for single room
                                'user_id' => $userId,
                                'guest_profile_id' => $guest->id,
                                'room_type_id' => $item['room_type_id'],
                                'room_unit_id' => null, // Assigned at check-in
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
                        $reference = 'BK' . date('y') . strtoupper(Str::random(4));
                    } while (Booking::where('booking_reference', $reference)->exists());

                    $days = Carbon::parse($validated['check_in_date'])->diffInDays($validated['check_out_date']) ?: 1;
                    $totalAmount = $roomType->price * $days;

                    $booking = Booking::create([
                        'booking_reference' => $reference,
                        'user_id' => $userId,
                        'guest_profile_id' => $guest->id,
                        'room_type_id' => $roomType->id,
                        'room_unit_id' => $selectedUnitId,
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
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Initialize Paystack for grouped bookings (multi-room)
     */
    private function initializePaystackGrouped(array $bookings, float $totalAmount)
    {
        $primaryBooking = $bookings[0];
        $url = "https://api.paystack.co/transaction/initialize";
        $secretKey = config('services.paystack.secret');

        if (!$secretKey) {
            return back()->with('error', 'Payment configuration missing.');
        }

        try {
            // Generate a unique reference for the group payment
            $paymentRef = $primaryBooking->booking_group_id ?? $primaryBooking->booking_reference;

            $response = \Illuminate\Support\Facades\Http::withOptions([
                'verify' => false,
            ])->withHeaders([
                'Authorization' => 'Bearer ' . $secretKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'email' => $primaryBooking->guest_email,
                'amount' => $totalAmount * 100, // Amount in Kobo
                'reference' => $paymentRef,
                'callback_url' => route('website.payment.callback'),
                'metadata' => [
                    'booking_ids' => array_map(fn($b) => $b->id, $bookings),
                    'booking_group_id' => $primaryBooking->booking_group_id,
                    'custom_fields' => [
                        ['display_name' => "Guest Name", 'variable_name' => "guest_name", 'value' => $primaryBooking->guest_name],
                        ['display_name' => "Rooms", 'variable_name' => "rooms_count", 'value' => count($bookings)],
                        ['display_name' => "Primary Ref", 'variable_name' => "primary_ref", 'value' => $primaryBooking->booking_reference]
                    ]
                ]
            ]);

            $result = $response->json();

            if ($result['status']) {
                return redirect($result['data']['authorization_url']);
            } else {
                return back()->with('error', 'Payment initialization failed: ' . ($result['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            Log::error("Paystack Init Error: " . $e->getMessage());
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
        } elseif (session('just_booked_group') === $booking->booking_group_id) {
            $canView = true;
        } elseif (Auth::check() && $booking->user_id === Auth::id()) {
            $canView = true;
        }

        if (!$canView) {
            abort(403, 'Access denied. Please login to view your booking.');
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
        return view('website::amenities', compact('amenities', 'settings'));
    }

    public function location()
    {
        $settings = $this->getSettings();
        return view('website::location', compact('settings'));
    }

    public function contact()
    {
        $settings = $this->getSettings();
        return view('website::contact', compact('settings'));
    }

    public function sendMessage(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string|max:1000',
        ]);

        ContactMessage::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'message' => $validated['message'],
            'status' => 'unread',
        ]);

        // ✅ SEND CONTACT EMAIL TO ADMIN
        try {
            // Replace with your actual admin email or fetch from settings
            $adminEmail = config('mail.from.address', 'info@brickspoint.com');

            Mail::to($adminEmail)
                ->send(new ContactMessageReceived($validated));
        } catch (\Exception $e) {
            Log::error("Contact Email Failed: " . $e->getMessage());
        }

        return redirect()->route('website.contact')->with('success', 'Your message has been sent!');
    }

    public function about()
    {
        $settings = $this->getSettings();
        return view('website::about', compact('settings'));
    }

    public function testimonials()
    {
        $testimonials = [
            ['name' => 'John Doe', 'text' => 'Amazing stay, great service!', 'rating' => 5],
            ['name' => 'Jane Smith', 'text' => 'Loved the pool and food.', 'rating' => 4],
        ];
        return view('website::testimonials', compact('testimonials'));
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
        // 1. Fetch Global Settings (Logo, Phone, etc.)
        $settings = Settings::pluck('value', 'key')->toArray();

        // 2. Fetch Dining Options
        $diningOptions = Dining::all();

        return view('website::dining', compact('settings', 'diningOptions'));
    }
    /**
     * Smart Availability Check
     * Checks Website Bookings AND Frontdesk Registrations at RoomType level.
     */
    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);

        // Get the room type with units
        $roomType = RoomType::with('units')->findOrFail($validated['room_type_id']);
        
        // Check availability at room type level
        $availableUnits = $roomType->getAvailableUnitsForDates($checkIn, $checkOut);
        $availableCount = $availableUnits->count();
        $totalUnits = $roomType->units->count();

        // Scenario A: At least one unit available
        if ($availableCount > 0) {
            if ($request->wantsJson()) {
                return response()->json([
                    'available' => true,
                    'message' => "{$availableCount} of {$totalUnits} rooms available!",
                    'available_count' => $availableCount,
                    'redirect_url' => route('website.book', [
                        'room_type_id' => $validated['room_type_id'],
                        'check_in' => $validated['check_in_date'],
                        'check_out' => $validated['check_out_date'],
                    ])
                ]);
            }
            return redirect()->route('website.book', [
                'room_type_id' => $validated['room_type_id'],
                'check_in' => $validated['check_in_date'],
                'check_out' => $validated['check_out_date'],
            ])->with('success', 'Room type available! Please complete your booking.');
        }

        // Scenario B: All units occupied - find next available date
        $message = "All {$totalUnits} rooms of this type are booked for your selected dates.";

        // Find the earliest checkout date for booked units
        $earliestAvailable = null;
        foreach ($roomType->units as $unit) {
            // Check bookings
            $latestBooking = Booking::where('room_unit_id', $unit->id)
                ->orWhere(function($q) use ($unit, $checkIn, $checkOut) {
                    $q->where('room_type_id', $unit->room_type_id)
                      ->whereNull('room_unit_id')
                      ->where('check_in_date', '<', $checkOut)
                      ->where('check_out_date', '>', $checkIn);
                })
                ->where('status', '!=', 'cancelled')
                ->where('check_in_date', '<', $checkOut)
                ->where('check_out_date', '>', $checkIn)
                ->orderBy('check_out_date')
                ->first();

            if ($latestBooking) {
                $unitFreeDate = Carbon::parse($latestBooking->check_out_date);
                if (!$earliestAvailable || $unitFreeDate->lt($earliestAvailable)) {
                    $earliestAvailable = $unitFreeDate;
                }
            }
        }

        $suggestion = null;
        if ($earliestAvailable) {
            $message .= " Next available from " . $earliestAvailable->format('M j, Y') . ".";
            $suggestion = [
                'check_in' => $earliestAvailable->format('Y-m-d'),
                'check_out' => $earliestAvailable->copy()->addDay()->format('Y-m-d')
            ];
        }

        if ($request->wantsJson()) {
            return response()->json([
                'available' => false,
                'message' => $message,
                'suggestion' => $suggestion
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

        if (!$isAuthorized) {
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
            return back()->with('success', 'Confirmation email sent to ' . $booking->guest_email);
        } catch (\Exception $e) {
            Log::error("Resend Email Failed: " . $e->getMessage());
            return back()->with('error', 'Could not send email. Please contact support.');
        }
    }
    /**
     * ✅ Initialize Paystack Transaction
     */
    private function initializePaystack(Booking $booking)
    {
        $url = "https://api.paystack.co/transaction/initialize";
        $secretKey = config('services.paystack.secret');
        if (!$secretKey) {
            return back()->with('error', 'Payment configuration missing.');
        }

        try {
            $response = \Illuminate\Support\Facades\Http::withOptions([
                'verify' => false, // ⚠️ DISABLES SSL CHECK (For Localhost/Dev Only)
            ])->withHeaders([
                'Authorization' => 'Bearer ' . $secretKey,
                'Content-Type' => 'application/json',
            ])->post($url, [
                'email' => $booking->guest_email,
                'amount' => $booking->total_amount * 100, // Amount in Kobo
                'reference' => $booking->booking_reference, // Use our Ref as Paystack Ref
                'callback_url' => route('website.payment.callback'),
                'metadata' => [
                    'booking_id' => $booking->id,
                    'custom_fields' => [
                        ['display_name' => "Guest Name", 'variable_name' => "guest_name", 'value' => $booking->guest_name],
                        ['display_name' => "Booking Ref", 'variable_name' => "booking_ref", 'value' => $booking->booking_reference]
                    ]
                ]
            ]);

            $result = $response->json();

            if ($result['status']) {
                // Redirect user to Paystack Payment Page
                return redirect($result['data']['authorization_url']);
            } else {
                return back()->with('error', 'Payment initialization failed: ' . ($result['message'] ?? 'Unknown error'));
            }
        } catch (\Exception $e) {
            Log::error("Paystack Init Error: " . $e->getMessage());
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
        $secretKey = config('services.paystack.secret');

        if (!$reference) {
            return redirect()->route('website.home')->with('error', 'No payment reference provided.');
        }

        try {
            // Verify with Paystack API
            $response = \Illuminate\Support\Facades\Http::withOptions([
                'verify' => false, // ⚠️ DISABLES SSL CHECK (For Localhost/Dev Only)
            ])->withHeaders([
                'Authorization' => 'Bearer ' . $secretKey,
            ])->get("https://api.paystack.co/transaction/verify/" . $reference);

            $result = $response->json();

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
                        ->with('success', 'Payment successful! All ' . $bookings->count() . ' rooms are confirmed.');

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
            Log::error("Paystack Verify Error: " . $e->getMessage());
            return redirect()->route('website.booking')->with('error', 'Payment verification error.');
        }
    }

    /**
     * Helper to send email (Keep code DRY)
     */
    private function sendConfirmationEmail(Booking $booking)
    {
        try {
            Mail::to($booking->guest_email)->send(new BookingConfirmation($booking));
        } catch (\Exception $e) {
            Log::error("Email Failed: " . $e->getMessage());
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

            if (!$booking) {
                return back()->with('error', 'No booking found with this group reference. Please check your details.');
            }

            // Grant access to the entire group
            session()->put('just_booked_ref', $booking->booking_reference);
            session()->put('just_booked_group', $reference);

            return redirect()->route('website.booking.confirmation', $booking->booking_reference)
                ->with('success', 'Group booking found! Showing all ' . 
                    Booking::where('booking_group_id', $reference)->count() . ' rooms.');
        }

        // Standard individual booking reference (BK...)
        $booking = Booking::where('booking_reference', $reference)
            ->where('guest_email', $email)
            ->first();

        if (!$booking) {
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
     */
    public function bookStep1(Request $request)
    {
        $cartService = new BookingCartService();
        
        // Get dates from request or cart
        $cartDates = $cartService->getDates();
        $checkIn = $request->check_in ?? $cartDates['check_in'] ?? date('Y-m-d');
        $checkOut = $request->check_out ?? $cartDates['check_out'] ?? date('Y-m-d', strtotime('+1 day'));
        $adults = $request->adults ?? 1;
        $children = $request->children ?? 0;

        // If a specific room_type_id is passed (from room-details availability check), auto-add to cart
        if ($request->filled('room_type_id')) {
            $roomType = RoomType::find($request->room_type_id);
            if ($roomType && $roomType->getAvailabilityCountForDates($checkIn, $checkOut) > 0) {
                // Auto-add 1 room of this type to the cart
                $cartService->add($roomType->id, 1, $checkIn, $checkOut);
            }
        }

        // Get all active room types with availability for these dates
        $roomTypes = RoomType::where('is_active', true)
            ->with(['amenities', 'units'])
            ->withCount('units')
            ->ordered()
            ->get()
            ->map(function ($roomType) use ($checkIn, $checkOut) {
                $roomType->available_count = $roomType->getAvailabilityCountForDates($checkIn, $checkOut);
                return $roomType;
            });

        $cart = $cartService->getCartSummary();

        return view('website::book', compact('roomTypes', 'checkIn', 'checkOut', 'adults', 'children', 'cart'));
    }

    /**
     * API: Get room availability for dates
     */
    public function getRoomAvailability(Request $request)
    {
        $validated = $request->validate([
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
        ]);

        $roomTypes = RoomType::where('is_active', true)
            ->with('amenities')
            ->withCount('units')
            ->ordered()
            ->get()
            ->map(function ($roomType) use ($validated) {
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
                    'available_count' => $roomType->getAvailabilityCountForDates(
                        $validated['check_in'],
                        $validated['check_out']
                    ),
                    'amenities' => $roomType->amenities->map(fn($a) => [
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

        $cartService = new BookingCartService();
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

        $cartService = new BookingCartService();
        $result = $cartService->update($validated['room_type_id'], $validated['quantity']);

        return response()->json($result);
    }

    /**
     * Cart: Remove room type from cart
     */
    public function cartRemove($roomTypeId)
    {
        $cartService = new BookingCartService();
        $result = $cartService->remove((int) $roomTypeId);

        return response()->json($result);
    }

    /**
     * Cart: Clear all items
     */
    public function cartClear()
    {
        $cartService = new BookingCartService();
        $result = $cartService->clear();

        return response()->json($result);
    }

    /**
     * Cart: Get cart contents
     */
    public function cartGet()
    {
        $cartService = new BookingCartService();
        
        return response()->json([
            'success' => true,
            'cart' => $cartService->getCartSummary(),
        ]);
    }
}
