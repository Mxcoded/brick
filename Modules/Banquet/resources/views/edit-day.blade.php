@extends('staff::layouts.master')
@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Edit Event Day(s)</li>
@endsection

@section('page-content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold display-5 text-primary">
            <i class="fas fa-calendar-plus me-2"></i>
            {{ $day ? 'Edit Event Day for Order #'.$order->order_id : 'Add Event Day for Order #'.$order->order_id }}
        </h1>
        <a href="{{ route('banquet.orders.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Orders
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-primary text-white">
            <h5 class="card-title mb-0"><i class="fas fa-calendar-day me-2"></i>Event Day Details</h5>
        </div>
        <div class="card-body">
            <form action="{{ $day ? route('banquet.orders.update-day', [$order->order_id, $day->id]) : route('banquet.orders.store-day', $order->order_id) }}" 
                  method="POST" id="eventForm">
                @csrf
                @if($day)
                    @method('PUT')
                @endif

                <!-- Event Date -->
                <div class="mb-4">
                    <label for="event_date" class="form-label fw-bold text-muted">Event Date</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-calendar-alt text-primary"></i></span>
                        <input type="date" name="event_date" id="event_date" class="form-control" 
                               value="{{ old('event_date', optional($day->event_date)->format('Y-m-d')) }}" required>
                    </div>
                    @error('event_date') 
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Event Description -->
                <div class="mb-4">
                    <label for="event_description" class="form-label fw-bold text-muted">Event Description</label>
                    <textarea name="event_description" id="event_description" class="form-control" rows="3"
                              placeholder="Enter a brief description of the event">{{ old('event_description', $day ? $day->event_description : '') }}</textarea>
                    @error('event_description') 
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Guest Count -->
                <div class="mb-4">
                    <label for="guest_count" class="form-label fw-bold text-muted">Guest Count</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-users text-primary"></i></span>
                        <input type="number" name="guest_count" id="guest_count" class="form-control" 
                               value="{{ old('guest_count', $day ? $day->guest_count : '') }}" min="1" required>
                    </div>
                    @error('guest_count') 
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <!-- Event Status and Type -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="event_status" class="form-label fw-bold text-muted">Event Status</label>
                        <select name="event_status" id="event_status" class="form-select" required>
                            @foreach ($eventStatuses as $status)
                                <option value="{{ $status }}" {{ old('event_status', $day ? $day->event_status : 'Pending') === $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                        @error('event_status') 
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="event_type" class="form-label fw-bold text-muted">Event Type</label>
                        <select name="event_type" id="event_type" class="form-select" required>
                            @foreach ($eventTypes as $type)
                                <option value="{{ $type }}" {{ old('event_type', $day ? $day->event_type : 'Other') === $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                        @error('event_type') 
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Room and Setup Style -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="room" class="form-label fw-bold text-muted">Room</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-door-open text-primary"></i></span>
                            <select name="room" id="room" class="form-select" required>
                                <option value="">Select Room</option>
                                @foreach($locations as $room)
                                    <option value="{{ $room }}" {{ old('room', $day ? $day->room : '') === $room ? 'selected' : '' }}>
                                        {{ $room }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('room') 
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="setup_style" class="form-label fw-bold text-muted">Setup Style</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-chair text-primary"></i></span>
                            <select name="setup_style" id="setup_style" class="form-select" required>
                                @foreach($setupStyles as $style)
                                    <option value="{{ $style }}" {{ old('setup_style', $day ? $day->setup_style : '') === $style ? 'selected' : '' }}>
                                        {{ $style }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('setup_style') 
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Timing Section -->
                @php
                    $useStandardTiming = $day && substr($day->start_time, 0, 5) === '08:00' && substr($day->end_time, 0, 5) === '20:00';
                    $standardTimingChecked = old('standard_timing') === 'on' || $useStandardTiming;
                @endphp
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="standard_timing" 
                                   {{ $standardTimingChecked ? 'checked' : '' }}>
                            <label class="form-check-label" for="standard_timing">
                                Use Standard Timing (8:00 AM - 8:00 PM)
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="start_time" class="form-label fw-bold text-muted">Start Time</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-clock text-primary"></i></span>
                            <input type="time" name="start_time" id="start_time" class="form-control" 
                                   value="{{ old('start_time', $day ? substr($day->start_time, 0, 5) : '') }}" required>
                        </div>
                        @error('start_time') 
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="end_time" class="form-label fw-bold text-muted">End Time</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-clock text-primary"></i></span>
                            <input type="time" name="end_time" id="end_time" class="form-control" 
                                   value="{{ old('end_time', $day ? substr($day->end_time, 0, 5) : '') }}" required>
                        </div>
                        @error('end_time') 
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Submit Button -->
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-calendar-check me-2"></i>
                        {{ $day ? 'Update Event Day' : 'Add Event Day' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const standardTimingCheckbox = document.getElementById('standard_timing');
    const startTimeInput = document.getElementById('start_time');
    const endTimeInput = document.getElementById('end_time');

    function updateTimeInputs() {
        if (standardTimingCheckbox.checked) {
            startTimeInput.value = '08:00';
            endTimeInput.value = '20:00';
            startTimeInput.setAttribute('readonly', 'true');
            endTimeInput.setAttribute('readonly', 'true');
        } else {
            startTimeInput.removeAttribute('readonly');
            endTimeInput.removeAttribute('readonly');
            // Preserve existing values instead of clearing them
        }
    }

    // Initial check
    updateTimeInputs();

    // Add event listener
    standardTimingCheckbox.addEventListener('change', updateTimeInputs);

    // Handle form submission
    document.getElementById('eventForm').addEventListener('submit', function(e) {
        if (standardTimingCheckbox.checked) {
            startTimeInput.removeAttribute('readonly');
            endTimeInput.removeAttribute('readonly');
        }
    });
});
</script>
@endsection