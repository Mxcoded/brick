<?php

namespace Modules\Housekeeping\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Room;
use App\Services\PropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoomController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Room::with('roomType');

        if ($propertyId = app(PropertyService::class)->id()) {
            $query->where('property_id', $propertyId);
        }

        $rooms = $query->orderBy('name')->paginate($request->get('per_page', 25));

        return response()->json([
            'data' => $rooms->items(),
            'meta' => [
                'current_page' => $rooms->currentPage(),
                'last_page' => $rooms->lastPage(),
                'per_page' => $rooms->perPage(),
                'total' => $rooms->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50',
            'room_type_id' => 'required|exists:room_types,id',
            'floor' => 'nullable|integer',
        ]);

        if ($propertyId = app(PropertyService::class)->id()) {
            $validated['property_id'] = $propertyId;
        }

        $room = Room::create($validated);
        $room->load('roomType');

        return response()->json([
            'message' => 'Room created.',
            'data' => $room,
        ], 201);
    }

    public function show(Room $room): JsonResponse
    {
        $room->load(['roomType', 'roomUnits']);

        return response()->json(['data' => $room]);
    }

    public function update(Request $request, Room $room): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:50',
            'room_type_id' => 'sometimes|exists:room_types,id',
            'floor' => 'nullable|integer',
        ]);

        $room->update($validated);
        $room->load('roomType');

        return response()->json([
            'message' => 'Room updated.',
            'data' => $room,
        ]);
    }

    public function destroy(Room $room): JsonResponse
    {
        $room->delete();

        return response()->json(['message' => 'Room deleted.'], 204);
    }
}
