<?php

namespace Modules\Housekeeping\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RoomUnit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Modules\Housekeeping\Models\HousekeepingLog;

class HousekeepingController extends Controller
{
    public function index()
    {
        $rooms = RoomUnit::with('roomType', 'currentOccupant')
            ->orderBy('floor')
            ->orderBy('room_number')
            ->get()
            ->groupBy('floor');

        $counts = [
            'dirty' => RoomUnit::where('cleaning_status', 'dirty')->count(),
            'cleaning' => RoomUnit::where('cleaning_status', 'cleaning')->count(),
            'clean' => RoomUnit::where('cleaning_status', 'clean')->count(),
            'inspected' => RoomUnit::where('cleaning_status', 'inspected')->count(),
        ];

        return view('housekeeping::index', compact('rooms', 'counts'));
    }

    public function updateStatus(Request $request)
    {
        $validated = $request->validate([
            'room_unit_id' => 'required|exists:room_units,id',
            'cleaning_status' => 'required|in:dirty,cleaning,clean,inspected',
            'notes' => 'nullable|string|max:500',
        ]);

        $room = RoomUnit::findOrFail($validated['room_unit_id']);
        $oldStatus = $room->cleaning_status ?? 'clean';

        $room->update([
            'cleaning_status' => $validated['cleaning_status'],
            'last_cleaned_at' => now(),
            'last_cleaned_by' => Auth::id(),
        ]);

        HousekeepingLog::create([
            'room_unit_id' => $room->id,
            'cleaned_by' => Auth::id(),
            'status_from' => $oldStatus,
            'status_to' => $validated['cleaning_status'],
            'notes' => $validated['notes'],
            'created_at' => now(),
        ]);

        return response()->json([
            'success' => true,
            'room' => $room->fresh()->load('roomType'),
            'message' => 'Room '.$room->room_number.' marked as '.ucfirst($validated['cleaning_status']),
        ]);
    }

    public function getRoomStatus($id)
    {
        $room = RoomUnit::with(['roomType', 'currentOccupant', 'housekeepingLogs' => function ($q) {
            $q->latest('created_at')->limit(10);
        }])->findOrFail($id);

        return response()->json($room);
    }

    public function bulkUpdate(Request $request)
    {
        $validated = $request->validate([
            'room_ids' => 'required|array',
            'room_ids.*' => 'exists:room_units,id',
            'cleaning_status' => 'required|in:dirty,cleaning,clean,inspected',
            'notes' => 'nullable|string|max:500',
        ]);

        $userId = Auth::id();
        $now = now();
        $count = 0;

        foreach ($validated['room_ids'] as $id) {
            $room = RoomUnit::find($id);
            if (! $room) {
                continue;
            }

            $oldStatus = $room->cleaning_status ?? 'clean';
            $room->update([
                'cleaning_status' => $validated['cleaning_status'],
                'last_cleaned_at' => $now,
                'last_cleaned_by' => $userId,
            ]);

            HousekeepingLog::create([
                'room_unit_id' => $room->id,
                'cleaned_by' => $userId,
                'status_from' => $oldStatus,
                'status_to' => $validated['cleaning_status'],
                'notes' => $validated['notes'],
                'created_at' => $now,
            ]);

            $count++;
        }

        return response()->json([
            'success' => true,
            'count' => $count,
            'message' => $count.' rooms updated to '.ucfirst($validated['cleaning_status']),
        ]);
    }

    public function logs()
    {
        $logs = HousekeepingLog::with('roomUnit.roomType', 'cleanedBy')
            ->latest('created_at')
            ->paginate(50);

        return view('housekeeping::logs', compact('logs'));
    }
}
