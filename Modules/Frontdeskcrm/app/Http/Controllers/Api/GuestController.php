<?php

namespace Modules\Frontdeskcrm\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Frontdeskcrm\Models\Guest;

class GuestController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Guest::query();

        if ($propertyId = app(PropertyService::class)->id()) {
            $query->where('property_id', $propertyId);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        $guests = $query->latest()->paginate($request->get('per_page', 25));

        return response()->json([
            'data' => $guests->items(),
            'meta' => [
                'current_page' => $guests->currentPage(),
                'last_page' => $guests->lastPage(),
                'per_page' => $guests->perPage(),
                'total' => $guests->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'email' => 'nullable|email',
            'contact_number' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:100',
            'identification_type' => 'nullable|string|max:50',
            'identification_number' => 'nullable|string|max:100',
            'home_address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
        ]);

        if ($propertyId = app(PropertyService::class)->id()) {
            $validated['property_id'] = $propertyId;
        }

        $guest = Guest::create($validated);

        return response()->json([
            'message' => 'Guest created.',
            'data' => $guest,
        ], 201);
    }

    public function show(Guest $guest): JsonResponse
    {
        $guest->load('registrations');

        return response()->json(['data' => $guest]);
    }

    public function update(Request $request, Guest $guest): JsonResponse
    {
        $validated = $request->validate([
            'full_name' => 'sometimes|string|max:255',
            'email' => 'nullable|email',
            'contact_number' => 'nullable|string|max:50',
            'nationality' => 'nullable|string|max:100',
            'identification_type' => 'nullable|string|max:50',
            'identification_number' => 'nullable|string|max:100',
            'home_address' => 'nullable|string',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:100',
        ]);

        $guest->update($validated);

        return response()->json([
            'message' => 'Guest updated.',
            'data' => $guest,
        ]);
    }

    public function destroy(Guest $guest): JsonResponse
    {
        if ($guest->registrations()->where('stay_status', 'checked_in')->exists()) {
            return response()->json([
                'message' => 'Cannot delete guest with active registration.',
            ], 422);
        }

        $guest->delete();

        return response()->json(['message' => 'Guest deleted.'], 204);
    }
}
