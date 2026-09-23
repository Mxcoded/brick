<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Modules\Website\Models\Testimonial;

class TestimonialController extends Controller
{
    public function index(Request $request)
    {
        $type = $request->get('type', 'all');
        $location = $request->get('location');

        $query = Testimonial::latest();

        if (in_array($type, Testimonial::TYPES)) {
            $query->where('type', $type);
        }

        if ($location) {
            $query->where('location', $location);
        }

        $testimonials = $query->get();

        $locations = Testimonial::whereNotNull('location')
            ->where('location', '!=', '')
            ->distinct()
            ->orderBy('location')
            ->pluck('location');

        return view('website::admin.testimonials.index', compact('testimonials', 'type', 'location', 'locations'));
    }

    public function create()
    {
        return view('website::admin.testimonials.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'text' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'guest_image' => 'nullable|string|max:255',
            'stay_type' => 'nullable|string|max:255',
            'type' => 'required|in:'.implode(',', Testimonial::TYPES),
            'dining_venue' => 'nullable|string|max:255',
            'event_name' => 'nullable|string|max:255',
            'cleanliness' => 'nullable|integer|min:1|max:5',
            'wifi_rating' => 'nullable|integer|min:1|max:5',
            'staff_rating' => 'nullable|integer|min:1|max:5',
            'food_rating' => 'nullable|integer|min:1|max:5',
            'maintenance_rating' => 'nullable|integer|min:1|max:5',
            'location' => 'nullable|string|max:255',
            'approved' => 'nullable|boolean',
        ]);

        $validated['approved'] = $request->boolean('approved');

        Testimonial::create($validated);

        return redirect()->route('website.admin.testimonials.index')
            ->with('success', 'Testimonial created successfully.');
    }

    public function show(Testimonial $testimonial)
    {
        return view('website::admin.testimonials.show', compact('testimonial'));
    }

    public function wifiQr(Request $request)
    {
        $lastWithWifi = Testimonial::whereNotNull('ssid')->latest('id')->first();

        $defaults = [
            'site' => 'Brickspoint Asokoro',
            'ssid' => 'Brickspoint-Guest',
            'ap' => 'Lobby Guest Wi-Fi',
            'band' => '5 GHz',
            'apMac' => '',
        ];

        if ($lastWithWifi) {
            $defaults['site'] = $lastWithWifi->location ?: $defaults['site'];
            $defaults['ssid'] = $lastWithWifi->ssid ?: $defaults['ssid'];
            $defaults['ap'] = $lastWithWifi->ap_name ?: $defaults['ap'];
            $defaults['apMac'] = $lastWithWifi->ap_mac ?: $defaults['apMac'];

            $radioId = $lastWithWifi->wifi_meta['radio_id'] ?? null;
            if ($radioId !== null && $radioId !== '') {
                $defaults['band'] = $radioId == '1' ? '5 GHz' : '2.4 GHz';
            }
        }

        $values = [
            'site' => $request->input('site', $defaults['site']),
            'ssid' => $request->input('ssid', $defaults['ssid']),
            'ap' => $request->input('ap', $defaults['ap']),
            'band' => $request->input('band', $defaults['band']),
            'apMac' => $request->input('apmac', $defaults['apMac']),
        ];

        $urls = [];
        foreach (Testimonial::TYPES as $type) {
            $urls[$type] = route('website.guest-feedback', array_filter([
                'type' => $type,
                'source' => 'qrcode',
                'site' => $values['site'],
                'ssid' => $values['ssid'],
                'ap' => $values['ap'],
                'band' => $values['band'],
                'apmac' => $values['apMac'],
            ]));
        }

        return view('website::admin.testimonials.wifi-qr', compact('urls', 'values'));
    }

    public function edit(Testimonial $testimonial)
    {
        return view('website::admin.testimonials.edit', compact('testimonial'));
    }

    public function update(Request $request, Testimonial $testimonial)
    {
        $validated = $request->validate([
            'guest_name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'text' => 'required|string',
            'rating' => 'required|integer|min:1|max:5',
            'guest_image' => 'nullable|string|max:255',
            'stay_type' => 'nullable|string|max:255',
            'type' => 'required|in:'.implode(',', Testimonial::TYPES),
            'dining_venue' => 'nullable|string|max:255',
            'event_name' => 'nullable|string|max:255',
            'cleanliness' => 'nullable|integer|min:1|max:5',
            'wifi_rating' => 'nullable|integer|min:1|max:5',
            'staff_rating' => 'nullable|integer|min:1|max:5',
            'food_rating' => 'nullable|integer|min:1|max:5',
            'maintenance_rating' => 'nullable|integer|min:1|max:5',
            'location' => 'nullable|string|max:255',
            'approved' => 'nullable|boolean',
        ]);

        $validated['approved'] = $request->boolean('approved');

        $testimonial->update($validated);

        return redirect()->route('website.admin.testimonials.index')
            ->with('success', 'Testimonial updated successfully.');
    }

    public function toggleApprove(Testimonial $testimonial)
    {
        $testimonial->update(['approved' => ! $testimonial->approved]);

        return redirect()->route('website.admin.testimonials.index')
            ->with('success', $testimonial->approved
                ? 'Testimonial approved successfully.'
                : 'Testimonial unapproved.');
    }

    public function destroy(Testimonial $testimonial)
    {
        $testimonial->delete();

        return redirect()->route('website.admin.testimonials.index')
            ->with('success', 'Testimonial deleted successfully.');
    }
}
