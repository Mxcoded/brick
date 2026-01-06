<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Website\Models\Room;
use Modules\Website\Models\Booking;
use Modules\Website\Models\ContactMessage;
use Modules\Website\Models\Amenity;

class WebsiteAdminController extends Controller
{
    /**
     * Display the admin dashboard.
     *
     * @return \Illuminate\View\View
     */
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

        // 2. Room Status
        $rooms = [
            'total' => Room::count(),
            'available' => Room::where('status', 'available')->count(),
            'maintenance' => Room::where('status', 'maintenance')->count(),
        ];

        // 3. Recent Activity
        $recentBookings = Booking::with('room')->latest()->take(5)->get();
        $recentMessages = ContactMessage::where('status', false)->latest()->take(5)->get();

        return view('website::admin.dashboard', compact('stats', 'rooms', 'recentBookings', 'recentMessages'));
    }
}
