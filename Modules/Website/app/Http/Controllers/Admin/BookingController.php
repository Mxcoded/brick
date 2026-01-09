<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Website\Models\Room;
use Modules\Website\Models\Booking;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings with filters.
     */
    public function index(Request $request)
    {
        $query = Booking::with(['room', 'user'])->latest();

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
     */
    public function create()
    {
        $rooms = Room::where('status', 'available')->get();
        return view('website::admin.bookings.create', compact('rooms'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'guest_phone' => 'required|string|max:20',
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'payment_status' => 'required|in:pending,paid,failed,partial',
            'status' => 'required|in:pending,confirmed,cancelled',
            'admin_notes' => 'nullable|string'
        ]);

        // 1. Availability Check (Unified Logic)
        $isAvailable = Booking::isAvailable(
            $validated['room_id'],
            $validated['check_in_date'],
            $validated['check_out_date']
        );

        if (!$isAvailable) {
            return back()->withErrors(['room_id' => 'This room is not available for the selected dates (overlaps with another booking or active guest).'])->withInput();
        }

        // 2. Calculate Total Amount
        $room = Room::findOrFail($validated['room_id']);
        $checkIn = Carbon::parse($validated['check_in_date']);
        $checkOut = Carbon::parse($validated['check_out_date']);
        $nights = $checkIn->diffInDays($checkOut);

        $nights = $nights < 1 ? 1 : $nights;
        $totalAmount = $room->price * $nights;

        // 3. Create Booking
        Booking::create([
            'booking_reference' => 'BK-' . strtoupper(Str::random(8)),
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
            'status' => $validated['status'],
            'admin_notes' => $validated['admin_notes'],
        ]);

        return redirect()->route('website.admin.bookings.index')
            ->with('success', 'Booking created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $booking = Booking::with(['room', 'user'])->findOrFail($id);
        return view('website::admin.bookings.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $booking = Booking::findOrFail($id);
        $rooms = Room::all();
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
            'payment_status' => 'required|in:pending,paid,failed,partial',
            'status' => 'required|in:pending,confirmed,cancelled,checked_in,completed',
            'admin_notes' => 'nullable|string'
        ]);

        // 1. Availability Check (Only if dates or room changed)
        if (
            $booking->room_id != $request->room_id ||
            $booking->check_in_date->format('Y-m-d') != $request->check_in_date ||
            $booking->check_out_date->format('Y-m-d') != $request->check_out_date
        ) {
            $isAvailable = Booking::isAvailable(
                $request->room_id,
                $request->check_in_date,
                $request->check_out_date,
                $id // Ignore current booking ID
            );

            if (!$isAvailable) {
                return back()->withErrors(['room_id' => 'Room unavailable for these new dates.'])->withInput();
            }

            // Recalculate price if dates/room changed
            $room = Room::findOrFail($request->room_id);
            $nights = Carbon::parse($request->check_in_date)->diffInDays(Carbon::parse($request->check_out_date));
            $booking->total_amount = $room->price * ($nights < 1 ? 1 : $nights);
        }

        $booking->update($validated);

        return redirect()->back()->with('success', 'Booking updated successfully.');
    }

    /**
     * Manual Confirm Method (For the Action Button)
     */
    public function confirm($id)
    {
        $booking = Booking::findOrFail($id);

        // Optional: Re-verify availability before confirming
        $isAvailable = Booking::isAvailable(
            $booking->room_id,
            $booking->check_in_date,
            $booking->check_out_date,
            $id
        );

        if (!$isAvailable) {
            return back()->with('error', 'Cannot confirm: This room is now occupied or double-booked.');
        }

        $booking->update(['status' => 'confirmed']);

        // Optional: Send Email Confirmation here
        // Mail::to($booking->guest_email)->send(new BookingConfirmed($booking));

        return back()->with('success', 'Booking confirmed successfully.');
    }

    /**
     * Manual Cancel Method (For the Action Button)
     */
    public function cancel($id)
    {
        $booking = Booking::findOrFail($id);

        $booking->update(['status' => 'cancelled']);

        // Optional: Send Cancellation Email

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
}
