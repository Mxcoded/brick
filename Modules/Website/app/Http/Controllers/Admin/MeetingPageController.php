<?php

namespace Modules\Website\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Modules\Website\Models\MeetingGallery;
use Modules\Website\Models\MeetingPage;
use Modules\Website\Models\MeetingRoom;

class MeetingPageController extends Controller
{
    public function edit()
    {
        $page = MeetingPage::firstOrCreate(
            ['id' => 1],
            [
                'hero_title' => 'Meetings & Events Space',
                'hero_subtitle' => 'Brickspoint ApartHotel',
                'hero_description' => 'Discover our versatile meeting and event spaces...',
                'is_published' => true,
            ]
        );

        $page->load(['rooms', 'gallery']);

        return view('website::admin.meeting.edit', compact('page'));
    }

    // ─── Hero Section ───────────────────────────────────

    public function updateHero(Request $request)
    {
        $page = MeetingPage::firstOrCreate(['id' => 1]);

        $validated = $request->validate([
            'hero_title' => 'nullable|string|max:255',
            'hero_subtitle' => 'nullable|string|max:255',
            'hero_description' => 'nullable|string',
            'hero_image' => 'nullable|image|max:8192',
            'stats_meeting_rooms' => 'nullable|integer|min:0',
            'stats_total_sqm' => 'nullable|integer|min:0',
            'stats_total_capacity' => 'nullable|integer|min:0',
            'brochure_pdf' => 'nullable|file|mimes:pdf|max:20480',
        ]);

        $data = $validated;

        $data['stats'] = [
            'meeting_rooms' => (int) $request->input('stats_meeting_rooms', 0),
            'total_sqm' => (int) $request->input('stats_total_sqm', 0),
            'total_capacity' => (int) $request->input('stats_total_capacity', 0),
        ];

        unset($data['stats_meeting_rooms'], $data['stats_total_sqm'], $data['stats_total_capacity']);

        if ($request->hasFile('hero_image')) {
            if ($page->hero_image) {
                $oldPath = str_replace('/storage/', '', $page->hero_image);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('hero_image')->store('meetings', 'public');
            $data['hero_image'] = Storage::url($path);
        } else {
            unset($data['hero_image']);
        }

        if ($request->hasFile('brochure_pdf')) {
            if ($page->brochure_pdf) {
                $oldPath = str_replace('/storage/', '', $page->brochure_pdf);
                Storage::disk('public')->delete($oldPath);
            }
            $path = $request->file('brochure_pdf')->store('meetings/brochures', 'public');
            $data['brochure_pdf'] = Storage::url($path);
        } else {
            unset($data['brochure_pdf']);
        }

        $page->update($data);

        return redirect()->route('website.admin.meeting.edit')
            ->with('success', 'Hero section updated successfully.');
    }

    // ─── Equipment & Catering ───────────────────────────

    public function updateEquipmentCatering(Request $request)
    {
        $page = MeetingPage::firstOrCreate(['id' => 1]);

        $validated = $request->validate([
            'equipment_heading' => 'nullable|string|max:255',
            'equipment_items' => 'nullable|string',
            'catering_heading' => 'nullable|string|max:255',
            'catering_description' => 'nullable|string',
            'catering_image_1' => 'nullable|image|max:8192',
            'catering_image_2' => 'nullable|image|max:8192',
            'catering_image_3' => 'nullable|image|max:8192',
        ]);

        $data = [];

        $data['equipment_heading'] = $request->input('equipment_heading');

        if ($request->filled('equipment_items')) {
            $items = array_filter(array_map('trim', explode("\n", $request->input('equipment_items'))));
            $data['equipment_items'] = array_values($items);
        } else {
            $data['equipment_items'] = [];
        }

        $data['catering_heading'] = $request->input('catering_heading');
        $data['catering_description'] = $request->input('catering_description');

        $imageFields = ['catering_image_1', 'catering_image_2', 'catering_image_3'];
        foreach ($imageFields as $field) {
            if ($request->hasFile($field)) {
                if ($page->$field) {
                    $oldPath = str_replace('/storage/', '', $page->$field);
                    Storage::disk('public')->delete($oldPath);
                }
                $path = $request->file($field)->store('meetings', 'public');
                $data[$field] = Storage::url($path);
            }
        }

        $page->update($data);

        return redirect()->route('website.admin.meeting.edit')
            ->with('success', 'Equipment & catering updated successfully.');
    }

    // ─── Contact & SEO ──────────────────────────────────

    public function updateContact(Request $request)
    {
        $page = MeetingPage::firstOrCreate(['id' => 1]);

        $validated = $request->validate([
            'contact_phone' => 'nullable|string|max:30',
            'contact_email' => 'nullable|email|max:255',
            'seo_title' => 'nullable|string|max:255',
            'seo_description' => 'nullable|string|max:500',
            'is_published' => 'nullable|boolean',
        ]);

        if ($request->has('is_published')) {
            $validated['is_published'] = $request->boolean('is_published');
        }

        $page->update($validated);

        return redirect()->route('website.admin.meeting.edit')
            ->with('success', 'Contact & SEO settings updated successfully.');
    }

    // ─── Meeting Rooms ─────────────────────────────────

    public function storeRoom(Request $request)
    {
        $page = MeetingPage::firstOrCreate(['id' => 1]);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'size_sqm' => 'nullable|numeric|min:0',
            'boardroom' => 'nullable|integer|min:0',
            'classroom' => 'nullable|integer|min:0',
            'theatre' => 'nullable|integer|min:0',
            'cocktail' => 'nullable|integer|min:0',
            'banquet' => 'nullable|integer|min:0',
            'cabaret' => 'nullable|integer|min:0',
            'ushape' => 'nullable|integer|min:0',
            'double_u' => 'nullable|integer|min:0',
            'triple_u' => 'nullable|integer|min:0',
        ]);

        $validated['meeting_page_id'] = $page->id;
        $validated['sort_order'] = $page->rooms()->count();

        MeetingRoom::create($validated);

        return redirect()->route('website.admin.meeting.edit')
            ->with('success', 'Meeting room added.');
    }

    public function updateRoom(Request $request, MeetingRoom $room)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'size_sqm' => 'nullable|numeric|min:0',
            'boardroom' => 'nullable|integer|min:0',
            'classroom' => 'nullable|integer|min:0',
            'theatre' => 'nullable|integer|min:0',
            'cocktail' => 'nullable|integer|min:0',
            'banquet' => 'nullable|integer|min:0',
            'cabaret' => 'nullable|integer|min:0',
            'ushape' => 'nullable|integer|min:0',
            'double_u' => 'nullable|integer|min:0',
            'triple_u' => 'nullable|integer|min:0',
        ]);

        $room->update($validated);

        return redirect()->route('website.admin.meeting.edit')
            ->with('success', 'Meeting room updated.');
    }

    public function destroyRoom(MeetingRoom $room)
    {
        $room->delete();

        return redirect()->route('website.admin.meeting.edit')
            ->with('success', 'Meeting room deleted.');
    }

    // ─── Gallery ───────────────────────────────────────

    public function storeGallery(Request $request)
    {
        $page = MeetingPage::firstOrCreate(['id' => 1]);

        $validated = $request->validate([
            'image' => 'required|image|max:8192',
            'alt_text' => 'nullable|string|max:255',
        ]);

        $path = $request->file('image')->store('meetings/gallery', 'public');

        $page->gallery()->create([
            'image' => Storage::url($path),
            'alt_text' => $validated['alt_text'] ?? null,
            'sort_order' => $page->gallery()->count(),
        ]);

        return redirect()->route('website.admin.meeting.edit')
            ->with('success', 'Gallery image added.');
    }

    public function destroyGallery(MeetingGallery $gallery)
    {
        $oldPath = str_replace('/storage/', '', $gallery->image);
        Storage::disk('public')->delete($oldPath);

        $gallery->delete();

        return redirect()->route('website.admin.meeting.edit')
            ->with('success', 'Gallery image deleted.');
    }
}
