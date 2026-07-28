<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Finance\Services\PostingService;
use Modules\Frontdeskcrm\Rules\ValidPhoneNumber;
use Modules\Website\Models\Booking;
use Modules\Website\Models\Room;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;
use Modules\Website\Services\RoomAssignmentService;
use Modules\Website\Services\WebsiteRateService;

class BookingController extends Controller
{
    /**
     * Display a listing of bookings with filters.
     */
    public function index(Request $request)
    {
        $query = Booking::with(['roomType', 'roomUnit', 'room', 'user'])->latest();

        // 1. Filter by Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 2. Filter by Date (Check-in)
        if ($request->filled('date')) {
            $query->whereDate('check_in_date', $request->date);
        }

        // 3. Search by Name, Email, Reference, or Group ID
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('booking_reference', 'like', "%$search%")
                    ->orWhere('booking_group_id', 'like', "%$search%")
                    ->orWhere('guest_name', 'like', "%$search%")
                    ->orWhere('guest_email', 'like', "%$search%")
                    ->orWhere('guest_phone', 'like', "%$search%");
            });
        }

        $bookings = $query->paginate(10)->withQueryString();

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
            'guest_phone' => ['required', 'string', 'max:20', new ValidPhoneNumber],
            'room_id' => 'required|exists:rooms,id',
            'check_in_date' => 'required|date|after_or_equal:today',
            'check_out_date' => 'required|date|after:check_in_date',
            'adults' => 'required|integer|min:1',
            'children' => 'nullable|integer|min:0',
            'payment_status' => 'required|in:pending,paid,failed,partial',
            'status' => 'required|in:pending,confirmed,cancelled',
            'admin_notes' => 'nullable|string',
        ]);

        // 1. Availability Check (Unified Logic)
        $isAvailable = Booking::isAvailable(
            $validated['room_id'],
            $validated['check_in_date'],
            $validated['check_out_date']
        );

        if (! $isAvailable) {
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
        $booking = Booking::create([
            'booking_reference' => 'BK-'.strtoupper(Str::random(8)),
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

        $booking->sendNotification('Booking Created');

        return redirect()->route('website.admin.bookings.index')
            ->with('success', 'Booking created successfully.');
    }

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        $booking = Booking::with(['roomType', 'roomUnit', 'room', 'user', 'guest'])->findOrFail($id);

        return view('website::admin.bookings.show', compact('booking'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $booking = Booking::with(['roomType', 'roomUnit'])->findOrFail($id);
        $roomTypes = RoomType::active()->ordered()->get();

        return view('website::admin.bookings.edit', compact('booking', 'roomTypes'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        // Financial fields are never edited via form — only through payment workflows
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'room_unit_id' => 'nullable|exists:room_units,id',
            'check_in_date' => 'required|date',
            'check_out_date' => 'required|date|after:check_in_date',
            'guest_name' => 'required|string|max:255',
            'guest_email' => 'required|email|max:255',
            'guest_phone' => 'nullable|string|max:50',
            'adults' => 'required|integer|min:1|max:10',
            'children' => 'nullable|integer|min:0|max:10',
            'status' => 'required|in:pending,confirmed,cancelled,checked_in,completed',
            'special_requests' => 'nullable|string',
            'admin_notes' => 'nullable|string',
        ]);

        // Check availability if dates or room type changed
        if (
            $booking->room_type_id != $validated['room_type_id'] ||
            $booking->check_in_date->format('Y-m-d') != $validated['check_in_date'] ||
            $booking->check_out_date->format('Y-m-d') != $validated['check_out_date']
        ) {
            if (! empty($validated['room_unit_id'])) {
                $isAvailable = RoomUnit::find($validated['room_unit_id'])?->isAvailableForDates(
                    $validated['check_in_date'],
                    $validated['check_out_date'],
                    $id
                );

                if (! $isAvailable) {
                    return back()->withErrors(['room_unit_id' => 'Selected room unit is unavailable for these dates.'])->withInput();
                }
            }

            // Auto-recalculate total when room type or dates change (includes guest fees)
            $roomType = RoomType::find($validated['room_type_id']);
            $rateService = app(WebsiteRateService::class);
            $rate = $rateService->calculateWithGuests(
                $roomType,
                $validated['check_in_date'],
                $validated['check_out_date'],
                $validated['adults'],
                $validated['children'] ?? 0
            );
            $booking->total_amount = $rate['total'];
        }

        // Validate guest count against capacity
        $roomType = $roomType ?? RoomType::find($validated['room_type_id'] ?? $booking->room_type_id);
        if ($roomType) {
            $totalGuests = $validated['adults'] + ($validated['children'] ?? 0);
            if ($totalGuests > $roomType->capacity) {
                return back()->withErrors(['adults' => 'Total guests ('.$totalGuests.') exceed room capacity ('.$roomType->capacity.').'])->withInput();
            }
        }

        // Prevent status regression (e.g. checked_in → confirmed)
        $statusFlow = ['pending' => 0, 'confirmed' => 1, 'checked_in' => 2, 'completed' => 3, 'cancelled' => 4];
        $currentRank = $statusFlow[$booking->status] ?? 0;
        $newRank = $statusFlow[$validated['status']] ?? 0;
        if ($newRank < $currentRank && $validated['status'] !== 'cancelled') {
            return back()->withErrors(['status' => 'Cannot move booking backwards from '.ucfirst(str_replace('_', ' ', $booking->status)).' to '.ucfirst(str_replace('_', ' ', $validated['status'])).'.'])->withInput();
        }

        $previousStatus = $booking->status;
        $booking->update($validated);

        // Send notification if status actually changed
        if ($previousStatus !== $validated['status']) {
            $labels = [
                'checked_in' => 'Checked In',
                'completed' => 'Checkout Complete',
                'cancelled' => 'Booking Cancelled',
                'confirmed' => 'Booking Confirmed',
            ];
            $booking->sendNotification($labels[$validated['status']] ?? null);
        }

        return redirect()->back()->with('success', 'Booking updated successfully.');
    }

    /**
     * Mark a booking as fully paid (manual payment confirmation).
     */
    public function markPaid($id)
    {
        $booking = Booking::findOrFail($id);
        $balance = $booking->total_amount - ($booking->amount_paid ?? 0);

        if ($balance <= 0) {
            return back()->with('error', 'This booking has no outstanding balance.');
        }

        $booking->update([
            'amount_paid' => $booking->total_amount,
            'payment_status' => 'paid',
        ]);

        // The model's saving event auto-confirms pending → confirmed, so send confirmation
        $booking->fresh()->sendNotification('Payment Confirmed');

        return redirect()->back()->with('success', 'Payment recorded. Booking marked as paid (₦'.number_format($booking->total_amount, 2).').');
    }

    /**
     * Manual Confirm Method (For the Action Button)
     */
    public function confirm($id)
    {
        $booking = Booking::with(['roomType', 'roomUnit'])->findOrFail($id);

        // Check availability based on booking type (new room type system vs legacy)
        $isAvailable = true;

        if ($booking->room_type_id) {
            // New room type system - check if assigned room unit is still available
            if ($booking->roomUnit) {
                $isAvailable = $booking->roomUnit->isAvailableForDates(
                    $booking->check_in_date->format('Y-m-d'),
                    $booking->check_out_date->format('Y-m-d'),
                    $booking->id // Exclude current booking from check
                );
            } else {
                // No room unit assigned yet - check if room type has any availability
                $availableUnits = $booking->roomType?->getAvailabilityCountForDates(
                    $booking->check_in_date->format('Y-m-d'),
                    $booking->check_out_date->format('Y-m-d')
                );
                $isAvailable = $availableUnits > 0;
            }
        } elseif ($booking->room_id) {
            // Legacy system - use old availability check
            $isAvailable = Booking::isAvailable(
                $booking->room_id,
                $booking->check_in_date,
                $booking->check_out_date,
                $id
            );
        }

        if (! $isAvailable) {
            return back()->with('error', 'Cannot confirm: The room/room type is no longer available for these dates.');
        }

        // 1. Update Status & Mark as PAID (also update amount_paid to clear balance)
        $booking->update([
            'status' => 'confirmed',
            'payment_status' => 'paid',
            'amount_paid' => $booking->total_amount, // ✅ Clear balance due
        ]);

        try {
            app(PostingService::class)
                ->recordSale('website', (float) $booking->total_amount, $booking->payment_method ?? 'transfer', 'booking', $booking->id);
        } catch (\Throwable $e) {
            report($e);
        }

        // 2. Send Confirmation Email
        $booking->sendNotification('Booking Confirmed');

        return back()->with('success', 'Booking confirmed successfully.');
    }

    /**
     * Manual Cancel Method (For the Action Button)
     */
    public function cancel($id)
    {
        $booking = Booking::findOrFail($id);

        $booking->update([
            'status' => 'cancelled',
            'payment_status' => 'void',
        ]);

        $booking->sendNotification('Booking Cancelled');

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
     * Resend the confirmation email manually.
     */
    public function resendConfirmation($id)
    {
        $booking = Booking::findOrFail($id);

        try {
            Mail::to($booking->guest_email)->send(new BookingConfirmation($booking));

            return back()->with('success', 'Confirmation email sent successfully to '.$booking->guest_email);
        } catch (\Exception $e) {
            Log::error('Admin Resend Email Failed: '.$e->getMessage());

            return back()->with('error', 'Failed to send email. Check mail server logs.');
        }
    }

    /**
     * Assign a specific room unit to the booking.
     */
    public function assignRoom(Request $request, $id)
    {
        $booking = Booking::with(['roomType'])->findOrFail($id);

        // Handle unassign request
        if ($request->has('unassign') && $request->unassign == '1') {
            $oldRoom = $booking->roomUnit?->room_number;
            $booking->update(['room_unit_id' => null]);

            Log::info('Room unassigned from booking:', [
                'booking_id' => $booking->id,
                'booking_reference' => $booking->booking_reference,
                'previous_room' => $oldRoom,
            ]);

            return back()->with('success', 'Room has been unassigned. The booking is now marked as "Room TBA".');
        }

        $request->validate([
            'room_unit_id' => 'required|exists:room_units,id',
        ]);

        // Verify the room unit belongs to the booked room type
        $roomUnit = RoomUnit::findOrFail($request->room_unit_id);

        if ($booking->room_type_id && $roomUnit->room_type_id != $booking->room_type_id) {
            return back()->with('error', 'Selected room does not belong to the booked room type.');
        }

        // Check availability for the dates (excluding current booking)
        $isAvailable = $roomUnit->isAvailableForDates(
            $booking->check_in_date->format('Y-m-d'),
            $booking->check_out_date->format('Y-m-d'),
            $booking->id
        );

        if (! $isAvailable) {
            return back()->with('error', 'Selected room is not available for the booking dates.');
        }

        $oldRoom = $booking->roomUnit?->room_number;
        $booking->update(['room_unit_id' => $request->room_unit_id]);

        Log::info('Room assigned to booking:', [
            'booking_id' => $booking->id,
            'booking_reference' => $booking->booking_reference,
            'previous_room' => $oldRoom,
            'new_room' => $roomUnit->room_number,
        ]);

        return back()->with('success', 'Room '.$roomUnit->room_number.' has been assigned to this booking.');
    }

    /**
     * Auto-assign the best available room to a booking.
     */
    public function autoAssignRoom(Request $request, $id)
    {
        $booking = Booking::with(['roomType'])->findOrFail($id);

        if (! $booking->room_type_id) {
            return back()->with('error', 'This booking has no room type assigned. Please set a room type first.');
        }

        if ($booking->room_unit_id) {
            return back()->with('error', 'This booking already has a room assigned. Unassign it first or use the manual selector.');
        }

        $assignmentService = app(RoomAssignmentService::class);
        $unit = $assignmentService->autoAssign($booking);

        if (! $unit) {
            return back()->with('error', 'No available rooms for the booked type and dates.');
        }

        Log::info('Room auto-assigned to booking:', [
            'booking_id' => $booking->id,
            'booking_reference' => $booking->booking_reference,
            'room' => $unit->room_number,
        ]);

        return back()->with('success', 'Room '.$unit->room_number.' auto-assigned to this booking.');
    }

    /**
     * Move Booking to a Different Room (Legacy - kept for backward compatibility)
     */
    public function moveRoom(Request $request, $id)
    {
        $booking = Booking::findOrFail($id);

        $request->validate([
            'new_room_id' => 'required|exists:rooms,id|different:old_room_id',
        ]);

        // Availability Check for the NEW room
        $isAvailable = Booking::isAvailable(
            $request->new_room_id,
            $booking->check_in_date,
            $booking->check_out_date,
            $booking->id // Ignore self
        );

        if (! $isAvailable) {
            return back()->with('error', 'Target room is not available for these dates.');
        }

        $booking->update(['room_id' => $request->new_room_id]);

        return back()->with('success', 'Booking moved successfully.');
    }

    /**
     * Change the room type for a booking (with price recalculation).
     */
    public function changeRoomType(Request $request, $id)
    {
        $booking = Booking::with(['roomType', 'roomUnit'])->findOrFail($id);

        $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'room_unit_id' => 'nullable|exists:room_units,id',
            'recalculate_price' => 'nullable|boolean',
        ]);

        $newRoomType = RoomType::findOrFail($request->room_type_id);
        $oldRoomType = $booking->roomType;
        $oldRoomUnit = $booking->roomUnit;

        // If a room unit is selected, verify it belongs to the selected room type
        $newRoomUnitId = null;
        if ($request->filled('room_unit_id')) {
            $roomUnit = RoomUnit::findOrFail($request->room_unit_id);

            if ($roomUnit->room_type_id != $request->room_type_id) {
                return back()->with('error', 'Selected room does not belong to the selected room type.');
            }

            // Check availability
            $isAvailable = $roomUnit->isAvailableForDates(
                $booking->check_in_date->format('Y-m-d'),
                $booking->check_out_date->format('Y-m-d'),
                $booking->id
            );

            if (! $isAvailable) {
                return back()->with('error', 'Selected room is not available for the booking dates.');
            }

            $newRoomUnitId = $roomUnit->id;
        }

        // Calculate new price if requested
        $newTotalAmount = $booking->total_amount;
        if ($request->boolean('recalculate_price') && $newRoomType->price != ($oldRoomType->price ?? 0)) {
            $nights = $booking->check_in_date->diffInDays($booking->check_out_date);
            $nights = $nights < 1 ? 1 : $nights;
            $newTotalAmount = $newRoomType->price * $nights;
        }

        // Update the booking
        $booking->update([
            'room_type_id' => $request->room_type_id,
            'room_unit_id' => $newRoomUnitId,
            'total_amount' => $newTotalAmount,
        ]);

        Log::info('Booking room type changed:', [
            'booking_id' => $booking->id,
            'booking_reference' => $booking->booking_reference,
            'previous_room_type' => $oldRoomType?->name,
            'new_room_type' => $newRoomType->name,
            'previous_room_unit' => $oldRoomUnit?->room_number,
            'new_room_unit' => $newRoomUnitId ? $roomUnit->room_number : 'TBA',
            'previous_amount' => $booking->getOriginal('total_amount'),
            'new_amount' => $newTotalAmount,
        ]);

        $message = 'Room type changed to '.$newRoomType->name;
        if ($newRoomUnitId) {
            $message .= ' (Room '.$roomUnit->room_number.')';
        }
        if ($newTotalAmount != $booking->getOriginal('total_amount')) {
            $message .= '. Price updated to ₦'.number_format($newTotalAmount, 2);
        }

        return back()->with('success', $message.'.');
    }
}
