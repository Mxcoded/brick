@extends('layouts.master')
@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('banquet.orders.index') }}">Banquet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('banquet.orders.show', $order->order_id) }}">Order #{{ $order->order_id }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Event Day</li>
@endsection

@section('page-content')
<div class="container-fluid px-4 banquet-theme">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="fw-bold display-5 text-charcoal">
            <i class="fas fa-edit me-2 text-gold"></i>Edit Event Day
        </h1>
        <a href="{{ route('banquet.orders.show', $order->order_id) }}" class="btn btn-outline-charcoal">
            <i class="fas fa-arrow-left me-2"></i>Back to Order
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-header bg-gold text-white py-3">
            <h5 class="card-title mb-0"><i class="fas fa-calendar-day me-2"></i>Update Details</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('banquet.orders.update-day', [$order->order_id, $day->id]) }}" method="POST" id="eventForm">
                @csrf
                @method('PUT')

                {{-- Date, Description, Guest Count, Status, Type inputs remain unchanged --}}
                <div class="mb-4">
                    <label for="event_date" class="form-label fw-bold text-charcoal">Event Date</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-calendar-alt text-gold"></i></span>
                        <input type="date" name="event_date" id="event_date" class="form-control border-start-0 ps-0" 
                               value="{{ old('event_date', optional($day->event_date)->format('Y-m-d')) }}" required>
                    </div>
                    @error('event_date') 
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="event_description" class="form-label fw-bold text-charcoal">Event Description</label>
                    <textarea name="event_description" id="event_description" class="form-control" rows="3"
                              placeholder="Enter a brief description of the event">{{ old('event_description', $day->event_description) }}</textarea>
                    @error('event_description') 
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="guest_count" class="form-label fw-bold text-charcoal">Guest Count</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0"><i class="fas fa-users text-gold"></i></span>
                        <input type="number" name="guest_count" id="guest_count" class="form-control border-start-0 ps-0" 
                               value="{{ old('guest_count', $day->guest_count) }}" min="1" required>
                    </div>
                    @error('guest_count') 
                        <div class="text-danger small mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="event_status" class="form-label fw-bold text-charcoal">Event Status</label>
                        <select name="event_status" id="event_status" class="form-select" required>
                            @foreach ($eventStatuses as $status)
                                <option value="{{ $status }}" {{ old('event_status', $day->event_status) === $status ? 'selected' : '' }}>
                                    {{ $status }}
                                </option>
                            @endforeach
                        </select>
                        @error('event_status') 
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="event_type" class="form-label fw-bold text-charcoal">Event Type</label>
                        <select name="event_type" id="event_type" class="form-select" required>
                            @foreach ($eventTypes as $type)
                                <option value="{{ $type }}" {{ old('event_type', $day->event_type) === $type ? 'selected' : '' }}>
                                    {{ $type }}
                                </option>
                            @endforeach
                        </select>
                        @error('event_type') 
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- REFACTORED: Dynamic Room and Style Selection --}}
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <label for="banquet_venue_id" class="form-label fw-bold text-charcoal">Venue (Room)</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-door-open text-gold"></i></span>
                            <select name="banquet_venue_id" id="banquet_venue_id" class="form-select border-start-0 ps-0" required>
                                <option value="">Select Venue</option>
                                @foreach($venues as $venue)
                                    <option value="{{ $venue->id }}" {{ old('banquet_venue_id', $day->banquet_venue_id) == $venue->id ? 'selected' : '' }}>
                                        {{ $venue->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('banquet_venue_id') 
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="banquet_setup_style_id" class="form-label fw-bold text-charcoal">Setup Style</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-chair text-gold"></i></span>
                            <select name="banquet_setup_style_id" id="banquet_setup_style_id" class="form-select border-start-0 ps-0" required>
                                <option value="">Select Style</option>
                                @foreach($setupStyles as $style)
                                    <option value="{{ $style->id }}" {{ old('banquet_setup_style_id', $day->banquet_setup_style_id) == $style->id ? 'selected' : '' }}>
                                        {{ $style->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        @error('banquet_setup_style_id') 
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Time inputs remain unchanged --}}
                @php
                    $useStandardTiming = substr($day->start_time, 0, 5) === '08:00' && substr($day->end_time, 0, 5) === '20:00';
                    $standardTimingChecked = old('standard_timing') === 'on' || $useStandardTiming;
                @endphp
                <div class="row g-3 mb-4">
                    <div class="col-12">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="standard_timing" 
                                   {{ $standardTimingChecked ? 'checked' : '' }}>
                            <label class="form-check-label text-charcoal" for="standard_timing">
                                Use Standard Timing (8:00 AM - 8:00 PM)
                            </label>
                        </div>
                    </div>
                    
                    <div class="col-md-6">
                        <label for="start_time" class="form-label fw-bold text-charcoal">Start Time</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-clock text-gold"></i></span>
                            <input type="time" name="start_time" id="start_time" class="form-control border-start-0 ps-0" 
                                   value="{{ old('start_time', substr($day->start_time, 0, 5)) }}" required>
                        </div>
                        @error('start_time') 
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="col-md-6">
                        <label for="end_time" class="form-label fw-bold text-charcoal">End Time</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-clock text-gold"></i></span>
                            <input type="time" name="end_time" id="end_time" class="form-control border-start-0 ps-0" 
                                   value="{{ old('end_time', substr($day->end_time, 0, 5)) }}" required>
                        </div>
                        @error('end_time') 
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <div class="d-grid">
                    <button type="submit" class="btn btn-gold btn-lg shadow-sm">
                        <i class="fas fa-save me-2"></i>Update Event Day
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

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
            startTimeInput.classList.add('bg-light');
            endTimeInput.classList.add('bg-light');
        } else {
            startTimeInput.removeAttribute('readonly');
            endTimeInput.removeAttribute('readonly');
            startTimeInput.classList.remove('bg-light');
            endTimeInput.classList.remove('bg-light');
        }
    }

    updateTimeInputs();
    standardTimingCheckbox.addEventListener('change', updateTimeInputs);
    document.getElementById('eventForm').addEventListener('submit', function(e) {
        if (standardTimingCheckbox.checked) {
            startTimeInput.removeAttribute('readonly');
            endTimeInput.removeAttribute('readonly');
        }
    });
});
</script>
@endsection

<style>
    .banquet-theme { font-family: 'Proxima Nova', Arial, sans-serif; }
    .text-gold { color: #C8A165 !important; }
    .text-charcoal { color: #333333 !important; }
    .bg-gold { background-color: #C8A165 !important; }
    .btn-gold { background-color: #C8A165; border-color: #C8A165; color: white; }
    .btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: white; }
    .btn-outline-charcoal { color: #333333; border-color: #333333; }
    .btn-outline-charcoal:hover { background-color: #333333; color: white; }
    .form-control:focus, .form-select:focus { border-color: #C8A165; box-shadow: 0 0 0 0.25rem rgba(200, 161, 101, 0.25); }
</style>
@endsection