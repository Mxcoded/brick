<?php

namespace Modules\Banquet\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Modules\Banquet\Models\LeadEvent;

class LeadEventController extends Controller
{
    public function index()
    {
        $events = LeadEvent::withCount('leads')->latest()->paginate(20);

        return view('banquet::lead-events.index', compact('events'));
    }

    public function create()
    {
        return view('banquet::lead-events.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:lead_events,code',
            'description' => 'nullable|string',
            'event_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'organizer' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'hero_subtitle' => 'nullable|string|max:255',
            'form_heading' => 'nullable|string|max:255',
            'form_subtext' => 'nullable|string',
            'thank_you_message' => 'nullable|string|max:500',
            'confirmation_email_body' => 'nullable|string',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('hero_image')) {
            $validated['hero_image'] = $request->file('hero_image')->store('lead-events', 'public');
        }

        $event = LeadEvent::create($validated);

        return redirect()->route('banquet.lead-events.index')
            ->with('success', "Event \"{$event->title}\" created.");
    }

    public function edit($id)
    {
        $event = LeadEvent::findOrFail($id);

        return view('banquet::lead-events.edit', compact('event'));
    }

    public function update(Request $request, $id)
    {
        $event = LeadEvent::findOrFail($id);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'code' => 'nullable|string|max:50|unique:lead_events,code,'.$event->id,
            'description' => 'nullable|string',
            'event_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'organizer' => 'nullable|string|max:255',
            'is_active' => 'nullable|boolean',
            'hero_subtitle' => 'nullable|string|max:255',
            'form_heading' => 'nullable|string|max:255',
            'form_subtext' => 'nullable|string',
            'thank_you_message' => 'nullable|string|max:500',
            'confirmation_email_body' => 'nullable|string',
        ]);

        $validated['is_active'] = $request->boolean('is_active');

        if ($request->hasFile('hero_image')) {
            if ($event->hero_image) {
                Storage::disk('public')->delete($event->hero_image);
            }
            $validated['hero_image'] = $request->file('hero_image')->store('lead-events', 'public');
        }

        if ($request->boolean('remove_hero_image')) {
            if ($event->hero_image) {
                Storage::disk('public')->delete($event->hero_image);
            }
            $validated['hero_image'] = null;
        }

        $event->update($validated);

        return redirect()->route('banquet.lead-events.index')
            ->with('success', "Event \"{$event->title}\" updated.");
    }

    public function qrcode($id)
    {
        $event = LeadEvent::findOrFail($id);
        $url = route('website.event-lead', $event->slug);

        return view('banquet::lead-events.qrcode', compact('event', 'url'));
    }

    public function destroy($id)
    {
        $event = LeadEvent::withCount('leads')->findOrFail($id);

        if ($event->leads_count > 0) {
            return redirect()->route('banquet.lead-events.index')
                ->with('error', "Cannot delete — {$event->leads_count} lead(s) are linked to this event.");
        }

        $event->delete();

        return redirect()->route('banquet.lead-events.index')
            ->with('success', 'Event deleted.');
    }
}
