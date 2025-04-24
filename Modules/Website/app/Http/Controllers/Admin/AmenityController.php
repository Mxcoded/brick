<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Website\Models\Amenity;
use Illuminate\Support\Facades\Log;

class AmenityController extends Controller
{
    public function index()
    {
        $amenities = Amenity::all();
        return view('website::admin.amenities.index', compact('amenities'));
    }

    public function create()
    {
        return view('website::admin.amenities.create');
    }

    public function store(Request $request)
    {
        Log::info('Amenity store request:', $request->all());
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:amenities',
            'icon' => 'nullable|string|max:255', // e.g., "fas fa-wifi"
        ]);

        $amenity = Amenity::create($validated);
        Log::info('Amenity created:', $amenity->toArray());

        return redirect()->route('website.admin.amenities.index')->with('success', 'Amenity created successfully.');
    }

    public function show(Amenity $amenity)
    {
        return view('website::admin.amenities.show', compact('amenity'));
    }

    public function edit(Amenity $amenity)
    {
        return view('website::admin.amenities.edit', compact('amenity'));
    }

    public function update(Request $request, Amenity $amenity)
    {
        Log::info('Amenity update request:', $request->all());
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:amenities,name,' . $amenity->id,
            'icon' => 'nullable|string|max:255',
        ]);

        $amenity->update($validated);
        Log::info('Amenity updated:', $amenity->toArray());

        return redirect()->route('website.admin.amenities.index')->with('success', 'Amenity updated successfully.');
    }

    public function destroy(Amenity $amenity)
    {
        $amenity->delete();
        return redirect()->route('website.admin.amenities.index')->with('success', 'Amenity deleted successfully.');
    }
}
