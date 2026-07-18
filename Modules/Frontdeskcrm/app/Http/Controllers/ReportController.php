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

        // Arrivals grouped by check_in date
        $arrivalsByDate = Registration::whereDate('check_in', '>=', $from)
            ->whereDate('check_in', '<=', $to)
            ->whereIn('stay_status', ['reserved', 'checked_in'])
            ->selectRaw('DATE(check_in) as day, COUNT(*) as arrivals')
            ->groupBy('day')
            ->pluck('arrivals', 'day');

        // Departures grouped by check_out date
        $departuresByDate = Registration::whereDate('check_out', '>=', $from)
            ->whereDate('check_out', '<=', $to)
            ->where('stay_status', 'checked_in')
            ->selectRaw('DATE(check_out) as day, COUNT(*) as departures')
            ->groupBy('day')
            ->pluck('departures', 'day');

        // Occupancy: count registrations that were checked_in on each day of the range
        // A registration is "occupied" on a day D if check_in <= D AND (check_out > D OR check_out IS NULL)
        $occupancyByDate = Registration::where('stay_status', 'checked_in')
            ->whereDate('check_in', '<=', $to)
            ->where(function ($q) {
                $q->whereDate('check_out', '>', $from)
                    ->orWhereNull('check_out');
            })
            ->selectRaw('
                CASE
                    WHEN DATE(check_in) < ? THEN ?
                    ELSE DATE(check_in)
                END as day,
                COUNT(*) as occupied
            ', [$from->toDateString(), $from->toDateString()])
            ->groupBy('day')
            ->pluck('occupied', 'day');

        $daily = collect();
        $period = $from->copy()->startOfDay();
        $runningOccupied = 0;

        while ($period->lte($to)) {
            $dayKey = $period->toDateString();

            $arrivals = $arrivalsByDate->get($dayKey, 0);
            $departures = $departuresByDate->get($dayKey, 0);

            // For occupancy, we need to track running count
            // On days where we have direct occupancy data, use it
            // Otherwise compute from arrivals/departures
            if (isset($occupancyByDate[$dayKey])) {
                $occupied = $occupancyByDate[$dayKey];
                $runningOccupied = $occupied;
            } else {
                $runningOccupied = $runningOccupied + $arrivals - $departures;
                $occupied = max(0, $runningOccupied);
            }

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

        // Room revenue grouped by date
        $roomRevByDate = Registration::whereIn('stay_status', ['checked_in', 'checked_out'])
            ->whereDate('check_in', '>=', $from)
            ->whereDate('check_in', '<=', $to)
            ->selectRaw('DATE(check_in) as day, SUM(room_rate) as room_revenue')
            ->groupBy('day')
            ->pluck('room_revenue', 'day');

        // Extra charges (folio_charges) grouped by date
        $extraRevByDate = DB::table('folio_charges')
            ->join('registrations', 'folio_charges.registration_id', '=', 'registrations.id')
            ->whereDate('folio_charges.created_at', '>=', $from)
            ->whereDate('folio_charges.created_at', '<=', $to)
            ->whereIn('registrations.stay_status', ['checked_in', 'checked_out'])
            ->when($propertyId, fn ($q) => $q->where('registrations.property_id', $propertyId))
            ->selectRaw('DATE(folio_charges.created_at) as day, SUM(folio_charges.amount) as extra_revenue')
            ->groupBy('day')
            ->pluck('extra_revenue', 'day');

        // Payments grouped by date
        $paymentsByDate = RegistrationPayment::whereHas('registration', function ($q) use ($propertyId) {
            $q->when($propertyId, fn ($q) => $q->where('property_id', $propertyId));
        })
            ->whereDate('payment_date', '>=', $from)
            ->whereDate('payment_date', '<=', $to)
            ->selectRaw('DATE(payment_date) as day, SUM(amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $daily = collect();
        $period = $from->copy();
        while ($period->lte($to)) {
            $dayKey = $period->toDateString();
            $roomRev = (float) ($roomRevByDate->get($dayKey, 0));
            $extraRev = (float) ($extraRevByDate->get($dayKey, 0));

            $daily->push([
                'label' => $period->format('M d'),
                'room_revenue' => $roomRev,
                'extra_revenue' => $extraRev,
                'total' => $roomRev + $extraRev,
                'payments' => (float) ($paymentsByDate->get($dayKey, 0)),
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

        // Arrivals grouped by check_in date
        $arrivalsByDate = Registration::whereDate('check_in', '>=', $from)
            ->whereDate('check_in', '<=', $to)
            ->whereIn('stay_status', ['reserved', 'checked_in', 'checked_out'])
            ->selectRaw('DATE(check_in) as day, COUNT(*) as arrivals')
            ->groupBy('day')
            ->pluck('arrivals', 'day');

        // Departures grouped by check_out date
        $departuresByDate = Registration::whereDate('check_out', '>=', $from)
            ->whereDate('check_out', '<=', $to)
            ->whereIn('stay_status', ['checked_in', 'reserved'])
            ->selectRaw('DATE(check_out) as day, COUNT(*) as departures')
            ->groupBy('day')
            ->pluck('departures', 'day');

        // In-house on start date
        $inHouseStart = Registration::where('stay_status', 'checked_in')
            ->whereDate('check_in', '<', $from)
            ->where(function ($q) use ($from) {
                $q->whereDate('check_out', '>', $from)
                    ->orWhereNull('check_out');
            })->count();

        $reservedStart = Registration::where('stay_status', 'reserved')
            ->whereDate('check_in', '<=', $from)
            ->whereDate('check_out', '>', $from)
            ->count();

        $daily = collect();
        $period = $from->copy();
        $runningInHouse = $inHouseStart;
        $runningReserved = $reservedStart;

        while ($period->lte($to)) {
            $dayKey = $period->toDateString();
            $arrivals = $arrivalsByDate->get($dayKey, 0);
            $departuresScheduled = $departuresByDate->get($dayKey, 0);

            $inHouse = $runningInHouse;
            $reserved = $runningReserved;

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

            $runningInHouse = $inHouse + $arrivals - $departuresScheduled;
            if ($runningInHouse < 0) {
                $runningInHouse = 0;
            }
            $runningReserved = max(0, $reserved);

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

        // Single grouped query for all source stats
        $sourceStats = Registration::whereBetween('check_in', [$from, $to])
            ->selectRaw('
                booking_source_id,
                COUNT(*) as total_bookings,
                COALESCE(SUM(room_rate * no_of_nights), 0) as revenue,
                SUM(CASE WHEN stay_status = ? THEN 1 ELSE 0 END) as checked_in,
                SUM(CASE WHEN stay_status = ? THEN 1 ELSE 0 END) as checked_out
            ', ['checked_in', 'checked_out'])
            ->groupBy('booking_source_id')
            ->pluck(null, 'booking_source_id');

        $bySource = BookingSource::all()->map(function ($source) use ($sourceStats) {
            $stats = $sourceStats->get($source->id);
            $revenue = $stats->revenue ?? 0;
            $commission = $revenue * ($source->commission_rate / 100);
            $netRevenue = $revenue - $commission;

            $source->total_bookings = $stats->total_bookings ?? 0;
            $source->revenue = $revenue;
            $source->checked_in = $stats->checked_in ?? 0;
            $source->checked_out = $stats->checked_out ?? 0;
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
        $ageDistribution = Guest::whereHas('registrations', function ($q) use ($from, $to) {
            $q->whereBetween('check_in', [$from, $to]);
        })->whereNotNull('birthday')
            ->selectRaw('
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) BETWEEN 18 AND 25 THEN 1 ELSE 0 END) as `18-25`,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) BETWEEN 26 AND 35 THEN 1 ELSE 0 END) as `26-35`,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) BETWEEN 36 AND 45 THEN 1 ELSE 0 END) as `36-45`,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) BETWEEN 46 AND 55 THEN 1 ELSE 0 END) as `46-55`,
                SUM(CASE WHEN TIMESTAMPDIFF(YEAR, birthday, CURDATE()) >= 56 THEN 1 ELSE 0 END) as `56+`
            ')
            ->first();

        $ranges = ['18-25', '26-35', '36-45', '46-55', '56+'];
        foreach ($ranges as $label) {
            $byAge->push(['label' => $label, 'count' => (int) ($ageDistribution->{$label} ?? 0)]);
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
