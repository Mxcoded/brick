<?php

namespace Modules\Frontdeskcrm\Http\Controllers;

use Illuminate\Http\Request;
use Modules\Frontdeskcrm\Http\Requests\StoreBookingSourceRequest;
use Modules\Frontdeskcrm\Http\Requests\UpdateBookingSourceRequest;
use Modules\Frontdeskcrm\Models\BookingSource;

class BookingSourceController extends Controller
{
    public function index(Request $request)
    {
        $sources = BookingSource::withCount(['registrations' => function ($query) {
            $query->where('stay_status', 'checked_out'); // For revenue filter
        }])->when($request->type, fn($q) => $q->where('type', $request->type))->paginate(10);
        return view('frontdeskcrm::booking-sources.index', compact('sources'));
    }

    public function create()
    {
        return view('frontdeskcrm::booking-sources.create');
    }

    public function store(StoreBookingSourceRequest $request)
    {
        BookingSource::create($request->validated());
        return redirect()->route('frontdesk.booking-sources.index')->with('success', 'Booking source added.');
    }

    public function show(BookingSource $bookingSource)
    {
        $bookingSource->load(['registrations' => function ($query) {
            $query->with('guest')->where('stay_status', 'checked_out');
        }]);
        return view('frontdeskcrm::booking-sources.show', compact('bookingSource'));
    }

    public function edit(BookingSource $bookingSource)
    {
        return view('frontdeskcrm::booking-sources.edit', compact('bookingSource'));
    }

    public function update(UpdateBookingSourceRequest $request, BookingSource $bookingSource)
    {
        $bookingSource->update($request->validated());
        return redirect()->route('frontdesk.booking-sources.index')->with('success', 'Booking source updated.');
    }

    public function destroy(BookingSource $bookingSource)
    {
        if ($bookingSource->registrations()->count() > 0) {
            return back()->with('error', 'Cannot delete source with existing bookings.');
        }
        $bookingSource->delete();
        return redirect()->route('frontdesk.booking-sources.index')->with('success', 'Booking source deleted.');
    }
}
