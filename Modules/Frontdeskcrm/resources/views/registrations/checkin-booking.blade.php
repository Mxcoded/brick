@extends('layouts.master')

@section('title', 'Check-in Online Booking')

@section('page-content')
    <div class="container py-4">
        <div class="card shadow border-0">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Check-in: {{ $booking->guest_name }} ({{ $booking->booking_reference }})</h5>
            </div>
            <div class="card-body">

                @php
                    $preferredUnit = $booking->roomUnit;
                    $preferredUnitTaken = false;
                    if ($preferredUnit) {
                        $preferredUnitTaken = \Modules\Frontdeskcrm\Models\Registration::where('room_unit_id', $preferredUnit->id)
                            ->whereIn('stay_status', ['checked_in', 'draft_by_guest', 'reserved'])
                            ->exists();
                    }
                @endphp

                @if ($preferredUnitTaken)
                    <div class="alert alert-warning border-start border-4 border-warning shadow-sm">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <strong>Attention:</strong> The guest preferred <strong>Room {{ $preferredUnit->room_number }} ({{ $booking->roomType->name ?? 'N/A' }})</strong>, but it
                        is currently occupied. Please allocate a different room below.
                    </div>
                @elseif ($preferredUnit)
                    <div class="alert alert-info border-start border-4 border-info shadow-sm">
                        <i class="fas fa-info-circle me-2"></i>
                        The guest selected <strong>Room {{ $preferredUnit->room_number }} ({{ $booking->roomType->name ?? 'N/A' }})</strong>. You can confirm this or move them to
                        another room.
                    </div>
                @else
                    <div class="alert alert-info border-start border-4 border-info shadow-sm">
                        <i class="fas fa-info-circle me-2"></i>
                        No room was assigned during booking. Please select a room below, or one will be auto-assigned.
                    </div>
                @endif

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
                                    {{ $booking->guest?->identification_type ?? 'Not Provided' }}
                                </span>
                            </div>
                            <div class="col-md-4">
                                <small class="text-muted d-block text-uppercase">ID Number</small>
                                <span class="fw-bold text-primary">
                                    {{ $booking->guest?->identification_number ?? 'Not Provided' }}
                                </span>
                            </div>
                            <div class="col-md-8">
                                <small class="text-muted d-block text-uppercase">Address</small>
                                <span>{{ $booking->guest?->home_address ?? 'Not Provided' }}</span>
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
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Allocate Room <span class="text-danger">*</span></label>
                            <select name="room_unit_id" class="form-select form-select-lg {{ ($preferredUnit && $preferredUnitTaken) ? 'is-invalid' : '' }}">
                                @if ($preferredUnit && ! $preferredUnitTaken)
                                    <option value="{{ $preferredUnit->id }}" selected class="fw-bold">
                                        Room {{ $preferredUnit->room_number }} ({{ $booking->roomType->name ?? 'N/A' }}) — Guest Preference
                                    </option>
                                @elseif ($preferredUnit && $preferredUnitTaken)
                                    <option value="" disabled selected class="text-danger fw-bold">
                                        Room {{ $preferredUnit->room_number }} (Occupied)
                                    </option>
                                @else
                                    <option value="" disabled selected>Select a room...</option>
                                @endif

                                @php
                                    $availabilityService = app(\Modules\Website\Services\RoomAvailabilityService::class);
                                    $availableUnits = $availabilityService->getAvailableUnits(
                                        $booking->room_type_id,
                                        $booking->check_in_date,
                                        $booking->check_out_date
                                    );
                                @endphp

                                @if ($availableUnits->count() > 0)
                                    <optgroup label="Available Rooms ({{ $booking->roomType->name ?? 'All Types' }})">
                                        @foreach ($availableUnits as $unit)
                                            @if ($preferredUnit && $unit->id === $preferredUnit->id)
                                                @continue
                                            @endif
                                            <option value="{{ $unit->id }}">
                                                Room {{ $unit->room_number }} ({{ $unit->roomType->name ?? 'N/A' }})
                                                — ₦{{ number_format($booking->roomType->price ?? $unit->roomType->price ?? 0) }}/night
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                            </select>
                            @error('room_unit_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text text-muted">Select the physical room or leave blank to auto-assign the best available.</div>
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
