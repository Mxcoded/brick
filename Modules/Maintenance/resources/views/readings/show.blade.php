@extends('layouts.master')

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-charcoal mb-1"><i class="fas fa-calendar-day me-2 text-gold"></i>Readings for {{ \Carbon\Carbon::parse($date)->format('l, F d, Y') }}</h3>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('maintenance.readings.create', ['date' => $date]) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit me-1"></i> Edit Day</a>
        <a href="{{ route('maintenance.readings.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i> Back</a>
    </div>
</div>

<div class="row g-3">
    @foreach($readings->groupBy('reading_type') as $type => $items)
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-2">
                <h6 class="mb-0 fw-bold">
                    <i class="fas fa-{{ $type === 'generator' ? 'bolt' : ($type === 'diesel_reservoir' ? 'oil-can' : ($type === 'water_tank' ? 'water' : 'snowflake')) }} me-1 text-gold"></i>
                    {{ \Modules\Maintenance\Models\MaintenanceReading::TYPES[$type] ?? $type }}
                </h6>
            </div>
            <div class="card-body">
                @foreach($items as $reading)
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <div>
                        <span class="fw-semibold small">{{ $reading->category ? ucfirst(str_replace('_', ' ', $reading->category)) : 'Level' }}</span>
                        @if($reading->notes)
                        <br><small class="text-muted">{{ $reading->notes }}</small>
                        @endif
                    </div>
                    <div class="text-end">
                        @if($type === 'cold_room')
                        <span class="fw-bold fs-5">{{ number_format($reading->reading_value, 1) }}&deg;C</span>
                        @elseif($type === 'diesel_reservoir')
                        <span class="fw-bold fs-5">{{ number_format($reading->reading_value, 0) }}L</span>
                        @else
                        <span class="fw-bold fs-5">{{ number_format($reading->reading_value, 1) }}%</span>
                        @if($reading->calculated_value && $type !== 'diesel_reservoir')
                        <br><small class="text-muted">{{ number_format($reading->calculated_value, $type === 'generator' ? 2 : 0) }} units</small>
                        @endif
                        @endif
                    </div>
                </div>
                @if(! $loop->last)<hr class="my-2">@endif
                @endforeach
                <div class="mt-2 small text-muted">
                    Recorded by {{ $items->first()->recorder?->name ?? 'N/A' }} at {{ $items->first()->created_at->format('h:i A') }}
                </div>
            </div>
        </div>
    </div>
    @endforeach
</div>
<style>
    .card { border-radius: 10px; }
    .card-header { border-radius: 10px 10px 0 0 !important; border-bottom: 2px solid #f0f0f0; }
</style>
@endsection
