<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use App\Models\RoomUnit;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Modules\Frontdeskcrm\Models\Channel;
use Modules\Frontdeskcrm\Services\ChannelSyncService;

class ChannelController extends Controller
{
    public function index()
    {
        $channels = Channel::with('creator')->latest()->get();
        $stats = [
            'total' => $channels->count(),
            'active' => $channels->where('is_active', true)->count(),
            'needs_sync' => $channels->filter(fn ($c) => $c->is_active && (! $c->last_sync_at || $c->last_sync_at->diffInMinutes(now()) > 60))->count(),
        ];

        return view('frontdeskcrm::channels.index', compact('channels', 'stats'));
    }

    public function create()
    {
        $roomUnits = RoomUnit::with('roomType')->get();
        $providers = Channel::PROVIDERS;

        return view('frontdeskcrm::channels.create', compact('roomUnits', 'providers'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'nullable|string|max:100',
            'api_key' => 'nullable|string',
            'api_endpoint' => 'nullable|url|max:500',
            'webhook_url' => 'nullable|url|max:500',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
            'room_mappings' => 'nullable|array',
            'room_mappings.*.room_unit_id' => 'required|exists:room_units,id',
            'room_mappings.*.external_room_id' => 'nullable|string|max:100',
            'room_mappings.*.external_room_name' => 'nullable|string|max:255',
        ]);

        $data['created_by'] = auth()->id();
        $data['last_sync_status'] = 'never';

        $channel = Channel::create($data);

        if (! empty($request->room_mappings)) {
            foreach ($request->room_mappings as $mapping) {
                $channel->roomMappings()->create($mapping);
            }
        }

        return redirect()->route('frontdesk.channels.index')
            ->with('success', 'Channel "'.$channel->name.'" created.');
    }

    public function show($id)
    {
        $channel = Channel::with(['creator', 'roomMappings.roomUnit.roomType'])->findOrFail($id);

        return view('frontdeskcrm::channels.show', compact('channel'));
    }

    public function edit($id)
    {
        $channel = Channel::with('roomMappings')->findOrFail($id);
        $roomUnits = RoomUnit::with('roomType')->get();
        $providers = Channel::PROVIDERS;

        return view('frontdeskcrm::channels.edit', compact('channel', 'roomUnits', 'providers'));
    }

    public function update(Request $request, $id)
    {
        $channel = Channel::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'provider' => 'nullable|string|max:100',
            'api_key' => 'nullable|string',
            'api_endpoint' => 'nullable|url|max:500',
            'webhook_url' => 'nullable|url|max:500',
            'is_active' => 'boolean',
            'notes' => 'nullable|string|max:1000',
            'room_mappings' => 'nullable|array',
            'room_mappings.*.room_unit_id' => 'required|exists:room_units,id',
            'room_mappings.*.external_room_id' => 'nullable|string|max:100',
            'room_mappings.*.external_room_name' => 'nullable|string|max:255',
        ]);

        $channel->update($data);

        $channel->roomMappings()->delete();
        if (! empty($request->room_mappings)) {
            foreach ($request->room_mappings as $mapping) {
                $channel->roomMappings()->create($mapping);
            }
        }

        return redirect()->route('frontdesk.channels.index')
            ->with('success', 'Channel "'.$channel->name.'" updated.');
    }

    public function destroy($id)
    {
        $channel = Channel::findOrFail($id);
        $channel->delete();

        return redirect()->route('frontdesk.channels.index')
            ->with('success', 'Channel deleted.');
    }

    public function sync($id)
    {
        $channel = Channel::with('roomMappings')->findOrFail($id);

        $service = app(ChannelSyncService::class);
        $results = $service->sync($channel);

        if (! empty($results['errors'])) {
            return back()->with('error', 'Sync completed with errors: '.implode('; ', $results['errors']));
        }

        $parts = [];
        if ($results['availability_pushed']) {
            $parts[] = 'availability pushed';
        }
        if ($results['bookings_pulled'] > 0) {
            $parts[] = "{$results['bookings_pulled']} booking(s) pulled";
        }

        return back()->with('success', 'Sync successful ('.implode(', ', $parts).').');
    }
}
