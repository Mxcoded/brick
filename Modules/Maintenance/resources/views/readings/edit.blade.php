@extends('layouts.master')

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-charcoal mb-1"><i class="fas fa-edit me-2 text-gold"></i>Edit Reading</h3>
        <p class="text-muted mb-0">{{ $reading->reading_date->format('M d, Y') }} &middot; {{ \Modules\Maintenance\Models\MaintenanceReading::TYPES[$reading->reading_type] ?? $reading->reading_type }} {{ $reading->category ? '- '.ucfirst($reading->category) : '' }}</p>
    </div>
    <a href="{{ route('maintenance.readings.show', $reading->reading_date->toDateString()) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
</div>

<form method="POST" action="{{ route('maintenance.readings.update', $reading->id) }}">
    @csrf
    @method('PUT')
    <div class="row">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Reading Value</label>
                        @if($reading->reading_type === 'cold_room')
                        <input type="number" step="0.1" name="reading_value" class="form-control" value="{{ old('reading_value', $reading->reading_value) }}" required>
                        <small class="text-muted">Temperature in &deg;C</small>
                        @elseif($reading->reading_type === 'diesel_reservoir')
                        <input type="number" step="1" min="0" name="reading_value" class="form-control" value="{{ old('reading_value', $reading->reading_value) }}" required>
                        <small class="text-muted">Litres on analog gauge</small>
                        @else
                        <input type="number" step="0.1" min="0" max="100" name="reading_value" class="form-control" value="{{ old('reading_value', $reading->reading_value) }}" required>
                        <small class="text-muted">Percentage (%)</small>
                        @endif
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold small">Notes</label>
                        <input type="text" name="notes" class="form-control" value="{{ old('notes', $reading->notes) }}" maxlength="500">
                    </div>
                    <button type="submit" class="btn btn-gold"><i class="fas fa-save me-1"></i> Update</button>
                </div>
            </div>
        </div>
    </div>
</form>
<style>
    .card { border-radius: 10px; }
</style>
@endsection
