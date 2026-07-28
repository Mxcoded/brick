<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationCharge;
use Modules\Frontdeskcrm\Models\RegistrationPayment;

class ReportController extends Controller
{
    public function index()
    {
        return view('frontdeskcrm::reports.index');
    }

    public function dailyRevenue(Request $request)
    {
        $date = $request->has('date') ? Carbon::parse($request->date) : today();

        $roomRevenue = RegistrationCharge::whereDate('charge_date', $date)
            ->where('charge_type', 'room')
            ->selectRaw('SUM(amount) as total, COUNT(*) as count')
            ->first();

        $payments = RegistrationPayment::whereDate('payment_date', $date)
            ->selectRaw('payment_method, SUM(amount) as total, COUNT(*) as count')
            ->groupBy('payment_method')
            ->get();

        $breakfastRevenue = RegistrationCharge::whereDate('charge_date', $date)
            ->where('charge_type', 'breakfast')
            ->sum('amount');

        $extensionRevenue = RegistrationCharge::whereDate('charge_date', $date)
            ->where('charge_type', 'extension')
            ->sum('amount');

        $checkedIn = Registration::where('stay_status', 'checked_in')
            ->whereDate('check_in', '<=', $date)
            ->whereDate('check_out', '>', $date)
            ->count();

        $checkedOut = Registration::whereDate('actual_checkout_at', $date)
            ->where('stay_status', 'checked_out')
            ->count();

        $arrivals = Registration::whereDate('check_in', $date)
            ->whereIn('stay_status', ['checked_in', 'reserved'])
            ->count();

        $departures = Registration::whereDate('check_out', $date)
            ->where('stay_status', 'checked_in')
            ->count();

        $availableRooms = DB::table('room_units')
            ->where('status', 'available')
            ->count();

        $totalRooms = DB::table('room_units')->count();
        $occupancyRate = $totalRooms > 0 ? round(($checkedIn / $totalRooms) * 100, 1) : 0;
        $adr = $checkedIn > 0 ? round($roomRevenue->total / $checkedIn, 2) : 0;

        $revpar = $totalRooms > 0 ? round(($roomRevenue->total / $totalRooms), 2) : 0;

        $bySource = Registration::whereDate('check_in', $date)
            ->where('stay_status', 'checked_in')
            ->selectRaw('booking_source_id, COUNT(*) as count')
            ->groupBy('booking_source_id')
            ->with('bookingSource')
            ->get();

        return view('frontdeskcrm::reports.daily-revenue', compact(
            'date', 'roomRevenue', 'payments',
            'breakfastRevenue', 'extensionRevenue',
            'checkedIn', 'checkedOut', 'arrivals', 'departures',
            'availableRooms', 'totalRooms', 'occupancyRate', 'adr', 'revpar',
            'bySource'
        ));
    }

    public function arrivalsDepartures(Request $request)
    {
        $date = $request->has('date') ? Carbon::parse($request->date) : today();
        $days = $request->get('days', 7);

        $arrivals = Registration::with(['guest', 'bookingSource', 'roomType'])
            ->whereBetween('check_in', [$date, $date->copy()->addDays($days - 1)])
            ->whereIn('stay_status', ['reserved', 'checked_in'])
            ->orderBy('check_in')
            ->get();

        $departures = Registration::with(['guest', 'roomUnit', 'rateCode'])
            ->whereBetween('check_out', [$date, $date->copy()->addDays($days - 1)])
            ->where('stay_status', 'checked_in')
            ->orderBy('check_out')
            ->get();

        return view('frontdeskcrm::reports.arrivals-departures', compact(
            'date', 'days', 'arrivals', 'departures'
        ));
    }

    public function occupancy(Request $request)
    {
        $from = $request->has('from') ? Carbon::parse($request->from) : today()->startOfMonth();
        $to = $request->has('to') ? Carbon::parse($request->to) : today()->endOfMonth();

        $totalRooms = DB::table('room_units')->count();

        $dailyData = [];
        $current = $from->copy();
        while ($current->lte($to)) {
            $occupied = Registration::where('stay_status', 'checked_in')
                ->whereDate('check_in', '<=', $current)
                ->whereDate('check_out', '>', $current)
                ->count();

            $arrivals = Registration::whereDate('check_in', $current)
                ->whereIn('stay_status', ['checked_in', 'reserved'])
                ->count();

            $departures = Registration::whereDate('actual_checkout_at', $current)
                ->where('stay_status', 'checked_out')
                ->count();

            $dailyData[] = [
                'date' => $current->copy(),
                'occupied' => $occupied,
                'available' => $totalRooms - $occupied,
                'occupancy_rate' => $totalRooms > 0 ? round(($occupied / $totalRooms) * 100, 1) : 0,
                'arrivals' => $arrivals,
                'departures' => $departures,
            ];

            $current->addDay();
        }

        $byRoomType = Registration::where('stay_status', 'checked_in')
            ->selectRaw('room_type_id, COUNT(*) as count')
            ->groupBy('room_type_id')
            ->with('roomType')
            ->get();

        $avgOccupancy = count($dailyData) > 0
            ? round(array_sum(array_column($dailyData, 'occupancy_rate')) / count($dailyData), 1)
            : 0;

        return view('frontdeskcrm::reports.occupancy', compact(
            'from', 'to', 'dailyData', 'totalRooms', 'byRoomType', 'avgOccupancy'
        ));
    }
}
