<?php

namespace Modules\Website\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Website\Models\FacilitiesPage;
use Modules\Website\Models\FacilityItem;

class FacilitiesPageController extends Controller
{
    public function edit()
    {
        $page = FacilitiesPage::firstOrCreate(
            ['id' => 1],
            [
                'hero_title' => 'Our Facilities',
                'hero_subtitle' => 'Experience Luxury & Comfort',
                'intro_heading' => 'Amenities & Services',
                'intro_description' => 'Discover a wide range of facilities designed to make your stay unforgettable.',
            ]
        );

        return view('website::admin.facilities.edit', compact('page'));
    }

    public function updateHero(Request $request)
    {
        $page = FacilitiesPage::firstOrCreate(['id' => 1]);

        $data = $request->validate([
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:255',
            'intro_heading' => 'nullable|string|max:255',
            'intro_description' => 'nullable|string',
        ]);

        if ($request->hasFile('hero_image')) {
            if ($page->hero_image) {
                $oldPath = str_replace('/storage/', '', $page->hero_image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('hero_image')->store('facilities', 'public');
            $data['hero_image'] = Storage::url($path);
        } else {
            unset($data['hero_image']);
        }

        $page->update($data);

        return redirect()->route('website.admin.facilities.edit')
            ->with('success', 'Facilities page updated successfully.');
    }

    public function storeItem(Request $request)
    {
        $page = FacilitiesPage::firstOrCreate(['id' => 1]);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'link_text' => 'nullable|string|max:100',
            'link_url' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['facilities_page_id'] = $page->id;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('facilities/items', 'public');
            $data['image'] = Storage::url($path);
        }

        FacilityItem::create($data);

        return redirect()->route('website.admin.facilities.edit')
            ->with('success', 'Facility item added successfully.');
    }

    public function updateItem(Request $request, FacilityItem $item)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|regex:/^[a-z0-9\-]+$/|unique:facility_items,slug,'.$item->id,
            'description' => 'nullable|string',
            'content' => 'nullable|string',
            'features' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'link_text' => 'nullable|string|max:100',
            'link_url' => 'nullable|string|max:255',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($item->image) {
                $oldPath = str_replace('/storage/', '', $item->image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('facilities/items', 'public');
            $data['image'] = Storage::url($path);
        }

        // Convert features textarea to array
        if (isset($data['features']) && $data['features']) {
            $data['features'] = array_filter(array_map('trim', explode("\n", $data['features'])));
        } else {
            $data['features'] = null;
        }

        $data['is_active'] = $request->boolean('is_active');

        $item->update($data);

        return redirect()->route('website.admin.facilities.edit')
            ->with('success', 'Facility item updated successfully.');
    }

    public function destroyItem(FacilityItem $item)
    {
        if ($item->image) {
            $oldPath = str_replace('/storage/', '', $item->image);
            Storage::disk('public')->delete($oldPath);
        }

        $item->delete();

        return redirect()->route('website.admin.facilities.edit')
            ->with('success', 'Facility item removed.');
    }
}
