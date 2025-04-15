<?php

namespace Modules\Website\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Website\Models\Room;
use Modules\Website\Models\Testimonial;
use Modules\Website\Models\Dining;
use Modules\Website\Models\Booking;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class WebsiteController extends Controller
{
    public function index()
    {
        $featuredRooms = Room::where('featured', true)->take(3)->get();
        $diningOptions = Dining::where('is_featured', true)->take(3)->get(); // Fetch featured dining options
        $testimonials = Testimonial::where('approved', true)->latest()->take(3)->get();
        return view('website::index', compact('featuredRooms', 'diningOptions', 'testimonials'));
    }

    public function rooms(Request $request)
    {
        $query = Room::query();

        // Filters
        if ($request->has('min_price')) {
            $query->where('price_per_night', '>=', $request->input('min_price'));
        }
        if ($request->has('max_price')) {
            $query->where('price_per_night', '<=', $request->input('max_price'));
        }
        if ($request->has('guests')) {
            $query->where('capacity', '>=', $request->input('guests'));
        }
        if ($request->has('sort')) {
            $sort = $request->input('sort');
            if ($sort === 'price_asc') {
                $query->orderBy('price_per_night', 'asc');
            } elseif ($sort === 'price_desc') {
                $query->orderBy('price_per_night', 'desc');
            }
        }

        $rooms = $query->get();
        return view('website::rooms', compact('rooms'));
    }
    public function roomDetails(Room $room)
    {
        $room->load('amenities', 'images');
        $relatedRooms = Room::where('id', '!=', $room->id)->take(3)->get();
        return view('website::room-details', compact('room', 'relatedRooms'));
    }
    public function bookingForm(Request $request)
    {
        $checkIn = $request->input('check_in', date('Y-m-d'));
        $checkOut = $request->input('check_out', date('Y-m-d', strtotime('+1 day')));

        $rooms = Room::whereDoesntHave('bookings', function ($query) use ($checkIn, $checkOut) {
            $query->where(function ($q) use ($checkIn, $checkOut) {
                $q->whereBetween('check_in', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out', [$checkIn, $checkOut])
                    ->orWhere(function ($q) use ($checkIn, $checkOut) {
                        $q->where('check_in', '<=', $checkIn)
                            ->where('check_out', '>=', $checkOut);
                    });
            })->where('status', 'confirmed');
        })->get();

        return view('website::booking', compact('rooms', 'checkIn', 'checkOut'));
    }

    public function submitBooking(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email',
            'guest_phone' => 'required|string|max:20',
        ]);

        if (Auth::check()) {
            $validated['user_id'] = Auth::id();
        } else {
            $validated['confirmation_token'] = Str::random(40);
        }

        // Generate booking reference number: BK + last 3 digits of year + 4-digit incremental
        $year = date('Y'); // e.g., 2025
        $yearPrefix = substr($year, -3); // e.g., "025"
        $prefix = "BK{$yearPrefix}"; // e.g., "BK025"

        // Find the highest existing number for this year
        $lastBooking = Booking::where('booking_ref_number', 'like', "{$prefix}%")
            ->orderBy('booking_ref_number', 'desc')
            ->first();

        $nextNumber = 1;
        if ($lastBooking) {
            $lastNumber = (int) substr($lastBooking->booking_ref_number, -4);
            $nextNumber = min($lastNumber + 1, 9999); // Cap at 9999
            if ($nextNumber === 9999 && Booking::where('booking_ref_number', "{$prefix}9999")->exists()) {
                throw new \Exception('Maximum bookings for this year reached.');
            }
        }

        $validated['booking_ref_number'] = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        $booking = Booking::create($validated);

        if (!Auth::check()) {
            $request->session()->put('booking_email', $validated['guest_email']);
        }

        // Optional: Send email
        // Mail::to($validated['guest_email'])->send(new BookingConfirmation($booking));

        return redirect()->route('website.booking.confirmation', [
            'booking' => $booking->id,
            'token' => $booking->confirmation_token ?? '',
        ]);
    }

    public function bookingConfirmation(Request $request, Booking $booking)
    {
        // For logged-in users, check user_id
        if (Auth::check()) {
            if ($booking->user_id === Auth::id()) {
                return view('website::booking-confirmation', compact('booking'));
            }
            return redirect()->route('website.home')->with('error', 'Unauthorized access to booking.');
        }

        // For non-logged-in users, verify token
        if ($booking->confirmation_token && $request->query('token') === $booking->confirmation_token) {
            return view('website::booking-confirmation', compact('booking'));
        }

        return redirect()->route('website.home')->with('error', 'Unauthorized access to booking.');
    }
    public function amenities()
    {
        return view('website::amenities');
    }

    public function location()
    {
        return view('website::location');
    }

    public function contact()
    {
        return view('website::contact');
    }

    public function submitContact(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email',
            'message' => 'required|string|max:1000',
        ]);

        // Logic to handle contact form (e.g., send email)
        return redirect()->route('website.contact')->with('success', 'Your message has been sent!');
    }

    public function about()
    {
        return view('website::about');
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
    public function checkAvailability(Request $request, Room $room)
    {
        try {
            $validated = $request->validate([
                'check_in' => 'required|date|after_or_equal:today',
                'check_out' => 'required|date|after:check_in',
            ]);

            $checkIn = $validated['check_in'];
            $checkOut = $validated['check_out'];

            $overlappingBookings = $room->bookings()
                ->where(function ($query) use ($checkIn, $checkOut) {
                    $query->whereBetween('check_in', [$checkIn, $checkOut])
                        ->orWhereBetween('check_out', [$checkIn, $checkOut])
                        ->orWhere(function ($query) use ($checkIn, $checkOut) {
                            $query->where('check_in', '<=', $checkIn)
                                ->where('check_out', '>=', $checkOut);
                        });
                })
                ->exists();

            return response()->json([
                'available' => !$overlappingBookings,
                'message' => $overlappingBookings
                    ? 'This room is not available for the selected dates.'
                    : 'This room is available for the selected dates.'
            ], 200, ['Content-Type' => 'application/json']);
        } catch (\Exception $e) {
            return response()->json([
                'available' => false,
                'message' => 'An error occurred: ' . $e->getMessage()
            ], 500, ['Content-Type' => 'application/json']);
        }
    }
}
