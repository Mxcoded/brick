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
    public function dashboard()
    {
        $stats = [
            'total_rooms' => Room::count(),
            'active_bookings' => Booking::where('status', 'confirmed')->count(),
            'unread_messages' => ContactMessage::where('status', 'unread')->count(),
            'total_amenities' => Amenity::count(),
        ];

        return view('website::admin.dashboard', compact('stats'));
    }
}
