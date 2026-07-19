<?php

namespace Modules\Housekeeping\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\RoomUnit;
use App\Services\PropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomUnitController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = RoomUnit::with('room');

        if ($propertyId = app(PropertyService::class)->id()) {
            $query->where('property_id', $propertyId);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $units = $query->orderBy('number')->paginate($request->get('per_page', 25));

        return response()->json([
            'data' => $units->items(),
            'meta' => [
                'current_page' => $units->currentPage(),
                'last_page' => $units->lastPage(),
                'per_page' => $units->perPage(),
                'total' => $units->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'room_id' => 'required|exists:rooms,id',
            'number' => 'required|string|max:50',
            'status' => 'sometimes|in:available,occupied,maintenance,dirty',
        ]);

        if ($propertyId = app(PropertyService::class)->id()) {
            $validated['property_id'] = $propertyId;
        }
        $validated['status'] = $validated['status'] ?? 'available';

        $unit = RoomUnit::create($validated);
        $unit->load('room');

        return response()->json([
            'message' => 'Room unit created.',
            'data' => $unit,
        ], 201);
    }

    public function show(RoomUnit $roomUnit): JsonResponse
    {
        $roomUnit->load(['room', 'registrations' => function ($q) {
            $q->where('stay_status', 'checked_in');
        }]);

        return response()->json(['data' => $roomUnit]);
    }

    public function update(Request $request, RoomUnit $roomUnit): JsonResponse
    {
        $validated = $request->validate([
            'room_id' => 'sometimes|exists:rooms,id',
            'number' => 'sometimes|string|max:50',
            'status' => 'sometimes|in:available,occupied,maintenance,dirty',
        ]);

        $roomUnit->update($validated);
        $roomUnit->load('room');

        return response()->json([
            'message' => 'Room unit updated.',
            'data' => $roomUnit,
        ]);
    }

    public function updateStatus(Request $request, RoomUnit $roomUnit): JsonResponse
    {
        $validated = $request->validate([
            'status' => 'required|in:available,occupied,maintenance,dirty',
        ]);

        $roomUnit->update(['status' => $validated['status']]);

        return response()->json([
            'message' => 'Room unit status updated.',
            'data' => $roomUnit->fresh(['room']),
        ]);
    }

    public function destroy(RoomUnit $roomUnit): JsonResponse
    {
        $roomUnit->delete();

        return response()->json(['message' => 'Room unit deleted.'], 204);
    }
}
