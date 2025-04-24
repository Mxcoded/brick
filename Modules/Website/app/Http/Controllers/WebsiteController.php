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

class WebsiteController extends Controller
{
    public function index()
    {
        $settings = $this->getSettings();
        $featuredRooms = Room::where('featured', true)->take(3)->get();
        $diningOptions = Dining::where('is_featured', true)->take(3)->get();
        $testimonials = Testimonial::where('approved', true)->latest()->take(3)->get();
        return view('website::index', compact('settings', 'featuredRooms', 'diningOptions', 'testimonials'));
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
        Log::debug('submitBooking started', ['request' => $request->all()]);

        try {
            $validated = $request->validate([
                'room_id' => 'required|exists:rooms,id',
                'check_in' => 'required|date|after_or_equal:today',
                'check_out' => 'required|date|after:check_in',
                'guest_name' => 'required|string|max:255|regex:/^[A-Za-z ]+$/',
                'guest_email' => 'required|email|max:255',
                'guest_phone' => 'required|string|max:20|regex:/^[0-9]{10,15}$/',
                'guests' => 'required|integer|min:1|max:10',
                'guest_company' => 'nullable|string|max:255',
                'guest_address' => 'nullable|string',
                'guest_nationality' => 'nullable|string|max:100',
                'guest_id_type' => 'nullable|string|max:50|in:passport,driver_license,national_id',
                'guest_id_number' => 'nullable|string|max:100',
                'number_of_children' => 'required|integer|min:0|max:10',
                'special_requests' => 'nullable|string',
                'payment_method' => 'nullable|in:credit_card,bank_transfer,cash',
            ]);

            Log::debug('Validation passed', ['validated' => $validated]);

            $checkIn = Carbon::parse($validated['check_in']);
            $checkOut = Carbon::parse($validated['check_out']);
            if ($checkOut->lessThanOrEqualTo($checkIn)) {
                Log::warning('Invalid dates in submitBooking', [
                    'check_in' => $validated['check_in'],
                    'check_out' => $validated['check_out'],
                ]);
                return back()->withErrors(['check_out' => 'Check-out date must be after check-in date.']);
            }
            $validated['check_in'] = $checkIn->format('Y-m-d');
            $validated['check_out'] = $checkOut->format('Y-m-d');

            Log::debug('Date parsing complete', ['check_in' => $validated['check_in'], 'check_out' => $validated['check_out']]);

            $validated['number_of_guests'] = $validated['guests'];
            unset($validated['guests']);

            $existingBooking = Booking::where('room_id', $validated['room_id'])
                ->where('status', '!=', 'cancelled')
                ->where(function ($query) use ($checkIn, $checkOut) {
                    $query->whereBetween('check_in', [$checkIn, $checkOut])
                        ->orWhereBetween('check_out', [$checkIn, $checkOut])
                        ->orWhereRaw('? BETWEEN check_in AND check_out', [$checkIn])
                        ->orWhereRaw('? BETWEEN check_in AND check_out', [$checkOut]);
                })
                ->first();

            if ($existingBooking) {
                Log::warning('Duplicate booking attempt (frontend)', [
                    'room_id' => $validated['room_id'],
                    'check_in' => $validated['check_in'],
                    'check_out' => $validated['check_out'],
                    'existing_booking_id' => $existingBooking->id,
                ]);
                return back()->withErrors(['check_in' => 'This room is already booked for the selected dates.']);
            }

            Log::debug('No duplicate bookings found');

            if (Auth::check()) {
                $validated['user_id'] = Auth::id();
                $validated['created_by'] = Auth::id();
                $validated['updated_by'] = Auth::id();
            } else {
                $validated['confirmation_token'] = Str::random(40);
                $validated['created_by'] = null;
                $validated['updated_by'] = null;
            }

            Log::debug('Auth and token set', ['user_id' => $validated['user_id'] ?? null, 'confirmation_token' => $validated['confirmation_token'] ?? null]);

            $room = Room::find($validated['room_id']);
            if (!$room) {
                Log::error('Room not found', ['room_id' => $validated['room_id']]);
                return back()->withErrors(['room_id' => 'Selected room does not exist.']);
            }

            $days = $checkIn->diffInDays($checkOut); // Reverse order to ensure positive
            $days = max(1, $days); // Ensure at least 1 night
            Log::debug('Nights calculated', ['days' => $days, 'check_in' => $validated['check_in'], 'check_out' => $validated['check_out']]);
            $validated['total_price'] = $room->price_per_night * $days;
            $validated['status'] = 'pending';
            $validated['payment_status'] = 'pending';
            $validated['source'] = 'website';

            Log::debug('Price and status set', ['total_price' => $validated['total_price'], 'days' => $days]);

            $year = date('Y');
            $yearPrefix = substr($year, -3);
            $prefix = "BK{$yearPrefix}";
            $lastBooking = Booking::where('booking_ref_number', 'like', "{$prefix}%")
                ->orderBy('booking_ref_number', 'desc')
                ->first();
            $nextNumber = $lastBooking ? (int) substr($lastBooking->booking_ref_number, -4) + 1 : 1;
            if ($nextNumber > 9999) {
                Log::error('Maximum bookings for this year reached', ['year' => $year]);
                throw new \Exception('Maximum bookings for this year reached.');
            }
            $validated['booking_ref_number'] = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

            Log::debug('Booking reference generated', ['booking_ref_number' => $validated['booking_ref_number']]);

            $booking = Booking::create($validated);

            Log::info('Frontend booking created', $booking->toArray());

            if (!Auth::check()) {
                $request->session()->put('booking_email', $validated['guest_email']);
            }

            return redirect()->route('website.booking.confirmation', [
                'booking' => $booking->id,
                'token' => $booking->confirmation_token ?? '',
            ])->with('success', 'Booking created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('Validation failed in submitBooking', [
                'errors' => $e->errors(),
                'request' => $request->all(),
            ]);
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error in submitBooking', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'request' => $request->all(),
            ]);
            return back()->withErrors(['general' => 'An error occurred while creating the booking. Please try again.'])->withInput();
        }
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

    public function submitContact(Request $request)
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

    /**
     * Settings
     */
    protected function getSettings()
    {
        return Settings::pluck('value', 'key')->toArray();
    }
}
