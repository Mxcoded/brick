<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Frontdeskcrm\Models\RateCode;

class RateCodeController extends Controller
{
    public function index()
    {
        $rateCodes = RateCode::orderBy('sort_order')->orderBy('code')->paginate(20);

        return view('frontdeskcrm::rate-codes.index', compact('rateCodes'));
    }

    public function create()
    {
        return view('frontdeskcrm::rate-codes.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:rate_codes,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_rate' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'min_los' => 'required|integer|min:1',
            'max_los' => 'nullable|integer|min:1',
            'closed_to_arrival' => 'boolean',
            'closed_to_departure' => 'boolean',
            'apply_weekdays' => 'boolean',
            'apply_weekends' => 'boolean',
            'is_active' => 'boolean',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'sort_order' => 'integer|min:0',
        ]);

        RateCode::create($validated);

        return redirect()->route('frontdesk.rate-codes.index')
            ->with('success', 'Rate code created successfully.');
    }

    public function edit(RateCode $rateCode)
    {
        return view('frontdeskcrm::rate-codes.edit', compact('rateCode'));
    }

    public function update(Request $request, RateCode $rateCode)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:rate_codes,code,'.$rateCode->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'default_rate' => 'required|numeric|min:0',
            'currency' => 'required|string|size:3',
            'min_los' => 'required|integer|min:1',
            'max_los' => 'nullable|integer|min:1',
            'closed_to_arrival' => 'boolean',
            'closed_to_departure' => 'boolean',
            'apply_weekdays' => 'boolean',
            'apply_weekends' => 'boolean',
            'is_active' => 'boolean',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'sort_order' => 'integer|min:0',
        ]);

        $rateCode->update($validated);

        return redirect()->route('frontdesk.rate-codes.index')
            ->with('success', 'Rate code updated successfully.');
    }

    public function destroy(RateCode $rateCode)
    {
        $rateCode->delete();

        return redirect()->route('frontdesk.rate-codes.index')
            ->with('success', 'Rate code deleted successfully.');
    }

    public function calendar(RateCode $rateCode, Request $request)
    {
        $year = $request->get('year', now()->year);
        $month = $request->get('month', now()->month);

        $entries = $rateCode->calendar()
            ->whereYear('date', $year)
            ->whereMonth('date', $month)
            ->get()
            ->keyBy(fn ($e) => $e->date->format('Y-m-d'));

        return view('frontdeskcrm::rate-codes.calendar', compact('rateCode', 'entries', 'year', 'month'));
    }

    public function updateCalendar(Request $request, RateCode $rateCode)
    {
        $validated = $request->validate([
            'entries' => 'required|array',
            'entries.*.date' => 'required|date',
            'entries.*.rate' => 'nullable|numeric|min:0',
            'entries.*.is_available' => 'boolean',
            'entries.*.available_rooms' => 'nullable|integer|min:0',
        ]);

        foreach ($validated['entries'] as $entry) {
            $rateCode->calendar()->updateOrCreate(
                ['date' => $entry['date']],
                [
                    'rate' => $entry['rate'] ?? null,
                    'is_available' => $entry['is_available'] ?? true,
                    'available_rooms' => $entry['available_rooms'] ?? null,
                ]
            );
        }

        return redirect()->route('frontdesk.rate-codes.calendar', $rateCode)
            ->with('success', 'Rate calendar updated.');
    }
}
