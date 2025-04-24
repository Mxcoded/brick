<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Website\Models\Booking;
use Modules\Website\Models\Room;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;


class BookingController extends Controller
{
    // public function index(Request $request)
    // {
    //     $query = Booking::with(['room', 'user']);

    //     // Apply filters
    //     if ($request->filled('status')) {
    //         $query->where('status', $request->status);
    //     }
    //     if ($request->filled('check_in_from')) {
    //         $query->whereDate('check_in', '>=', Carbon::parse($request->check_in_from));
    //     }
    //     if ($request->filled('check_in_to')) {
    //         $query->whereDate('check_in', '<=', Carbon::parse($request->check_in_to));
    //     }

    //     $bookings = $query->get();
    //     return view('website::admin.bookings.index', compact('bookings'));
    // }
    public function index()
    {
        $bookings = Booking::with('room')->paginate(10);
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
            'guest_company' => 'nullable|string|max:255',
            'guest_address' => 'nullable|string',
            'guest_nationality' => 'nullable|string|max:100',
            'guest_id_type' => 'nullable|string|max:50',
            'guest_id_number' => 'nullable|string|max:100',
            'number_of_guests' => 'required|integer|min:1',
            'number_of_children' => 'required|integer|min:0',
            'special_requests' => 'nullable|string',
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,cancelled,no_show',
            'total_price' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:pending,partial,paid,refunded',
            'payment_method' => 'nullable|in:credit_card,bank_transfer,cash',
            'source' => 'nullable|in:website,phone,OTA,walk_in',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
        ]);

        $checkIn = Carbon::parse($validated['check_in']);
        $checkOut = Carbon::parse($validated['check_out']);
        if ($checkOut <= $checkIn) {
            Log::warning('Invalid dates in store:', [
                'check_in' => $validated['check_in'],
                'check_out' => $validated['check_out'],
            ]);
            return back()->withErrors(['check_out' => 'Check-out date must be after check-in date.']);
        }
        $validated['check_in'] = $checkIn->format('Y-m-d');
        $validated['check_out'] = $checkOut->format('Y-m-d');

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
            Log::warning('Duplicate booking attempt:', [
                'room_id' => $validated['room_id'],
                'check_in' => $validated['check_in'],
                'check_out' => $validated['check_out'],
            ]);
            return back()->withErrors(['check_in' => 'This room is already booked for the selected dates.']);
        }

        $year = date('Y');
        $yearPrefix = substr($year, -3);
        $prefix = "BK{$yearPrefix}";
        $lastBooking = Booking::where('booking_ref_number', 'like', "{$prefix}%")
            ->orderBy('booking_ref_number', 'desc')
            ->first();
        $nextNumber = $lastBooking ? (int) substr($lastBooking->booking_ref_number, -4) + 1 : 1;
        if ($nextNumber > 9999) {
            throw new \Exception('Maximum bookings for this year reached.');
        }
        $validated['booking_ref_number'] = $prefix . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);

        if (empty($validated['user_id'])) {
            $validated['confirmation_token'] = Str::random(40);
        }

        $room = Room::find($validated['room_id']);
        $days = $checkOut->diffInDays($checkIn);
        $calculatedPrice = abs($room->price_per_night * $days);
        $validated['total_price'] = $calculatedPrice;

        $validated['created_by'] = Auth::id();
        $validated['updated_by'] = Auth::id();

        $booking = Booking::create($validated);

        Log::info('Booking created:', $booking->toArray());
        return redirect()->route('website::admin.bookings.index')->with('success', 'Booking created successfully.');
    }

    public function update(Request $request, Booking $booking)
    {
        Log::info('Booking update request:', $request->all());
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'guest_name' => 'required_without:user_id|string|max:255|regex:/^[A-Za-z ]+$/',
            'guest_email' => 'required_without:user_id|email|max:255',
            'guest_phone' => 'required_without:user_id|string|max:20|regex:/^[0-9]{10,15}$/',
            'user_id' => 'nullable|exists:users,id',
            'check_in' => 'required|date|after_or_equal:today',
            'check_out' => 'required|date|after:check_in',
            'guest_company' => 'nullable|string|max:255',
            'guest_address' => 'nullable|string',
            'guest_nationality' => 'nullable|string|max:100',
            'guest_id_type' => 'nullable|string|max:50|in:passport,driver_license,national_id',
            'guest_id_number' => 'nullable|string|max:100',
            'number_of_guests' => 'required|integer|min:1|max:10',
            'number_of_children' => 'required|integer|min:0|max:10',
            'special_requests' => 'nullable|string',
            'status' => 'required|in:pending,confirmed,checked_in,checked_out,cancelled,no_show',
            'total_price' => 'required|numeric|min:0',
            'deposit_amount' => 'nullable|numeric|min:0',
            'payment_status' => 'required|in:pending,partial,paid,refunded',
            'payment_method' => 'nullable|in:credit_card,bank_transfer,cash',
            'source' => 'nullable|in:website,phone,OTA,walk_in',
            'check_in_time' => 'nullable|date_format:H:i',
            'check_out_time' => 'nullable|date_format:H:i',
            'cancellation_reason' => 'nullable|string|required_if:status,cancelled',
        ]);

        $checkIn = Carbon::parse($validated['check_in']);
        $checkOut = Carbon::parse($validated['check_out']);
        if ($checkOut <= $checkIn) {
            Log::warning('Invalid dates in update:', [
                'check_in' => $validated['check_in'],
                'check_out' => $validated['check_out'],
            ]);
            return back()->withErrors(['check_out' => 'Check-out date must be after check-in date.']);
        }
        $validated['check_in'] = $checkIn->format('Y-m-d');
        $validated['check_out'] = $checkOut->format('Y-m-d');

        $room = Room::find($validated['room_id']);
        $days = $checkOut->diffInDays($checkIn);
        $calculatedPrice = abs($room->price_per_night * $days);
        if (abs($validated['total_price'] - $calculatedPrice) > 0.01) {
            Log::warning('Total price mismatch:', [
                'submitted' => $validated['total_price'],
                'calculated' => $calculatedPrice,
            ]);
            $validated['total_price'] = $calculatedPrice;
        }

        $existingBooking = Booking::where('room_id', $validated['room_id'])
            ->where('id', '!=', $booking->id)
            ->where('status', '!=', 'cancelled')
            ->where(function ($query) use ($checkIn, $checkOut) {
                $query->whereBetween('check_in', [$checkIn, $checkOut])
                    ->orWhereBetween('check_out', [$checkIn, $checkOut])
                    ->orWhereRaw('? BETWEEN check_in AND check_out', [$checkIn])
                    ->orWhereRaw('? BETWEEN check_in AND check_out', [$checkOut]);
            })
            ->first();

        if ($existingBooking) {
            Log::warning('Duplicate booking attempt on update:', [
                'room_id' => $validated['room_id'],
                'check_in' => $validated['check_in'],
                'check_out' => $validated['check_out'],
            ]);
            return back()->withErrors(['check_in' => 'This room is already booked for the selected dates.']);
        }

        $validated['booking_ref_number'] = $booking->booking_ref_number;
        $validated['confirmation_token'] = $booking->confirmation_token;
        $validated['updated_by'] = Auth::id();
        $validated['cancelled_at'] = $validated['status'] === 'cancelled' ? now() : null;

        $booking->update($validated);
        Log::info('Booking updated:', $booking->toArray());
        return redirect()->route('website.admin.bookings.index')->with('success', 'Booking updated successfully.');
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

    public function cancel(Request $request, Booking $booking)
    {
        Log::debug('Cancel booking started', ['booking_id' => $booking->id, 'request' => $request->all()]);

        try {
            if ($booking->status === 'cancelled') {
                Log::warning('Attempt to cancel already cancelled booking', ['booking_id' => $booking->id]);
                return redirect()->route('website.admin.bookings.index')->with('error', 'Booking is already cancelled.');
            }

            $validated = $request->validate([
                'cancellation_reason' => 'nullable|string|max:255',
            ]);

            $booking->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
                'cancellation_reason' => $validated['cancellation_reason'] ?? 'Cancelled by admin',
                'updated_by' => Auth::id(),
            ]);

            Log::info('Booking cancelled', [
                'booking_id' => $booking->id,
                'booking_ref_number' => $booking->booking_ref_number,
                'cancelled_by' => Auth::id(),
                'cancellation_reason' => $validated['cancellation_reason'] ?? 'Cancelled by admin',
            ]);

            return redirect()->route('website.admin.bookings.index')->with('success', 'Booking cancelled successfully.');
        } catch (\Exception $e) {
            Log::error('Error cancelling booking', [
                'booking_id' => $booking->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('website.admin.bookings.index')->with('error', 'Failed to cancel booking. Please try again.');
        }
    }

    public function destroy(Booking $booking)
    {
        Log::debug('Delete booking started', ['booking_id' => $booking->id]);

        try {
            if (!Auth::user()->hasRole('admin')) {
                Log::warning('Unauthorized delete attempt', ['booking_id' => $booking->id, 'user_id' => Auth::id()]);
                return redirect()->route('website.admin.bookings.index')->with('error', 'Unauthorized action.');
            }

            $booking->delete();

            Log::info('Booking deleted', ['booking_id' => $booking->id, 'deleted_by' => Auth::id()]);

            return redirect()->route('website.admin.bookings.index')->with('success', 'Booking deleted successfully.');
        } catch (\Exception $e) {
            Log::error('Error deleting booking', [
                'booking_id' => $booking->id,
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return redirect()->route('website.admin.bookings.index')->with('error', 'Failed to delete booking. Please try again.');
        }
    }
}
