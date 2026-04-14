@extends('layouts.master')

@section('title', 'Check-in Online Booking')

@section('page-content')
    <div class="container py-4">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Check-in: {{ $booking->guest_name }} ({{ $booking->booking_reference }})</h5>
            </div>
            <div class="card-body">

                {{-- Alert if the preferred room is actually occupied by someone else (Overstay) --}}
                @php
                    $isPreferredRoomTaken = \Modules\Frontdeskcrm\Models\Registration::where(
                        'room_id',
                        $booking->room_id,
                    )
                        ->whereIn('stay_status', ['checked_in', 'draft_by_guest'])
                        ->exists();
                @endphp

                @if ($isPreferredRoomTaken)
                    <div class="alert alert-warning border-start border-4 border-warning shadow-sm">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention:</strong> The guest preferred <strong>{{ $booking->room->name }}</strong>, but it
                        is currently occupied (perhaps an overstay). Please allocate a different room below.
                    </div>
                @else
                    <div class="alert alert-info border-start border-4 border-info shadow-sm">
                        <i class="fas fa-info-circle me-2"></i>
                        The guest selected <strong>{{ $booking->room->name }}</strong>. You can confirm this or move them to
                        another room.
                    </div>
                @endif
                {{-- ✅ NEW: Identity Verification Card --}}
                <div class="card bg-light border mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="fas fa-id-card me-2"></i>Guest Identity Verification
                        </h6>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <small class="text-muted d-block text-uppercase">Full Name</small>
                                <span class="fw-bold">{{ $booking->guest_name }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block text-uppercase">ID Type</small>
                                <span class="fw-bold text-primary">
                                    {{ $booking->guest->identification_type ?? 'Not Provided' }}
                                </span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block text-uppercase">ID Number</small>
                                <span class="fw-bold text-primary">
                                    {{ $booking->guest->identification_number ?? 'Not Provided' }}
                                </span>
                            </div>
                            <div class="col-md-8">
                                <small class="text-muted d-block text-uppercase">Address</small>
                                <span>{{ $booking->guest->home_address ?? 'Not Provided' }}</span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block text-uppercase">Contact</small>
                                <span>{{ $booking->guest_phone }}</span>
                            </div>
                        </div>
                    </div>
                </div>
                <form action="{{ route('frontdesk.bookings.process', $booking->booking_reference) }}" method="POST">
                    @csrf
                    <div class="row g-3">

                        {{-- ✅ 1. DYNAMIC ROOM ALLOCATION (The Fix) --}}
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Allocate Room <span class="text-danger">*</span></label>
                            <select name="room_id"
                                class="form-select form-select-lg {{ $isPreferredRoomTaken ? 'is-invalid' : '' }}">

                                {{-- Option A: The Preferred Room --}}
                                <option value="{{ $booking->room_id }}" {{ !$isPreferredRoomTaken ? 'selected' : '' }}
                                    class="{{ $isPreferredRoomTaken ? 'text-danger fw-bold' : 'fw-bold' }}">
                                    {{ $booking->room->name }} (Guest Preference)
                                    {{ $isPreferredRoomTaken ? '[OCCUPIED]' : '' }}
                                </option>

                                {{-- Option B: Other Available Rooms --}}
                                <optgroup label="Available Rooms">
                                    @foreach (\Modules\Website\Models\Room::where('status', 'available')->where('id', '!=', $booking->room_id)->get() as $room)
                                        {{-- Check if physically free --}}
                                        @php
                                            $isOccupied = \Modules\Frontdeskcrm\Models\Registration::where(
                                                'room_id',
                                                $room->id,
                                            )
                                                ->whereIn('stay_status', ['checked_in'])
                                                ->exists();
                                        @endphp

                                        @unless ($isOccupied)
                                            <option value="{{ $room->id }}">
                                                {{ $room->name }} ({{ $room->capacity }} Guests) -
                                                ₦{{ number_format($room->price) }}
                                            </option>
                                        @endunless
                                    @endforeach
                                </optgroup>
                            </select>
                            <div class="form-text text-muted">Finalize the physical room assignment here.</div>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label text-muted">Checkout Date</label>
                            <input type="text" class="form-control bg-light"
                                value="{{ $booking->check_out_date->format('M d, Y') }}" readonly>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label fw-bold">Guest ID / Passport Number <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="identification_number" class="form-control" required
                                placeholder="Enter ID from physical document">
                        </div>
                    </div>

                    <div class="mt-4 d-flex justify-content-end gap-2">
                        <a href="{{ route('frontdesk.registrations.dashboard') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-success fw-bold">
                            <i class="fas fa-check-circle me-2"></i> Finalize Allocation & Check In
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
