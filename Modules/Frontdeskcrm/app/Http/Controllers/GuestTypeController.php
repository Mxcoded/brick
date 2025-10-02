<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Frontdeskcrm\Http\Requests\StoreGuestTypeRequest;
use Modules\Frontdeskcrm\Http\Requests\UpdateGuestTypeRequest;
use Modules\Frontdeskcrm\Models\GuestType;

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
        return view('frontdeskcrm::guest-types.create');
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
        }]);
        return view('frontdeskcrm::guest-types.show', compact('guestType'));
    }

    public function edit(GuestType $guestType)
    {
        return view('frontdeskcrm::guest-types.edit', compact('guestType'));
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
}
