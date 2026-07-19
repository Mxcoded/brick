<?php

namespace Modules\Frontdeskcrm\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PropertyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Modules\Frontdeskcrm\Models\Registration;

class RegistrationController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Registration::with(['guest', 'roomUnit.room']);

        if ($propertyId = app(PropertyService::class)->id()) {
            $query->where('property_id', $propertyId);
        }

        if ($request->filled('status')) {
            $query->where('stay_status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('full_name', 'like', "%{$search}%")
                    ->orWhere('contact_number', 'like', "%{$search}%");
            });
        }

        $registrations = $query->latest()->paginate($request->get('per_page', 25));

        return response()->json([
            'data' => $registrations->items(),
            'meta' => [
                'current_page' => $registrations->currentPage(),
                'last_page' => $registrations->lastPage(),
                'per_page' => $registrations->perPage(),
                'total' => $registrations->total(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'guest_id' => 'required|exists:guests,id',
            'room_unit_id' => 'required|exists:room_units,id',
            'check_in' => 'required|date',
            'check_out' => 'required|date|after:check_in',
            'no_of_guests' => 'required|integer|min:1',
            'room_rate' => 'required|numeric|min:0',
            'full_name' => 'nullable|string|max:255',
            'contact_number' => 'nullable|string|max:50',
            'email' => 'nullable|email',
        ]);

        if ($propertyId = app(PropertyService::class)->id()) {
            $validated['property_id'] = $propertyId;
        }
        $validated['stay_status'] = 'reserved';
        $validated['registration_date'] = now()->toDateString();
        $validated['total_amount'] = 0;

        $registration = Registration::create($validated);
        $registration->load(['guest', 'roomUnit.room']);

        return response()->json([
            'message' => 'Registration created.',
            'data' => $registration,
        ], 201);
    }

    public function show(Registration $registration): JsonResponse
    {
        $registration->load(['guest', 'roomUnit.room', 'folioCharges', 'payments']);
        $registration->loadCount(['folioCharges', 'payments']);

        return response()->json(['data' => $registration]);
    }

    public function update(Request $request, Registration $registration): JsonResponse
    {
        $validated = $request->validate([
            'check_in' => 'sometimes|date',
            'check_out' => 'sometimes|date|after:check_in',
            'no_of_guests' => 'sometimes|integer|min:1',
            'room_rate' => 'sometimes|numeric|min:0',
        ]);

        $registration->update($validated);
        $registration->load(['guest', 'roomUnit.room']);

        return response()->json([
            'message' => 'Registration updated.',
            'data' => $registration,
        ]);
    }

    public function destroy(Registration $registration): JsonResponse
    {
        if ($registration->stay_status === 'checked_in') {
            return response()->json([
                'message' => 'Cannot delete a checked-in registration. Checkout first.',
            ], 422);
        }

        $registration->delete();

        return response()->json(['message' => 'Registration deleted.'], 204);
    }

    public function checkin(Registration $registration): JsonResponse
    {
        if ($registration->stay_status !== 'reserved') {
            return response()->json([
                'message' => 'Only reserved registrations can be checked in.',
            ], 422);
        }

        $registration->update([
            'stay_status' => 'checked_in',
            'registration_date' => now()->toDateString(),
            'checked_in_at' => now(),
        ]);

        $registration->load(['guest', 'roomUnit.room']);

        return response()->json([
            'message' => 'Guest checked in successfully.',
            'data' => $registration,
        ]);
    }

    public function checkout(Registration $registration): JsonResponse
    {
        if ($registration->stay_status !== 'checked_in') {
            return response()->json([
                'message' => 'Only checked-in registrations can be checked out.',
            ], 422);
        }

        $registration->update([
            'stay_status' => 'checked_out',
            'actual_checkout_at' => now(),
        ]);

        $registration->load(['guest', 'roomUnit.room']);

        return response()->json([
            'message' => 'Guest checked out successfully.',
            'data' => $registration,
        ]);
    }
}
