<?php

namespace Modules\Website\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Website\Models\Room;
use Modules\Website\Models\Testimonial;
use Modules\Website\Models\Dining;
use Modules\Website\Models\Booking;
use Modules\Website\Models\ContactMessage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Modules\Website\Models\Settings;
use Modules\Website\Models\Amenity;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Modules\Website\Http\Requests\StoreBookingRequest;
use Illuminate\Support\Facades\DB;

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

        // 2. Search (Name/Description)
        $query->when($request->filled('search'), function ($q) use ($request) {
            $q->where(function ($sub) use ($request) {
                $sub->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('description', 'like', '%' . $request->search . '%');
            });
        });

        // 3. Filter by Min Price (Matches your blade 'min_price')
        $query->when($request->filled('min_price'), function ($q) use ($request) {
            $q->where('price', '>=', $request->min_price);
        });

        // 4. Filter by Max Price (Matches your blade 'max_price')
        $query->when($request->filled('max_price'), function ($q) use ($request) {
            $q->where('price', '<=', $request->max_price);
        });

        // 5. Filter by Guests (Matches your blade 'guests')
        // We assume 'guests' maps to the 'capacity' column
        $query->when($request->filled('guests'), function ($q) use ($request) {
            $q->where('capacity', '>=', $request->guests);
        });

        // 6. Availability Check (from Homepage Shortcut)
        if ($request->filled(['check_in', 'check_out'])) {
            $checkIn = Carbon::parse($request->check_in);
            $checkOut = Carbon::parse($request->check_out);

            $query->whereDoesntHave('bookings', function ($q) use ($checkIn, $checkOut) {
                $q->where('status', '!=', 'cancelled')
                    ->where(function ($sub) use ($checkIn, $checkOut) {
                        $sub->where('check_in_date', '<', $checkOut)
                            ->where('check_out_date', '>', $checkIn);
                    });
            });
        }

        // 7. Sorting (Matches your blade 'sort')
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
     * Display the booking form.
     */
    public function booking(Request $request)
    {
        $selectedRoom = null;
        if ($request->has('room_id')) {
            $selectedRoom = Room::find($request->room_id);
        }

        $rooms = Room::where('status', 'available')->get();

        return view('website::booking', compact('rooms', 'selectedRoom'));
    }

    /**
     * Store a newly created booking in storage.
     * Replaces the old 'submitBooking' method.
     */
    public function storeBooking(StoreBookingRequest $request)
    {
        // 1. Retrieve validated data (safe from mass assignment)
        $validated = $request->validated();

        try {
            return DB::transaction(function () use ($validated) {

                // 2. Find Room (and Lock it to prevent modification during check)
                $room = Room::lockForUpdate()->findOrFail($validated['room_id']);

                // 3. Strict Availability Check (Optimized Overlap Logic)
                $isBooked = Booking::where('room_id', $room->id)
                    ->where('status', '!=', 'cancelled')
                    ->where(function ($query) use ($validated) {
                        $query->where(function ($q) use ($validated) {
                            $q->where('check_in_date', '<', $validated['check_out_date'])
                                ->where('check_out_date', '>', $validated['check_in_date']);
                        });
                    })
                    ->exists();

                if ($isBooked) {
                    throw new \Exception('We apologize, but this room was just booked by another guest for these dates.');
                }

                // 4. Calculate Costs
                $checkIn = Carbon::parse($validated['check_in_date']);
                $checkOut = Carbon::parse($validated['check_out_date']);
                $nights = $checkIn->diffInDays($checkOut);
                $nights = $nights < 1 ? 1 : $nights;
                $totalAmount = $room->price * $nights;

                // 5. Generate Reference
                $reference = 'BK-' . strtoupper(Str::random(8));

                // 6. Create Booking
                $booking = Booking::create([
                    'booking_reference' => $reference,
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
                    'special_requests' => $validated['special_requests'] ?? null,
                ]);

                // 7. Redirect
                return redirect()->route('website.booking.confirmation', ['ref' => $reference])
                    ->with('success', 'Your booking request has been received!');
            });
        } catch (\Exception $e) {
            Log::error("Booking Failed: " . $e->getMessage());

            return back()
                ->withInput()
                ->withErrors(['error' => $e->getMessage()]);
        }
    }

    /**
     * Show confirmation page.
     */
    public function confirmation($ref)
    {
        // FIX: Fetch the actual booking object using the reference string
        $booking = Booking::where('booking_reference', $ref)->firstOrFail();

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

        // Optional: Send email notification (uncomment if mail is set up)
        // Mail::to('admin@luxuryhotelabuja.com')->send(new ContactMessageReceived($validated));

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
     * Smart Availability Check
     * Checks if room is free and suggests next available date if occupied.
     */
    public function checkAvailability(Request $request)
    {
        // 1. Validate Input
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
        ]);

        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);

        // 2. Find ALL overlapping bookings (conflicts)
        $conflicts = Booking::where('room_id', $validated['room_id'])
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($checkIn, $checkOut) {
                // Standard overlap logic: (StartA < EndB) and (EndA > StartB)
                $query->where('check_in_date', '<', $checkOut)
                    ->where('check_out_date', '>', $checkIn);
            })
            ->orderBy('check_out_date', 'desc') // Important: Get the conflict that ends last
            ->get();

        // 3. Scenario A: Room is fully available
        if ($conflicts->isEmpty()) {
            if ($request->wantsJson()) {
                return response()->json([
                    'available' => true,
                    'message' => 'Room is available!',
                    // Send redirection URL to JS
                    'redirect_url' => route('website.booking', $validated)
                ]);
            }
            return redirect()->route('website.booking', $validated)
                ->with('success', 'Room is available! Please complete your booking.');
        }

        // 4. Scenario B: Room is occupied (Smart Suggestion Logic)
        $lastConflict = $conflicts->first(); // The booking that blocks the room longest
        $occupiedUntil = Carbon::parse($lastConflict->check_out_date);

        // Base message
        $message = "This room is currently booked until " . $occupiedUntil->format('l, F j') . ".";

        // If the room becomes free *during* the user's requested window
        // (e.g. User wants 2nd-9th, Room free on 4th. Suggest 4th-9th)
        if ($occupiedUntil->lt($checkOut)) {
            $message .= " However, it is available from " . $occupiedUntil->format('M j') . " to " . $checkOut->format('M j') . ". Would you like to adjust your dates?";
        } else {
            $message .= " Please select different dates.";
        }

        if ($request->wantsJson()) {
            return response()->json([
                'available' => false,
                'message' => $message,
                'suggestion' => [
                    'check_in' => $occupiedUntil->format('Y-m-d'),
                    'check_out' => $checkOut->format('Y-m-d')
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
}
