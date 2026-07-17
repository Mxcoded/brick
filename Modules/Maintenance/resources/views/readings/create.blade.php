@extends('layouts.master')

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold text-charcoal mb-1"><i class="fas fa-clipboard-list me-2 text-gold"></i>Daily Readings</h3>
        <p class="text-muted mb-0">Log generator, diesel, water, and cold room readings</p>
    </div>
    <a href="{{ route('maintenance.readings.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-chart-bar me-1"></i> View Report</a>
</div>

@if ($errors->any())
<div class="alert alert-danger alert-dismissible fade show">
    <strong>Please fix the following errors:</strong>
    <ul class="mb-0 mt-2">
        @foreach ($errors->all() as $error)
        <li>{{ $error }}</li>
        @endforeach
    </ul>
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<form method="POST" action="{{ route('maintenance.readings.store') }}" class="mb-4">
    @csrf
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <label class="form-label fw-semibold small">Reading Date</label>
            <input type="date" name="reading_date" class="form-control" value="{{ $date }}" max="{{ now()->toDateString() }}" onchange="window.location='{{ route('maintenance.readings.create') }}?date='+encodeURIComponent(this.value)">
        </div>
    </div>

    <div class="row g-3">
        {{-- ══════ GENERATOR SECTION ══════ --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" title="Log the percentage reading from each generator screen. The diesel remaining is auto-calculated based on the generator's capacity.">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-bolt me-1 text-gold"></i>Generators</h6>
                </div>
                <div class="card-body">
                    @foreach(\Modules\Maintenance\Models\MaintenanceReading::GENERATORS as $key => $gen)
                    @php
                        $existingKey = "generator.{$key}";
                        $prev = ($existing[$existingKey] ?? collect())->first();
                    @endphp
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="fw-semibold small">{{ $gen['label'] }}</span>
                            <span class="small text-muted">Capacity: {{ $gen['capacity'] }}</span>
                        </div>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="small text-muted">Screen Reading (%)</label>
                                <input type="number" step="0.1" min="0" max="100"
                                    name="readings[{{ $key }}][reading_value]"
                                    class="form-control form-control-sm gen-pct"
                                    data-capacity="{{ $gen['capacity'] }}"
                                    data-target="gen-{{ $key }}"
                                    value="{{ old("readings.{$key}.reading_value", $prev?->reading_value) }}"
                                    required>
                            </div>
                            <div class="col-6">
                                <label class="small text-muted">Diesel Remaining (calc)</label>
                                <input type="text" class="form-control form-control-sm bg-light"
                                    id="gen-{{ $key }}"
                                    value="{{ $prev?->calculated_value ? number_format($prev->calculated_value, 2) : '' }}"
                                    readonly>
                            </div>
                        </div>
                        <input type="hidden" name="readings[{{ $key }}][reading_type]" value="generator">
                        <input type="hidden" name="readings[{{ $key }}][category]" value="{{ $key }}">
                        <input type="hidden" name="readings[{{ $key }}][capacity]" value="{{ $gen['capacity'] }}">
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ══════ DIESEL RESERVOIR ══════ --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" title="Enter the analog gauge reading from the main diesel reservoir tank in litres. Litres recorded auto-fills based on your entry.">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-oil-can me-1 text-gold"></i>Diesel Reservoir</h6>
                </div>
                <div class="card-body">
                    @php $reservoir = ($existing['diesel_reservoir.'] ?? collect())->first(); @endphp
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="small text-muted">Analog Reading (Litres)</label>
                            <input type="number" step="1" min="0"
                                name="readings[reservoir][reading_value]"
                                class="form-control form-control-sm res-ltr"
                                data-target="res-ltr-calc"
                                value="{{ old('readings.reservoir.reading_value', $reservoir?->reading_value) }}"
                                required>
                        </div>
                        <div class="col-6">
                            <label class="small text-muted">Litres Recorded (auto)</label>
                            <input type="text" class="form-control form-control-sm bg-light"
                                id="res-ltr-calc"
                                value="{{ $reservoir?->reading_value ? number_format($reservoir->reading_value) : '' }}"
                                readonly>
                        </div>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="small text-muted">Tank Capacity (optional)</label>
                            <input type="number" name="readings[reservoir][capacity]" class="form-control form-control-sm"
                                placeholder="e.g. 5000"
                                value="{{ old('readings.reservoir.capacity', $reservoir?->capacity) }}">
                        </div>
                        <div class="col-6">
                            <label class="small text-muted">Capacity Used</label>
                            <input type="text" class="form-control form-control-sm bg-light"
                                id="res-pct-calc"
                                value="{{ $reservoir?->capacity && $reservoir->reading_value ? number_format(($reservoir->reading_value / $reservoir->capacity) * 100, 1) . '%' : '' }}"
                                readonly>
                        </div>
                    </div>
                    <input type="hidden" name="readings[reservoir][reading_type]" value="diesel_reservoir">
                    <input type="hidden" name="readings[reservoir][category]" value="">
                </div>
            </div>
        </div>

        {{-- ══════ WATER TANK ══════ --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" title="Log the assumed water level in the hotel's main water tank. This is an estimation based on visual inspection.">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-water me-1 text-gold"></i>Water Tank</h6>
                </div>
                <div class="card-body">
                    @php $water = ($existing['water_tank.'] ?? collect())->first(); @endphp
                    <div class="mb-2">
                        <label class="small text-muted">Assumed Level (%)</label>
                        <input type="number" step="1" min="0" max="100"
                            name="readings[water][reading_value]"
                            class="form-control"
                            value="{{ old('readings.water.reading_value', $water?->reading_value) }}"
                            required>
                        <small class="text-muted">0% = empty, 100% = full</small>
                    </div>
                    <div class="mb-2">
                        <label class="small text-muted">Notes (optional)</label>
                        <input type="text" name="readings[water][notes]" class="form-control form-control-sm"
                            value="{{ old('readings.water.notes', $water?->notes) }}"
                            placeholder="e.g. pumping started at 10am">
                    </div>
                    <input type="hidden" name="readings[water][reading_type]" value="water_tank">
                    <input type="hidden" name="readings[water][category]" value="">
                </div>
            </div>
        </div>

        {{-- ══════ COLD ROOM ══════ --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100" title="Log the temperature readings for the kitchen freezer(s) and fridge(s). Track to ensure food safety compliance.">
                <div class="card-header bg-white py-2">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-snowflake me-1 text-gold"></i>Cold Room</h6>
                </div>
                <div class="card-body">
                    @foreach(\Modules\Maintenance\Models\MaintenanceReading::COLD_ROOM_TYPES as $key => $label)
                    @php
                        $existingKey = "cold_room.{$key}";
                        $prev = ($existing[$existingKey] ?? collect())->first();
                    @endphp
                    <div class="mb-2">
                        <label class="small text-muted">{{ $label }} Temperature (&deg;C)</label>
                        <input type="number" step="0.1"
                            name="readings[{{ $key }}][reading_value]"
                            class="form-control"
                            value="{{ old("readings.{$key}.reading_value", $prev?->reading_value) }}"
                            required>
                        <input type="hidden" name="readings[{{ $key }}][reading_type]" value="cold_room">
                        <input type="hidden" name="readings[{{ $key }}][category]" value="{{ $key }}">
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <div class="mt-4 d-flex gap-2">
        <button type="submit" class="btn btn-gold px-4"><i class="fas fa-save me-1"></i> Save Readings</button>
        <a href="{{ route('maintenance.readings.index') }}" class="btn btn-outline-secondary">Cancel</a>
    </div>
</form>

@section('page-scripts')
<script>
document.addEventListener('input', function(e) {
    // Generator calculation
    if (e.target.matches('.gen-pct')) {
        var pct = parseFloat(e.target.value) || 0;
        var capacity = parseFloat(e.target.dataset.capacity) || 0;
        var calc = (pct / 100) * capacity;
        document.getElementById(e.target.dataset.target).value = calc.toFixed(2);
    }
    // Reservoir calculation
    if (e.target.matches('.res-ltr')) {
        var ltr = parseFloat(e.target.value) || 0;
        var capacityField = e.target.closest('.card-body').querySelector('input[name$="[capacity]"]');
        var capacity = parseFloat(capacityField ? capacityField.value : 0) || 0;
        document.getElementById(e.target.dataset.target).value = ltr.toLocaleString();
        var pctField = document.getElementById('res-pct-calc');
        if (capacity > 0) {
            pctField.value = ((ltr / capacity) * 100).toFixed(1) + '%';
        } else {
            pctField.value = '';
        }
    }
    // Recalc reservoir when capacity changes
    if (e.target.matches('input[name$="[capacity]"]') && e.target.closest('.card-body').querySelector('.res-ltr')) {
        var ltrInput = e.target.closest('.card-body').querySelector('.res-ltr');
        var ltr = parseFloat(ltrInput.value) || 0;
        var capacity = parseFloat(e.target.value) || 0;
        var pctField = document.getElementById('res-pct-calc');
        if (capacity > 0 && ltr > 0) {
            pctField.value = ((ltr / capacity) * 100).toFixed(1) + '%';
        } else {
            pctField.value = '';
        }
    }

});
</script>
@endsection
@endsection
