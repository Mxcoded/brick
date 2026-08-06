<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Modules\Website\Models\Addon;

class AddonController extends Controller
{
    public function index()
    {
        $addons = Addon::ordered()->withCount('bookings')->get();

        return view('website::admin.addons.index', compact('addons'));
    }

    public function create()
    {
        return view('website::admin.addons.create');
    }

    public function store(Request $request)
    {
        Log::info('Addon store request:', $request->all());
        $validated = $request->validate($this->rules());

        $addon = Addon::create($validated);
        Log::info('Addon created:', $addon->toArray());

        return redirect()->route('website.admin.addons.index')->with('success', 'Add-on created successfully.');
    }

    public function show(Addon $addon)
    {
        $addon->loadCount('bookings');

        return view('website::admin.addons.show', compact('addon'));
    }

    public function edit(Addon $addon)
    {
        return view('website::admin.addons.edit', compact('addon'));
    }

    public function update(Request $request, Addon $addon)
    {
        Log::info('Addon update request:', $request->all());
        $validated = $request->validate($this->rules($addon));

        $addon->update($validated);
        Log::info('Addon updated:', $addon->toArray());

        return redirect()->route('website.admin.addons.index')->with('success', 'Add-on updated successfully.');
    }

    public function destroy(Addon $addon)
    {
        $addon->delete();

        return redirect()->route('website.admin.addons.index')->with('success', 'Add-on deleted successfully.');
    }

    private function rules(?Addon $addon = null): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['required', 'string', 'max:255', 'alpha_dash', Rule::unique('addons', 'slug')->ignore($addon?->id)],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'is_per_night' => ['sometimes', 'boolean'],
            'is_active' => ['sometimes', 'boolean'],
            'icon' => ['nullable', 'string', 'max:255'],
            'image_url' => ['nullable', 'string', 'max:255'],
            'sort_order' => ['nullable', 'integer', 'min:0', 'max:9999'],
        ];
    }
}
