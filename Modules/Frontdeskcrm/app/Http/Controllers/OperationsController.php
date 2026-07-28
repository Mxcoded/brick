<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Frontdeskcrm\Models\Registration;

class OperationsController extends Controller
{
    public function dashboard()
    {
        $today = today();

        $totalRooms = DB::table('room_units')->count();
        $availableRooms = DB::table('room_units')->where('status', 'available')->count();
        $occupiedRooms = DB::table('room_units')->where('status', 'occupied')->count();
        $cleanRooms = DB::table('room_units')->where('status', 'clean')->count();
        $dirtyRooms = DB::table('room_units')->where('status', 'dirty')->count();
        $outOfOrderRooms = DB::table('room_units')->where('status', 'out_of_order')->count();

        $inHouseCount = Registration::where('stay_status', 'checked_in')
            ->whereDate('check_in', '<=', $today)
            ->whereDate('check_out', '>', $today)
            ->count();

        $dueOutToday = Registration::with(['guest', 'roomUnit', 'folio'])
            ->whereDate('check_out', $today)
            ->where('stay_status', 'checked_in')
            ->orderBy('check_out')
            ->get();

        $arrivalsToday = Registration::with(['guest', 'roomType', 'bookingSource', 'booking'])
            ->whereDate('check_in', $today)
            ->whereIn('stay_status', ['reserved', 'checked_in'])
            ->orderBy('check_in')
            ->get();

        $checkedOutToday = Registration::with(['guest', 'roomUnit'])
            ->whereDate('actual_checkout_at', $today)
            ->where('stay_status', 'checked_out')
            ->orderBy('actual_checkout_at', 'desc')
            ->get();

        $occupancyRate = $totalRooms > 0 ? round(($inHouseCount / $totalRooms) * 100, 1) : 0;

        $pendingPayments = Registration::where('stay_status', 'checked_in')
            ->whereDate('check_out', '<=', $today)
            ->where(function ($query) {
                $query->whereRaw('total_amount > COALESCE((SELECT SUM(amount) FROM registration_payments WHERE registration_payments.registration_id = registrations.id), 0)');
            })
            ->count();

        return view('frontdeskcrm::operations.dashboard', compact(
            'today', 'totalRooms', 'availableRooms', 'occupiedRooms',
            'cleanRooms', 'dirtyRooms', 'outOfOrderRooms',
            'inHouseCount', 'dueOutToday', 'arrivalsToday',
            'checkedOutToday', 'occupancyRate', 'pendingPayments'
        ));
    }
}
