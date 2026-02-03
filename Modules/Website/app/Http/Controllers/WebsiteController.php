<?php

namespace Modules\Website\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Website\Models\Room;
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

class WebsiteController extends Controller
{
    public function index()
    {
        // 1. Settings can remain an array (accessed by key)
        $settings = \Modules\Website\Models\Settings::pluck('value', 'key')->toArray();

        // 2. FIX: Ensure these return Collections (REMOVE ->toArray())
        // The view calls ->take(3) on these, so they MUST be Collections.
        $featuredRooms = Room::where('is_featured', true)
            ->where('status', 'available')
            ->latest()
            ->get(); // Returns Collection

        $testimonials = Testimonial::where('approved', true)
            ->latest()
            ->get(); // Returns Collection

        $dining = Dining::all(); // Returns Collection

        return view('website::index', compact('settings', 'featuredRooms', 'testimonials', 'dining'));
    }

    /**
     * Display the rooms page with filtering.
     */
    public function rooms(Request $request)
    {
        // 1. Base Query
        $query = Room::where('status', 'available');

        // ... (Search, Price, and Guest filters remain the same) ...

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

        // =========================================================
        // 6. AVAILABILITY CHECK (THE FIX)
        // =========================================================
        if ($request->filled(['check_in', 'check_out'])) {
            $checkIn = Carbon::parse($request->check_in);
            $checkOut = Carbon::parse($request->check_out);

            // A. Exclude Rooms with Conflicting WEBSITE Bookings
            $query->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
                $q->where('status', '!=', 'cancelled')
                    ->where(function ($sub) use ($checkIn, $checkOut) {
                        $sub->where('check_in_date', '<', $checkOut)
                            ->where('check_out_date', '>', $checkIn);
                    });
            });

            // B. Exclude Rooms with Conflicting FRONTDESK Registrations
            // We must check 'registrations' relationship for Walk-ins/Reserved
            if (class_exists(Registration::class)) {
                $query->whereDoesntHave('registrations', function ($q) use ($checkIn, $checkOut) {
                    $q->whereIn('stay_status', ['checked_in', 'draft_by_guest', 'reserved'])
                        ->where(function ($sub) use ($checkIn, $checkOut) {
                            $sub->where('check_in', '<', $checkOut)
                                ->where('check_out', '>', $checkIn);
                        });
                });
            }
        }

        // ... (Sorting and Pagination remain the same) ...

        // 7. Sorting
        if ($request->filled('sort')) {
            switch ($request->sort) {
                case 'price_asc':
                    $query->orderBy('price', 'asc');
                    break;
                case 'price_desc':
                    $query->orderBy('price', 'desc');
                    break;
                default:
                    $query->latest();
                    break;
            }
        } else {
            $query->latest();
        }

        // 8. Pagination
        $rooms = $query->paginate(9)->withQueryString();

        return view('website::rooms', compact('rooms'));
    }
    /**
     * Show details for a specific room.
     */
    public function roomDetails($slug)
    {
        // 1. Fetch the main room by Slug or ID
        $room = is_numeric($slug)
            ? Room::findOrFail($slug)
            : Room::where('slug', $slug)->firstOrFail();

        // 2. FIX: Fetch Related Rooms
        // Logic: Get other available rooms, exclude current one, take 3 random ones
        $relatedRooms = Room::where('id', '!=', $room->id)
            ->where('status', 'available')
            ->inRandomOrder() // Or ->latest()
            ->take(3)
            ->get();

        return view('website::room-details', compact('room', 'relatedRooms'));
    }
    
    /**
     * Show the Booking Form (GET)
     */
    public function booking(Request $request)
    {
        // 1. Get ALL available rooms for the dropdown list (Restored your logic)
        $rooms = Room::where('status', 'available')->get();

        // 2. Determine the "Selected Room" (if any)
        // We check 'old' (validation error), then 'request' (URL), then null.
        $roomId = old('room_id', $request->room_id);

        $selectedRoom = null;
        if ($roomId) {
            $selectedRoom = Room::find($roomId);
        }

        // Pass both the list ($rooms) and the specific selection ($selectedRoom)
        return view('website::booking', compact('rooms', 'selectedRoom'));
    }

    public function checkEmail(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        // Check if email exists in the Users table
        $exists = \App\Models\User::where('email', $request->email)->exists();

        return response()->json(['exists' => $exists]);
    }
    /**
     * Handle Booking Submission (POST)
     */

    public function storeBooking(Request $request)
    {
        // 1. Validation (Updated with new fields)
        $rules = [
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'guest_phone' => 'required|string|max:20',
            'guest_gender' => 'required|in:male,female,other', // ✅ New
            'guest_address' => 'required|string|max:500',      // ✅ New
            'guest_nationality' => 'required|string|max:100',  // ✅ New
            'guest_dob' => 'nullable|date',                    // ✅ New
            'guest_id_type' => 'required|string|max:50',
            'guest_id_number' => 'required|string|max:50',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'payment_method' => 'required|in:paystack,pay_on_arrival',
        ];

        if (!Auth::check() && $request->has('create_account')) {
            $rules['password'] = 'required|string|min:8';
            $rules['guest_email'] = 'required|email|unique:users,email'; // Only unique for USERS, not guests
        }

        $validated = $request->validate($rules);

        // 2. Check Availability
        $isAvailable = Booking::isAvailable(
            $validated['room_id'],
            $validated['check_in_date'],
            $validated['check_out_date']
        );

        if (!$isAvailable) {
            return back()->with('error', 'Sorry, this room is no longer available for these dates.')->withInput();
        }

        try {
            $booking = DB::transaction(function () use ($validated, $request) {

                // ====================================================
                // 3. SMART GUEST HANDLING
                // ====================================================
                $userId = Auth::id(); // Null if not logged in

                // A. Handle "Create Account" Request
                if (!$userId && $request->has('create_account')) {
                    $newUser = User::create([
                        'name' => $validated['guest_name'],
                        'email' => $validated['guest_email'],
                        'password' => Hash::make($request->password),
                    ]);
                    $userId = $newUser->id;
                    Auth::login($newUser); // Auto-login
                }

                // B. Find or Create the Guest Profile
                // We check by Email OR Phone to find returning guests
                $guest = Guest::where('email', $validated['guest_email'])
                    ->orWhere('contact_number', $validated['guest_phone'])
                    ->first();

                if ($guest) {
                    // Update existing guest with latest info (Address, etc.)
                    $guest->update([
                        'full_name' => $validated['guest_name'],
                        'gender' => $validated['guest_gender'],
                        'home_address' => $validated['guest_address'], // Maps to 'home_address' in DB
                        'nationality' => $validated['guest_nationality'],
                        'birthday' => $validated['guest_dob'],
                        'identification_type' => $validated['guest_id_type'],
                        'identification_number' => $validated['guest_id_number'],
                        'user_id' => $userId ?? $guest->user_id, // Link user account if created
                    ]);
                } else {
                    // Create New Guest Profile
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
                        'identification_number' => $validated['guest_id_number'], // for verification
                    ]);
                }

                // ====================================================
                // 4. Create Booking (Linked to Guest)
                // ====================================================

                // Generate Unique Reference
                do {
                    $reference = 'BK' . date('y') . strtoupper(Str::random(4));
                } while (Booking::where('booking_reference', $reference)->exists());

                $room = Room::findOrFail($validated['room_id']);
                $days = Carbon::parse($validated['check_in_date'])->diffInDays($validated['check_out_date']) ?: 1;
                $totalAmount = $room->price * $days;

                return Booking::create([
                    'booking_reference' => $reference,
                    'user_id' => $userId,
                    'guest_profile_id' => $guest->id, // ✅ Critical: Links to CRM Guest
                    'room_id' => $room->id,
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
            });

            // 5. Payment or Confirmation
            if ($validated['payment_method'] === 'paystack') {
                return $this->initializePaystack($booking);
            }

            $this->sendConfirmationEmail($booking);
            session()->put('just_booked_ref', $booking->booking_reference);

            return redirect()->route('website.booking.confirmation', $booking->booking_reference)
                ->with('success', 'Booking Reserved! Please pay upon arrival.');
        } catch (\Exception $e) {
            Log::error($e);
            return back()->with('error', 'Error: ' . $e->getMessage())->withInput();
        }
    }

    public function confirmation($ref)
    {
        $booking = Booking::with('room')->where('booking_reference', $ref)->firstOrFail();

        // Security Check
        $canView = false;

        if (session('just_booked_ref') === $ref) {
            $canView = true;
        } elseif (Auth::check() && $booking->user_id === Auth::id()) {
            $canView = true;
        }

        if (!$canView) {
            abort(403, 'Access denied. Please login to view your booking.');
            return redirect()->route('website.home')->with('error', 'You are not authorized to view this booking.');
        }

        return view('website::booking-confirmation', compact('booking'));
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
     * Checks Website Bookings AND Frontdesk Registrations.
     */
    public function checkAvailability(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
       

        // 1. Find Conflicts in WEBSITE BOOKINGS
        $bookingConflicts = Booking::where('room_id', $validated['room_id'])
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->where('check_in_date', '<', $checkOut)
                    ->where('check_out_date', '>', $checkIn);
            })
            ->get();

        // 2. Find Conflicts in FRONTDESK REGISTRATIONS (THE FIX)
        $registrationConflicts = collect();
        if (class_exists(Registration::class)) {
            $registrationConflicts = Registration::where('room_id', $validated['room_id'])
                // ✅ FIX: Include 'reserved' so the website knows it's taken
                ->whereIn('stay_status', ['checked_in', 'draft_by_guest', 'reserved'])
                ->where(function ($query) use ($checkIn, $checkOut) {
                    $query->where('check_in', '<', $checkOut)
                        ->where('check_out', '>', $checkIn);
                })
                ->get();
        }

        // 3. Scenario A: Room is fully available (No conflicts in either system)
        if ($bookingConflicts->isEmpty() && $registrationConflicts->isEmpty()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'available' => true,
                    'message' => 'Room is available!',
                    'redirect_url' => route('website.booking', $validated)
                ]);
            }
            return redirect()->route('website.booking', $validated)
                ->with('success', 'Room is available! Please complete your booking.');
        }

        // 4. Scenario B: Room is Occupied - Find the latest end date
        // We need to find out WHEN it becomes free.
        $latestBookingDate = $bookingConflicts->max('check_out_date');
        $latestRegDate = $registrationConflicts->max('check_out'); // 'check_out' column in registrations

        // Compare dates safely
        $occupiedUntil = null;
        if ($latestBookingDate && $latestRegDate) {
            $occupiedUntil = $latestBookingDate > $latestRegDate ? Carbon::parse($latestBookingDate) : Carbon::parse($latestRegDate);
        } elseif ($latestBookingDate) {
            $occupiedUntil = Carbon::parse($latestBookingDate);
        } else {
            $occupiedUntil = Carbon::parse($latestRegDate);
        }

        // Construct Message
        $message = "This room is currently booked until " . $occupiedUntil->format('l, F j') . ".";

        if ($occupiedUntil->lt($checkOut)) {
            $message .= " However, it is available from " . $occupiedUntil->format('M j') . " to " . $occupiedUntil->copy()->addDay()->format('M j') . ". Would you like to adjust your dates?";
        } else {
            $message .= " Please select different dates.";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'available' => false,
                'message' => $message,
                'suggestion' => [
                    'check_in' => $occupiedUntil->format('Y-m-d'),
                    'check_out' => $occupiedUntil->copy()->addDay()->format('Y-m-d')
                ]
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
                // Payment Successful
                $booking = Booking::where('booking_reference', $reference)->first();

                if ($booking) {
                    $booking->update([
                        'payment_status' => 'paid',
                        'amount_paid' => $booking->total_amount,
                        'status' => 'confirmed', // Auto-confirm since paid
                    ]);

                    // Send Email & Set Session
                    $this->sendConfirmationEmail($booking);
                    session()->put('just_booked_ref', $booking->booking_reference);

                    return redirect()->route('website.booking.confirmation', $booking->booking_reference)
                        ->with('success', 'Payment successful! Your booking is confirmed.');
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
     */
    public function findBooking(Request $request)
    {
        $request->validate([
            'booking_reference' => 'required|string',
            'email' => 'required|email',
        ]);

        // Find booking matching BOTH ref and email
        $booking = Booking::where('booking_reference', $request->booking_reference)
            ->where('guest_email', $request->email)
            ->first();

        if (!$booking) {
            return back()->with('error', 'No booking found with these details. Please check your reference code.');
        }

        // ✅ SECURITY: Grant temporary access via session
        // This allows them to pass the check in the confirmation() method
        session()->put('just_booked_ref', $booking->booking_reference);

        return redirect()->route('website.booking.confirmation', $booking->booking_reference)
            ->with('success', 'Booking found!');
    }
}
