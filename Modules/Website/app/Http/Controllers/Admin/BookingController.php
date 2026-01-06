<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Website\Models\Room;
use Modules\Website\Models\Booking;
use Modules\Frontdeskcrm\Models\Registration;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings with filters.
     */
    public function index(Request $request)
    {
        $query = Booking::with('room')->latest();

        // 1. Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 2. Filter by Date (Check-in)
        if ($request->filled('date')) {
            $query->whereDate('check_in_date', $request->date);
        }

        // 3. Search by Name, Email, or Reference
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_reference', 'like', "%$search%")
                    ->orWhere('guest_name', 'like', "%$search%")
                    ->orWhere('guest_email', 'like', "%$search%");
            });
        }

        $bookings = $query->paginate(15)->withQueryString();

        return view('website::admin.bookings.index', compact('bookings'));
    }

    /**
     * Show the form for creating a new resource.
     * Note: Admins usually use Frontdesk CRM to book, but this is a fallback.
     */
    public function create()
    {
        // Only show rooms that are operationally available (not in maintenance)
        $rooms = Room::where('status', '!=', 'maintenance')->get();
        return view('website::admin.bookings.create', compact('rooms'));
    }

    /**
     * Store a newly created booking in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'guest_phone' => 'required|string|max:20',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'payment_status' => 'required|in:pending,paid,failed',
            'special_requests' => 'nullable|string'
        ]);

        $room = Room::findOrFail($validated['room_id']);

        // 1. Calculate Total Amount
        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $nights = $checkIn->diffInDays($checkOut) ?: 1;
        $totalAmount = $room->price * $nights;

        // 2. Create Booking
        // Note: We DO NOT change $room->status here. Room status (available/booked) 
        // is calculated dynamically based on dates, not a static database field.
        Booking::create([
            'booking_reference' => 'BK-' . strtoupper(uniqid()),
            'room_id' => $validated['room_id'],
            'guest_name' => $validated['guest_name'],
            'guest_email' => $validated['guest_email'],
            'guest_phone' => $validated['guest_phone'],
            'check_in_date' => $validated['check_in_date'],
            'check_out_date' => $validated['check_out_date'],
            'adults' => $validated['adults'],
            'children' => $validated['children'] ?? 0,
            'total_amount' => $totalAmount,
            'payment_status' => $validated['payment_status'],
            'status' => 'confirmed', // Admin created bookings are usually confirmed immediately
            'special_requests' => $validated['special_requests'],
        ]);

        return redirect()->route('website.admin.bookings.index')
            ->with('success', 'Booking created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $booking = Booking::with('room')->findOrFail($id);
        return view('website::admin.bookings.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $booking = Booking::findOrFail($id);
        $rooms = Room::where('status', '!=', 'maintenance')->get();
        return view('website::admin.bookings.edit', compact('booking', 'rooms'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,cancelled',
            'payment_status' => 'required|in:pending,paid,failed,refunded',
        ]);

        $booking->update($validated);

        return redirect()->route('website.admin.bookings.index')
            ->with('success', 'Booking updated successfully.');
    }

    /**
     * Confirm a booking manually.
     * Prevents Double Booking by checking Frontdesk CRM.
     */
    public function confirm($id)
    {
        $booking = Booking::findOrFail($id);

        if ($booking->status === 'confirmed') {
            return back()->with('info', 'Booking is already confirmed.');
        }

        // 1. Cross-Module Availability Check (Frontdesk Integration)
        if ($this->isRoomOccupiedInFrontdesk($booking->room_id, $booking->check_in_date, $booking->check_out_date)) {
            return back()->with('error', 'Cannot Confirm: Room is physically occupied in Frontdesk CRM for these dates.');
        }

        // 2. Update Status
        $booking->update([
            'status' => 'confirmed',
            'payment_status' => 'paid', // Assuming manual confirmation means money received
        ]);

        // Optional: Send Confirmation Email here

        return back()->with('success', 'Booking confirmed successfully.');
    }

    /**
     * Cancel a booking.
     */
    public function cancel($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => 'cancelled']);

        return back()->with('success', 'Booking cancelled successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        $booking = Booking::findOrFail($id);
        $booking->delete();

        return redirect()->route('website.admin.bookings.index')
            ->with('success', 'Booking deleted successfully.');
    }

    /**
     * Helper: Check Frontdesk CRM for conflicts.
     * Returns TRUE if room is occupied.
     */
    private function isRoomOccupiedInFrontdesk($roomId, $checkIn, $checkOut)
    {
        // Check if Frontdesk module exists
        if (!class_exists(Registration::class)) {
            return false;
        }

        // Check for overlapping registrations
        return Registration::where('room_id', $roomId)
            ->whereIn('status', ['checked_in', 'reserved'])
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->where('check_in_date', '<', $checkOut)
                    ->where('check_out_date', '>', $checkIn);
            })
            ->exists();
    }
}
