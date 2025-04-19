<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Website\Models\Booking;
use Modules\Website\Models\Room;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BookingController extends Controller
{
    public function index()
    {
        $bookings = Booking::with(['room', 'user'])->get();
        return view('website::admin.bookings.index', compact('bookings'));
    }

    public function create()
    {
        $rooms = Room::all();
        return view('website::admin.bookings.create', compact('rooms'));
    }

    public function store(Request $request)
    {
        Log::info('Booking store request:', $request->all());
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'guest_name' => 'required_without:user_id|string|max:255',
            'guest_email' => 'required_without:user_id|email|max:255',
            'guest_phone' => 'required_without:user_id|string|max:20',
            'user_id' => 'nullable|exists:users,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'status' => 'required|in:pending,confirmed,cancelled',
        ]);

        // Parse dates
        $validated['check_in'] = Carbon::parse($validated['check_in'])->format('Y-m-d');
        $validated['check_out'] = Carbon::parse($validated['check_out'])->format('Y-m-d');

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

        // Add confirmation token for non-authenticated users
        if (empty($validated['user_id'])) {
            $validated['confirmation_token'] = Str::random(40);
        }

        $booking = Booking::create($validated);

        Log::info('Booking created:', $booking->toArray());
        return redirect()->route('website.admin.bookings.index')->with('success', 'Booking created successfully.');
    }

    public function show(Booking $booking)
    {
        $booking->load(['room', 'user']);
        return view('website::admin.bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        $rooms = Room::all();
        return view('website::admin.bookings.edit', compact('booking', 'rooms'));
    }

    public function update(Request $request, Booking $booking)
    {
        Log::info('Booking update request:', $request->all());
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'guest_name' => 'required_without:user_id|string|max:255',
            'guest_email' => 'required_without:user_id|email|max:255',
            'guest_phone' => 'required_without:user_id|string|max:20',
            'user_id' => 'nullable|exists:users,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'status' => 'required|in:pending,confirmed,cancelled',
        ]);

        $validated['check_in'] = Carbon::parse($validated['check_in'])->format('Y-m-d');
        $validated['check_out'] = Carbon::parse($validated['check_out'])->format('Y-m-d');

        // Preserve existing booking_ref_number and confirmation_token
        $validated['booking_ref_number'] = $booking->booking_ref_number;
        $validated['confirmation_token'] = $booking->confirmation_token;

        $booking->update($validated);
        Log::info('Booking updated:', $booking->toArray());
        return redirect()->route('website.admin.bookings.index')->with('success', 'Booking updated successfully.');
    }

    public function destroy(Booking $booking)
    {
        Log::info('Booking deletion triggered:', ['booking_id' => $booking->id]);
        $booking->delete();
        Log::info('Booking deleted:', ['booking_id' => $booking->id]);
        return redirect()->route('website.admin.bookings.index')->with('success', 'Booking deleted successfully.');
    }
}
