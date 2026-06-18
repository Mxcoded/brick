<?php

namespace Modules\Website\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Modules\Website\Models\Offer;
use Modules\Website\Models\OffersPage;

class OffersPageController extends Controller
{
    public function edit()
    {
        $page = OffersPage::firstOrCreate(
            ['id' => 1],
            [
                'hero_title' => 'Exclusive Offers',
                'hero_subtitle' => 'Brickspoint ApartHotel',
                'intro_heading' => 'Special Packages & Deals',
                'intro_description' => 'Discover our latest offers and experience great savings on your stay.',
            ]
        );

        return view('website::admin.offers.edit', compact('page'));
    }

    public function updateHero(Request $request)
    {
        $page = OffersPage::firstOrCreate(['id' => 1]);

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
            $path = $request->file('hero_image')->store('offers', 'public');
            $data['hero_image'] = Storage::url($path);
        } else {
            unset($data['hero_image']);
        }

        $page->update($data);

        return redirect()->route('website.admin.offers.edit')
            ->with('success', 'Offers page updated successfully.');
    }

    public function storeOffer(Request $request)
    {
        $page = OffersPage::firstOrCreate(['id' => 1]);

        $data = $request->validate([
            'title' => 'required|string|max:255',
            'short_description' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $data['offers_page_id'] = $page->id;

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('offers/items', 'public');
            $data['image'] = Storage::url($path);
        }

        Offer::create($data);

        return redirect()->route('website.admin.offers.edit')
            ->with('success', 'Offer added successfully.');
    }

    public function updateOffer(Request $request, Offer $offer)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|regex:/^[a-z0-9\-]+$/|unique:offers,slug,'.$offer->id,
            'short_description' => 'nullable|string',
            'content' => 'nullable|string',
            'features' => 'nullable|string',
            'icon' => 'nullable|string|max:100',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
            'terms_conditions' => 'nullable|string',
            'sort_order' => 'nullable|integer|min:0',
            'is_active' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            if ($offer->image) {
                $oldPath = str_replace('/storage/', '', $offer->image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('image')->store('offers/items', 'public');
            $data['image'] = Storage::url($path);
        }

        if (isset($data['features']) && $data['features']) {
            $data['features'] = array_filter(array_map('trim', explode("\n", $data['features'])));
        } else {
            $data['features'] = null;
        }

        $data['is_active'] = $request->boolean('is_active');

        $offer->update($data);

        return redirect()->route('website.admin.offers.edit')
            ->with('success', 'Offer updated successfully.');
    }

    public function destroyOffer(Offer $offer)
    {
        if ($offer->image) {
            $oldPath = str_replace('/storage/', '', $offer->image);
            Storage::disk('public')->delete($oldPath);
        }

        $offer->delete();

        return redirect()->route('website.admin.offers.edit')
            ->with('success', 'Offer removed.');
    }
}
