<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use App\Models\RoomType;
use App\Models\RoomUnit;
use App\Services\PropertyService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Modules\Frontdeskcrm\Models\BookingSource;
use Modules\Frontdeskcrm\Models\Guest;
use Modules\Frontdeskcrm\Models\GuestType;
use Modules\Frontdeskcrm\Models\NightAudit;
use Modules\Frontdeskcrm\Models\Registration;
use Modules\Frontdeskcrm\Models\RegistrationPayment;

class ReportController extends Controller
{
    private function getDateRange(Request $request)
    {
        $today = Carbon::today();
        $from = $request->date_from ? Carbon::parse($request->date_from) : $today->copy()->startOfMonth();
        $to = $request->date_to ? Carbon::parse($request->date_to) : $today->copy()->endOfMonth();

        return [$from, $to];
    }

    public function index(Request $request)
    {
        $today = Carbon::today();
        $propertyId = app(PropertyService::class)->id();

        $arrivalsToday = Registration::whereDate('check_in', $today)
            ->whereIn('stay_status', ['reserved', 'checked_in'])->count();

        $departuresToday = Registration::whereDate('check_out', $today)
            ->where('stay_status', 'checked_in')->count();

        $inHouse = Registration::where('stay_status', 'checked_in')->count();
        $totalRooms = RoomUnit::count();
        $occupancyPercent = $totalRooms > 0 ? round(($inHouse / $totalRooms) * 100, 1) : 0;

        [$from, $to] = $this->getDateRange($request);

        $monthRevenue = RegistrationPayment::whereHas('registration', function ($q) use ($propertyId) {
            $q->when($propertyId, fn ($q) => $q->where('property_id', $propertyId));
        })->whereBetween('payment_date', [$from, $to])->sum('amount');
        $roomRevenue = Registration::whereIn('stay_status', ['checked_in', 'checked_out'])
            ->whereBetween('check_in', [$from, $to])
            ->sum(DB::raw('room_rate * no_of_nights'));

        $totalNightsSold = Registration::whereIn('stay_status', ['checked_in', 'checked_out'])
            ->whereBetween('check_in', [$from, $to])
            ->sum('no_of_nights');

        $adr = $totalNightsSold > 0 ? round($roomRevenue / $totalNightsSold, 2) : 0;
        $revpar = $totalRooms > 0 ? round($roomRevenue / ($totalRooms * max($from->diffInDays($to), 1)), 2) : 0;

        $recentAudits = NightAudit::completed()->latest('audit_date')->take(7)->get();

        $currentMonthLabel = $from->format('M Y');

        return view('frontdeskcrm::reports.index', compact(
            'arrivalsToday', 'departuresToday', 'inHouse', 'totalRooms',
            'occupancyPercent', 'monthRevenue', 'roomRevenue', 'adr', 'revpar',
            'recentAudits', 'currentMonthLabel', 'from', 'to'
        ));
    }

    public function occupancy(Request $request)
    {
        [$from, $to] = $this->getDateRange($request);
        $totalRooms = RoomUnit::count();

        $daily = collect();
        $period = $from->copy()->startOfDay();
        while ($period->lte($to)) {
            $occupied = Registration::where('stay_status', 'checked_in')
                ->whereDate('check_in', '<=', $period)
                ->where(function ($q) use ($period) {
                    $q->whereDate('check_out', '>', $period)
                        ->orWhereNull('check_out');
                })->count();

            $arrivals = Registration::whereDate('check_in', $period)
                ->whereIn('stay_status', ['reserved', 'checked_in'])->count();

            $departures = Registration::whereDate('check_out', $period)
                ->where('stay_status', 'checked_in')->count();

            $daily->push([
                'date' => $period->copy(),
                'label' => $period->format('M d'),
                'occupied' => $occupied,
                'arrivals' => $arrivals,
                'departures' => $departures,
                'available' => $totalRooms - $occupied,
                'occupancy_pct' => $totalRooms > 0 ? round(($occupied / $totalRooms) * 100, 1) : 0,
            ]);

            $period->addDay();
        }

        $byRoomType = RoomType::withCount(['registrations as checked_in_count' => function ($q) {
            $q->where('stay_status', 'checked_in');
        }])->get()->map(function ($rt) {
            $total = RoomUnit::where('room_type_id', $rt->id)->count();
            $rt->total_rooms = $total;

            return $rt;
        });

        $avgOccupancy = $daily->avg('occupancy_pct');

        return view('frontdeskcrm::reports.occupancy', compact(
            'daily', 'byRoomType', 'totalRooms', 'avgOccupancy', 'from', 'to'
        ));
    }

    public function revenue(Request $request)
    {
        [$from, $to] = $this->getDateRange($request);
        $propertyId = app(PropertyService::class)->id();

        $daily = collect();
        $period = $from->copy();
        while ($period->lte($to)) {

            $roomRev = Registration::whereIn('stay_status', ['checked_in', 'checked_out'])
                ->whereDate('check_in', '<=', $period)
                ->whereDate('check_out', '>=', $period)
                ->sum(DB::raw('room_rate'));

            $extraRev = DB::table('folio_charges')
                ->join('registrations', 'folio_charges.registration_id', '=', 'registrations.id')
                ->whereDate('folio_charges.created_at', $period)
                ->whereIn('registrations.stay_status', ['checked_in', 'checked_out'])
                ->when($propertyId, fn ($q) => $q->where('registrations.property_id', $propertyId))
                ->sum('folio_charges.amount');

            $payments = RegistrationPayment::whereHas('registration', function ($q) use ($propertyId) {
                $q->when($propertyId, fn ($q) => $q->where('property_id', $propertyId));
            })->whereDate('payment_date', $period)->sum('amount');

            $daily->push([
                'label' => $period->format('M d'),
                'room_revenue' => $roomRev,
                'extra_revenue' => $extraRev,
                'total' => $roomRev + $extraRev,
                'payments' => $payments,
            ]);

            $period->addDay();
        }

        $roomRevenueTotal = $daily->sum('room_revenue');
        $extraRevenueTotal = $daily->sum('extra_revenue');
        $grandTotal = $roomRevenueTotal + $extraRevenueTotal;
        $paymentsTotal = $daily->sum('payments');
        $taxEstimate = round($grandTotal * app(PropertyService::class)->taxRate() / 100, 2);

        $bySource = BookingSource::withCount(['registrations as revenue' => function ($q) {
            $q->select(DB::raw('COALESCE(SUM(room_rate * no_of_nights), 0)'));
        }])->get()->map(function ($s) {
            $s->revenue = $s->revenue ?? 0;

            return $s;
        });

        $byPaymentMethod = RegistrationPayment::whereHas('registration', function ($q) use ($propertyId) {
            $q->when($propertyId, fn ($q) => $q->where('property_id', $propertyId));
        })->whereBetween('payment_date', [$from, $to])
            ->select('payment_method', DB::raw('SUM(amount) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('payment_method')->get();

        $byRoomType = RoomType::withCount(['registrations as nights_sold' => function ($q) use ($from, $to) {
            $q->whereIn('stay_status', ['checked_in', 'checked_out'])
                ->whereBetween('check_in', [$from, $to])
                ->select(DB::raw('COALESCE(SUM(no_of_nights), 0)'));
        }])->get()->map(function ($rt) {
            $rt->nights_sold = $rt->nights_sold ?? 0;

            return $rt;
        });

        return view('frontdeskcrm::reports.revenue', compact(
            'daily', 'roomRevenueTotal', 'extraRevenueTotal', 'grandTotal',
            'paymentsTotal', 'taxEstimate', 'bySource', 'byPaymentMethod',
            'byRoomType', 'from', 'to'
        ));
    }

    public function forecast(Request $request)
    {
        $from = Carbon::today();
        $to = $request->date_to ? Carbon::parse($request->date_to) : $from->copy()->addDays(30);
        $totalRooms = RoomUnit::count();

        $daily = collect();
        $period = $from->copy();
        while ($period->lte($to)) {
            $arrivals = Registration::whereDate('check_in', $period)
                ->whereIn('stay_status', ['reserved', 'checked_in', 'checked_out'])
                ->count();

            $departuresScheduled = Registration::whereDate('check_out', $period)
                ->whereIn('stay_status', ['checked_in', 'reserved'])
                ->count();

            $inHouse = Registration::where('stay_status', 'checked_in')
                ->whereDate('check_in', '<=', $period)
                ->where(function ($q) use ($period) {
                    $q->whereDate('check_out', '>', $period)
                        ->orWhereNull('check_out');
                })->count();

            $reserved = Registration::where('stay_status', 'reserved')
                ->whereDate('check_in', '<=', $period)
                ->whereDate('check_out', '>', $period)
                ->count();

            $projectedOccupancy = $inHouse + $arrivals - $departuresScheduled;
            if ($projectedOccupancy < 0) {
                $projectedOccupancy = 0;
            }

            $daily->push([
                'label' => $period->format('D, M d'),
                'date' => $period->copy(),
                'arrivals' => $arrivals,
                'departures' => $departuresScheduled,
                'in_house' => $inHouse,
                'reserved' => $reserved,
                'projected' => $projectedOccupancy,
                'available' => $totalRooms - $projectedOccupancy,
                'occupancy_pct' => $totalRooms > 0 ? round(($projectedOccupancy / $totalRooms) * 100, 1) : 0,
            ]);

            $period->addDay();
        }

        $totalArrivals = $daily->sum('arrivals');
        $totalDepartures = $daily->sum('departures');
        $avgProjectedOccupancy = $daily->avg('occupancy_pct');

        return view('frontdeskcrm::reports.forecast', compact(
            'daily', 'totalArrivals', 'totalDepartures',
            'avgProjectedOccupancy', 'totalRooms', 'from', 'to'
        ));
    }

    public function sources(Request $request)
    {
        [$from, $to] = $this->getDateRange($request);
        $propertyId = app(PropertyService::class)->id();

        $bySource = BookingSource::withCount(['registrations as total_bookings' => function ($q) use ($from, $to) {
            $q->whereBetween('check_in', [$from, $to]);
        }])->get()->map(function ($source) use ($from, $to) {
            $registrations = Registration::where('booking_source_id', $source->id)
                ->whereBetween('check_in', [$from, $to])->get();

            $revenue = $registrations->sum(function ($r) {
                return ($r->room_rate ?? 0) * ($r->no_of_nights ?? 0);
            });

            $checkedIn = $registrations->where('stay_status', 'checked_in')->count();
            $checkedOut = $registrations->where('stay_status', 'checked_out')->count();
            $commission = $revenue * ($source->commission_rate / 100);
            $netRevenue = $revenue - $commission;

            $source->revenue = $revenue;
            $source->checked_in = $checkedIn;
            $source->checked_out = $checkedOut;
            $source->commission = $commission;
            $source->net_revenue = $netRevenue;

            return $source;
        });

        $byChannel = DB::table('channels')
            ->where('channels.property_id', $propertyId)
            ->leftJoin('channel_room_mappings', 'channels.id', '=', 'channel_room_mappings.channel_id')
            ->select('channels.id', 'channels.name', 'channels.provider', 'channels.is_active',
                DB::raw('COUNT(DISTINCT channel_room_mappings.room_unit_id) as mapped_rooms'))
            ->groupBy('channels.id', 'channels.name', 'channels.provider', 'channels.is_active')
            ->get();

        $grandTotalRevenue = $bySource->sum('revenue');
        $grandTotalCommission = $bySource->sum('commission');
        $grandNetRevenue = $grandTotalRevenue - $grandTotalCommission;

        return view('frontdeskcrm::reports.sources', compact(
            'bySource', 'byChannel', 'grandTotalRevenue', 'grandTotalCommission',
            'grandNetRevenue', 'from', 'to'
        ));
    }

    public function demographics(Request $request)
    {
        [$from, $to] = $this->getDateRange($request);
        $propertyId = app(PropertyService::class)->id();

        $byGuestType = GuestType::withCount(['registrations as total' => function ($q) use ($from, $to) {
            $q->whereBetween('check_in', [$from, $to]);
        }])->get()->map(function ($gt) use ($from, $to) {
            $revenue = Registration::where('guest_type_id', $gt->id)
                ->whereBetween('check_in', [$from, $to])
                ->select(DB::raw('COALESCE(SUM(room_rate * no_of_nights), 0) as rev'))
                ->value('rev');
            $gt->revenue = $revenue;

            return $gt;
        });

        $byNationality = Registration::whereBetween('check_in', [$from, $to])
            ->whereNotNull('nationality')
            ->select('nationality', DB::raw('COUNT(*) as total'), DB::raw('COALESCE(SUM(room_rate * no_of_nights), 0) as revenue'))
            ->groupBy('nationality')
            ->orderByDesc('total')
            ->take(15)
            ->get();

        $genderStats = Registration::whereBetween('check_in', [$from, $to])
            ->whereIn('stay_status', ['checked_in', 'checked_out'])
            ->select('gender', DB::raw('COUNT(*) as total'))
            ->groupBy('gender')
            ->get();

        $repeatVisitors = Guest::has('registrations', '>=', 2)
            ->whereHas('registrations', function ($q) use ($from, $to) {
                $q->whereBetween('check_in', [$from, $to]);
            })->count();

        $firstTimeVisitors = Guest::has('registrations', '=', 1)
            ->whereHas('registrations', function ($q) use ($from, $to) {
                $q->whereBetween('check_in', [$from, $to]);
            })->count();

        $byAge = collect();
        $now = Carbon::now();
        $ranges = [
            '18-25' => [18, 25],
            '26-35' => [26, 35],
            '36-45' => [36, 45],
            '46-55' => [46, 55],
            '56+' => [56, 200],
        ];
        foreach ($ranges as $label => [$min, $max]) {
            $count = Guest::whereHas('registrations', function ($q) use ($from, $to) {
                $q->whereBetween('check_in', [$from, $to]);
            })->whereNotNull('birthday')
                ->whereRaw("TIMESTAMPDIFF(YEAR, birthday, '{$now->toDateString()}') BETWEEN {$min} AND {$max}")
                ->count();
            $byAge->push(['label' => $label, 'count' => $count]);
        }

        $byGender = [
            'male' => $genderStats->where('gender', 'male')->first()?->total ?? 0,
            'female' => $genderStats->where('gender', 'female')->first()?->total ?? 0,
            'other' => $genderStats->where('gender', 'other')->first()?->total ?? 0,
            'unspecified' => $genderStats->whereNull('gender')->first()?->total ?? 0,
        ];

        $topCompanies = Registration::whereBetween('check_in', [$from, $to])
            ->whereNotNull('company_name')
            ->where('company_name', '!=', '')
            ->select('company_name', DB::raw('COUNT(*) as total'), DB::raw('COALESCE(SUM(room_rate * no_of_nights), 0) as revenue'))
            ->groupBy('company_name')
            ->orderByDesc('total')
            ->take(10)
            ->get();

        $totalGuests = Guest::whereHas('registrations', function ($q) use ($from, $to) {
            $q->whereBetween('check_in', [$from, $to]);
        })->count();

        return view('frontdeskcrm::reports.demographics', compact(
            'byGuestType', 'byNationality', 'byGender', 'byAge',
            'repeatVisitors', 'firstTimeVisitors', 'topCompanies',
            'totalGuests', 'from', 'to'
        ));
    }
}
