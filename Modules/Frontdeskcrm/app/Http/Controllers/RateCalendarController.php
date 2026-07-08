<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use App\Models\RoomType;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Modules\Frontdeskcrm\Models\RateCalendar;
use Modules\Frontdeskcrm\Models\RateCode;

class RateCalendarController extends Controller
{
    public function index(Request $request)
    {
        $rateCodes = RateCode::active()->ordered()->get();
        $roomTypes = RoomType::active()->ordered()->get();

        $month = $request->input('month', now()->format('Y-m'));
        $startDate = Carbon::parse($month.'-01')->startOfMonth();
        $endDate = $startDate->copy()->endOfMonth();
        $dates = [];

        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dates[] = $date->copy();
        }

        $calendarEntries = RateCalendar::whereIn('rate_code_id', $rateCodes->pluck('id'))
            ->whereIn('room_type_id', $roomTypes->pluck('id'))
            ->whereBetween('date', [$startDate, $endDate])
            ->get()
            ->groupBy(fn ($item) => $item->rate_code_id.'_'.$item->room_type_id.'_'.$item->date);

        $prevMonth = $startDate->copy()->subMonth()->format('Y-m');
        $nextMonth = $startDate->copy()->addMonth()->format('Y-m');

        return view('frontdeskcrm::rate-calendar.index', compact(
            'rateCodes', 'roomTypes', 'dates', 'startDate', 'endDate',
            'calendarEntries', 'month', 'prevMonth', 'nextMonth'
        ));
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'rate_code_id' => 'required|exists:rate_codes,id',
            'room_type_id' => 'required|exists:room_types,id',
            'date' => 'required|date',
            'price' => 'nullable|numeric|min:0|max:999999.99',
            'min_stay' => 'nullable|integer|min:0|max:99',
            'cta' => 'boolean',
            'ctd' => 'boolean',
            'stop_sell' => 'boolean',
        ]);

        RateCalendar::updateOrCreate(
            [
                'rate_code_id' => $validated['rate_code_id'],
                'room_type_id' => $validated['room_type_id'],
                'date' => $validated['date'],
            ],
            [
                'price' => $validated['price'] ?? null,
                'min_stay' => $validated['min_stay'] ?? null,
                'cta' => $validated['cta'] ?? false,
                'ctd' => $validated['ctd'] ?? false,
                'stop_sell' => $validated['stop_sell'] ?? false,
            ]
        );

        return back()->with('success', 'Rate calendar updated.');
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'rate_code_id' => 'required|exists:rate_codes,id',
            'room_type_id' => 'required|exists:room_types,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'price' => 'nullable|numeric|min:0|max:999999.99',
            'min_stay' => 'nullable|integer|min:0|max:99',
            'cta' => 'boolean',
            'ctd' => 'boolean',
            'stop_sell' => 'boolean',
        ]);

        $period = CarbonPeriod::create($validated['start_date'], $validated['end_date']);

        foreach ($period as $date) {
            RateCalendar::updateOrCreate(
                [
                    'rate_code_id' => $validated['rate_code_id'],
                    'room_type_id' => $validated['room_type_id'],
                    'date' => $date->format('Y-m-d'),
                ],
                [
                    'price' => $validated['price'] ?? null,
                    'min_stay' => $validated['min_stay'] ?? null,
                    'cta' => $validated['cta'] ?? false,
                    'ctd' => $validated['ctd'] ?? false,
                    'stop_sell' => $validated['stop_sell'] ?? false,
                ]
            );
        }

        return back()->with('success', 'Bulk update applied.');
    }

    public function getDay(Request $request)
    {
        $validated = $request->validate([
            'rate_code_id' => 'required|exists:rate_codes,id',
            'room_type_id' => 'required|exists:room_types,id',
            'date' => 'required|date',
        ]);

        $entry = RateCalendar::where([
            'rate_code_id' => $validated['rate_code_id'],
            'room_type_id' => $validated['room_type_id'],
            'date' => $validated['date'],
        ])->first();

        $rateCode = RateCode::find($validated['rate_code_id']);
        $basePrice = $rateCode?->prices()
            ->where('room_type_id', $validated['room_type_id'])
            ->first()?->price;

        return response()->json([
            'entry' => $entry,
            'base_price' => $basePrice,
        ]);
    }
}
