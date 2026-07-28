<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Frontdeskcrm\Models\Season;

class SeasonController extends Controller
{
    public function index()
    {
        $seasons = Season::orderBy('valid_from')->paginate(20);

        return view('frontdeskcrm::seasons.index', compact('seasons'));
    }

    public function create()
    {
        return view('frontdeskcrm::seasons.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:seasons,code',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after_or_equal:valid_from',
            'rate_multiplier' => 'required|numeric|min:0|max:999.9999',
            'is_active' => 'boolean',
        ]);

        Season::create($validated);

        return redirect()->route('frontdesk.seasons.index')
            ->with('success', 'Season created successfully.');
    }

    public function edit(Season $season)
    {
        return view('frontdeskcrm::seasons.edit', compact('season'));
    }

    public function update(Request $request, Season $season)
    {
        $validated = $request->validate([
            'code' => 'required|string|max:20|unique:seasons,code,'.$season->id,
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'valid_from' => 'required|date',
            'valid_to' => 'required|date|after_or_equal:valid_from',
            'rate_multiplier' => 'required|numeric|min:0|max:999.9999',
            'is_active' => 'boolean',
        ]);

        $season->update($validated);

        return redirect()->route('frontdesk.seasons.index')
            ->with('success', 'Season updated successfully.');
    }

    public function destroy(Season $season)
    {
        $season->delete();

        return redirect()->route('frontdesk.seasons.index')
            ->with('success', 'Season deleted successfully.');
    }
}
