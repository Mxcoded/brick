<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Modules\Website\Models\Booking;
use Modules\Website\Models\ContactMessage;
use Modules\Website\Models\Room;
use Modules\Website\Models\RoomType;
use Modules\Website\Models\RoomUnit;

class WebsiteAdminController extends Controller
{
    /**
     * Display the Website Admin Dashboard.
     */
    public function index()
    {
        // 1. Booking Stats
        $stats = [
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'revenue' => Booking::where('payment_status', 'paid')->sum('total_amount'),
        ];

        // 2. Room Status - Using RoomType/RoomUnit architecture
        $today = Carbon::today();
        $totalUnits = RoomUnit::count();
        $maintenanceUnits = RoomUnit::where('status', 'maintenance')->count();

        // Calculate truly available units (not in maintenance AND not booked for today)
        $bookedUnitIds = Booking::whereIn('status', ['confirmed', 'pending', 'checked_in'])
            ->whereDate('check_in_date', '<=', $today)
            ->whereDate('check_out_date', '>', $today)
            ->whereNotNull('room_unit_id')
            ->pluck('room_unit_id')
            ->toArray();

        // Also count bookings without assigned units (they occupy capacity)
        $unassignedBookings = Booking::whereIn('status', ['confirmed', 'pending', 'checked_in'])
            ->whereDate('check_in_date', '<=', $today)
            ->whereDate('check_out_date', '>', $today)
            ->whereNull('room_unit_id')
            ->count();

        $availableUnits = RoomUnit::where('status', 'available')
            ->whereNotIn('id', $bookedUnitIds)
            ->count() - $unassignedBookings;

        $rooms = [
            'total' => $totalUnits,
            'available' => max(0, $availableUnits),
            'maintenance' => $maintenanceUnits,
            'occupied' => $totalUnits - max(0, $availableUnits) - $maintenanceUnits,
        ];

        // 3. Recent Activity
        $recentBookings = Booking::with(['roomType', 'roomUnit'])->latest()->take(5)->get();
        $recentMessages = ContactMessage::where('status', false)->latest()->take(5)->get();

        return view('website::admin.dashboard', compact('stats', 'rooms', 'recentBookings', 'recentMessages'));
    }
}
