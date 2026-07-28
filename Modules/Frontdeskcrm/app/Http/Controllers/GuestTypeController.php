<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Frontdeskcrm\Http\Requests\StoreGuestTypeRequest;
use Modules\Frontdeskcrm\Http\Requests\UpdateGuestTypeRequest;
use Modules\Frontdeskcrm\Models\GuestType;
use Modules\Frontdeskcrm\Models\GuestTypeRate;
use Modules\Website\Models\RoomType;

class GuestTypeController extends Controller
{
    public function index(Request $request)
    {
        $types = GuestType::withCount(['registrations' => function ($query) {
            $query->where('stay_status', 'checked_out');
        }])->paginate(10);

        return view('frontdeskcrm::guest-types.index', compact('types'));
    }

    public function create()
    {
        $roomTypes = RoomType::all();

        return view('frontdeskcrm::guest-types.create', compact('roomTypes'));
    }

    public function store(StoreGuestTypeRequest $request)
    {
        GuestType::create($request->validated());

        return redirect()->route('frontdesk.guest-types.index')->with('success', 'Guest type added.');
    }

    public function show(GuestType $guestType)
    {
        $guestType->load(['registrations' => function ($query) {
            $query->with('guest')->where('stay_status', 'checked_out');
        }, 'rates.roomType']);

        $roomTypes = RoomType::all();

        return view('frontdeskcrm::guest-types.show', compact('guestType', 'roomTypes'));
    }

    public function edit(GuestType $guestType)
    {
        $roomTypes = RoomType::all();

        return view('frontdeskcrm::guest-types.edit', compact('guestType', 'roomTypes'));
    }

    public function update(UpdateGuestTypeRequest $request, GuestType $guestType)
    {
        $guestType->update($request->validated());

        return redirect()->route('frontdesk.guest-types.index')->with('success', 'Guest type updated.');
    }

    public function destroy(GuestType $guestType)
    {
        if ($guestType->registrations()->count() > 0) {
            return back()->with('error', 'Cannot delete type with existing registrations.');
        }
        $guestType->delete();

        return redirect()->route('frontdesk.guest-types.index')->with('success', 'Guest type deleted.');
    }

    public function negotiatedRate(GuestType $guestType, int $roomTypeId)
    {
        $result = $guestType->getNegotiatedRate($roomTypeId);

        return response()->json($result);
    }

    public function storeRate(Request $request, GuestType $guestType)
    {
        $validated = $request->validate([
            'room_type_id' => 'required|exists:room_types,id',
            'rate' => 'required|numeric|min:0',
            'valid_from' => 'nullable|date',
            'valid_to' => 'nullable|date|after_or_equal:valid_from',
        ]);

        $validated['guest_type_id'] = $guestType->id;
        $validated['is_active'] = true;

        GuestTypeRate::create($validated);

        return redirect()->route('frontdesk.guest-types.show', $guestType)->with('success', 'Negotiated rate added.');
    }

    public function destroyRate(GuestType $guestType, GuestTypeRate $rate)
    {
        if ($rate->guest_type_id !== $guestType->id) {
            return back()->with('error', 'Invalid rate entry.');
        }
        $rate->delete();

        return redirect()->route('frontdesk.guest-types.show', $guestType)->with('success', 'Negotiated rate removed.');
    }
}
