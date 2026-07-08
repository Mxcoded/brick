<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\RoomUnit;
use Carbon\Carbon;
use Modules\Website\Models\Booking;
use Modules\Website\Models\ContactMessage;

class WebsiteAdminController extends Controller
{
    /**
     * Display the Website Admin Dashboard.
     */
    public function index()
    {
        $today = Carbon::today();

        // 1. Booking Stats
        $stats = [
            'total_bookings' => Booking::count(),
            'pending_bookings' => Booking::where('status', 'pending')->count(),
            'confirmed_bookings' => Booking::where('status', 'confirmed')->count(),
            'checked_in_bookings' => Booking::where('status', 'checked_in')->count(),
            'completed_bookings' => Booking::where('status', 'completed')->count(),
            'cancelled_bookings' => Booking::where('status', 'cancelled')->count(),
            'revenue' => Booking::where('payment_status', 'paid')->sum('total_amount'),
            'today_arrivals' => Booking::whereDate('check_in_date', $today)
                ->whereIn('status', ['confirmed', 'pending'])
                ->count(),
            'today_departures' => Booking::whereDate('check_out_date', $today)
                ->whereIn('status', ['confirmed', 'checked_in'])
                ->count(),
            'unread_messages' => ContactMessage::unread()->count(),
        ];

        // 2. Room Status
        $totalUnits = RoomUnit::count();
        $maintenanceUnits = RoomUnit::where('status', 'maintenance')->count();

        $bookedUnitIds = Booking::whereIn('status', ['confirmed', 'pending', 'checked_in'])
            ->whereDate('check_in_date', '<=', $today)
            ->whereDate('check_out_date', '>', $today)
            ->whereNotNull('room_unit_id')
            ->pluck('room_unit_id')
            ->toArray();

        $unassignedBookings = Booking::whereIn('status', ['confirmed', 'pending', 'checked_in'])
            ->whereDate('check_in_date', '<=', $today)
            ->whereDate('check_out_date', '>', $today)
            ->whereNull('room_unit_id')
            ->count();

        $availableUnits = RoomUnit::where('status', 'available')
            ->whereNotIn('id', $bookedUnitIds)
            ->count() - $unassignedBookings;

        $occupied = $totalUnits - max(0, $availableUnits) - $maintenanceUnits;

        $rooms = [
            'total' => $totalUnits,
            'available' => max(0, $availableUnits),
            'maintenance' => $maintenanceUnits,
            'occupied' => max(0, $occupied),
            'occupancy_pct' => $totalUnits > 0
                ? round((max(0, $occupied) / $totalUnits) * 100)
                : 0,
        ];

        // 3. Recent Activity
        $recentBookings = Booking::with(['roomType', 'roomUnit'])->latest()->take(5)->get();
        $recentMessages = ContactMessage::unread()->latest()->take(5)->get();

        return view('website::admin.dashboard', compact('stats', 'rooms', 'recentBookings', 'recentMessages'));
    }
}
