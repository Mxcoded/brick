@extends('layouts.master')

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Rate Calendar</h4>
    <div>
        <a href="{{ route('frontdesk.rate-calendar.index', ['month' => $prevMonth, 'rate_code_id' => request('rate_code_id')]) }}" class="btn btn-sm btn-outline-secondary">&laquo; Prev</a>
        <strong class="mx-2">{{ $startDate->format('F Y') }}</strong>
        <a href="{{ route('frontdesk.rate-calendar.index', ['month' => $nextMonth, 'rate_code_id' => request('rate_code_id')]) }}" class="btn btn-sm btn-outline-secondary">Next &raquo;</a>
    </div>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

@php
    $selectedRateCodeId = request('rate_code_id');
@endphp

<ul class="nav nav-tabs mb-3">
    @foreach($rateCodes as $rc)
    <li class="nav-item">
        <a class="nav-link {{ $selectedRateCodeId == $rc->id || (!$selectedRateCodeId && $loop->first) ? 'active' : '' }}"
           href="{{ route('frontdesk.rate-calendar.index', ['month' => $month, 'rate_code_id' => $rc->id]) }}">
            {{ $rc->name }} ({{ $rc->code }})
        </a>
    </li>
    @endforeach
</ul>

@php
    $activeRateCode = $rateCodes->firstWhere('id', $selectedRateCodeId) ?? $rateCodes->first();
@endphp

@if($activeRateCode)
<div class="table-responsive">
    <table class="table table-bordered table-sm rate-calendar-table">
        <thead>
            <tr>
                <th style="min-width: 140px;">Room Type</th>
                @foreach($dates as $date)
                    <th class="text-center {{ $date->isToday() ? 'table-primary' : '' }}" style="min-width: 90px;">
                        <div>{{ $date->format('D') }}</div>
                        <div><strong>{{ $date->format('d') }}</strong></div>
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach($roomTypes as $roomType)
            <tr>
                <td><strong>{{ $roomType->name }}</strong></td>
                @foreach($dates as $date)
                    @php
                        $key = $activeRateCode->id . '_' . $roomType->id . '_' . $date->format('Y-m-d');
                        $entry = $calendarEntries->get($key);
                        $basePrice = $activeRateCode->prices->firstWhere('room_type_id', $roomType->id)?->price;
                        $displayPrice = $entry?->price ?? $basePrice;
                        $isOverridden = $entry && $entry->price !== null;
                    @endphp
                    <td class="text-center align-middle {{ $date->isToday() ? 'table-primary' : '' }} {{ $entry?->stop_sell ? 'table-danger' : '' }}"
                        data-rate-code="{{ $activeRateCode->id }}"
                        data-room-type="{{ $roomType->id }}"
                        data-date="{{ $date->format('Y-m-d') }}"
                        data-base-price="{{ $basePrice }}">
                        <div class="rate-cell-price {{ $isOverridden ? 'text-warning fw-bold' : '' }}">
                            &#8358;{{ number_format($displayPrice) }}
                        </div>
                        <div class="rate-cell-restrictions" style="font-size: 0.7rem;">
                            @if($entry?->min_stay)
                                <span class="badge bg-info">MS{{ $entry->min_stay }}</span>
                            @endif
                            @if($entry?->cta)
                                <span class="badge bg-warning">CTA</span>
                            @endif
                            @if($entry?->ctd)
                                <span class="badge bg-warning">CTD</span>
                            @endif
                            @if($entry?->stop_sell)
                                <span class="badge bg-danger">SOLD</span>
                            @endif
                        </div>
                        <button class="btn btn-xs btn-outline-secondary rate-edit-btn mt-1 py-0 px-1"
                                data-bs-toggle="modal" data-bs-target="#rateEditModal"
                                data-rate-code="{{ $activeRateCode->id }}"
                                data-room-type="{{ $roomType->id }}"
                                data-date="{{ $date->format('Y-m-d') }}"
                                data-price="{{ $entry?->price ?? '' }}"
                                data-min-stay="{{ $entry?->min_stay ?? '' }}"
                                data-cta="{{ $entry?->cta ? '1' : '0' }}"
                                data-ctd="{{ $entry?->ctd ? '1' : '0' }}"
                                data-stop-sell="{{ $entry?->stop_sell ? '1' : '0' }}"
                                title="Edit">
                            <i class="fas fa-pen fa-xs"></i>
                        </button>
                    </td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@else
<div class="alert alert-info">No active rate codes found. <a href="{{ route('frontdesk.rate-codes.create') }}">Create one</a>.</div>
@endif

{{-- Edit Modal --}}
<div class="modal fade" id="rateEditModal" tabindex="-1">
    <div class="modal-dialog">
        <form method="POST" action="{{ route('frontdesk.rate-calendar.update') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title">Edit Rate Cell</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="rate_code_id" id="modal_rate_code_id">
                <input type="hidden" name="room_type_id" id="modal_room_type_id">
                <input type="hidden" name="date" id="modal_date">

                <div class="mb-2">
                    <label class="form-label">Base Price: <span id="modal_base_price" class="text-muted"></span></label>
                </div>

                <div class="mb-3">
                    <label class="form-label">Override Price (&#8358;)</label>
                    <input type="number" step="0.01" class="form-control" name="price" id="modal_price" placeholder="Leave blank to use base price" min="0">
                </div>

                <div class="row mb-3">
                    <div class="col-4">
                        <label class="form-label">Min Stay</label>
                        <input type="number" class="form-control" name="min_stay" id="modal_min_stay" min="0" max="99" placeholder="0">
                    </div>
                    <div class="col-4 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="cta" id="modal_cta" value="1">
                            <label class="form-check-label" for="modal_cta">CTA</label>
                        </div>
                    </div>
                    <div class="col-4 d-flex align-items-end">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="ctd" id="modal_ctd" value="1">
                            <label class="form-check-label" for="modal_ctd">CTD</label>
                        </div>
                    </div>
                </div>

                <div class="form-check mb-3">
                    <input class="form-check-input" type="checkbox" name="stop_sell" id="modal_stop_sell" value="1">
                    <label class="form-check-label text-danger" for="modal_stop_sell">Stop Sell (close this rate)</label>
                </div>

                <hr>
                <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="collapse" data-bs-target="#bulkRange">
                    Bulk Apply to Range
                </button>
                <div class="collapse mt-2" id="bulkRange">
                    <div class="row">
                        <div class="col-6">
                            <label class="form-label">From</label>
                            <input type="date" class="form-control form-control-sm" name="bulk_start" id="bulk_start">
                        </div>
                        <div class="col-6">
                            <label class="form-label">To</label>
                            <input type="date" class="form-control form-control-sm" name="bulk_end" id="bulk_end">
                        </div>
                    </div>
                    <div class="mt-2 text-muted" style="font-size: 0.85rem;">
                        Same price/restrictions will be applied to all dates in range.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary">Save</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('rateEditModal');

    editModal.addEventListener('show.bs.modal', function (event) {
        const btn = event.relatedTarget;

        document.getElementById('modal_rate_code_id').value = btn.dataset.rateCode;
        document.getElementById('modal_room_type_id').value = btn.dataset.roomType;
        document.getElementById('modal_date').value = btn.dataset.date;

        const basePrice = btn.dataset.basePrice || '';
        document.getElementById('modal_base_price').textContent = basePrice ? '₦' + parseFloat(basePrice).toLocaleString() : 'N/A';
        document.getElementById('modal_price').value = btn.dataset.price || '';
        document.getElementById('modal_min_stay').value = btn.dataset.minStay || '';
        document.getElementById('modal_cta').checked = btn.dataset.cta === '1';
        document.getElementById('modal_ctd').checked = btn.dataset.ctd === '1';
        document.getElementById('modal_stop_sell').checked = btn.dataset.stopSell === '1';

        const dateVal = btn.dataset.date;
        document.getElementById('bulk_start').value = dateVal;
        document.getElementById('bulk_end').value = dateVal;
    });

    const form = editModal.querySelector('form');
    const origAction = form.action;

    document.querySelectorAll('[data-bs-target="#bulkRange"]')[0]?.addEventListener('click', function () {
        const bulkStart = document.getElementById('bulk_start').value;
        const bulkEnd = document.getElementById('bulk_end').value;

        if (bulkStart && bulkEnd && bulkStart !== bulkEnd) {
            form.action = '{{ route("frontdesk.rate-calendar.bulk-update") }}';
        } else {
            form.action = origAction;
        }
    });

    form.addEventListener('submit', function () {
        const bulkStart = document.getElementById('bulk_start').value;
        const bulkEnd = document.getElementById('bulk_end').value;

        if (bulkStart && bulkEnd && bulkStart !== bulkEnd) {
            this.action = '{{ route("frontdesk.rate-calendar.bulk-update") }}';
            const startInput = document.createElement('input');
            startInput.type = 'hidden';
            startInput.name = 'start_date';
            startInput.value = bulkStart;
            this.appendChild(startInput);

            const endInput = document.createElement('input');
            endInput.type = 'hidden';
            endInput.name = 'end_date';
            endInput.value = bulkEnd;
            this.appendChild(endInput);
        }
    });
});
</script>
@endpush

@push('styles')
<style>
.rate-calendar-table td {
    vertical-align: middle;
    font-size: 0.85rem;
    padding: 0.25rem 0.4rem;
}
.rate-calendar-table .rate-cell-price {
    line-height: 1.2;
}
.rate-edit-btn {
    opacity: 0.4;
    transition: opacity 0.15s;
}
.rate-calendar-table tr:hover .rate-edit-btn {
    opacity: 1;
}
.rate-calendar-table td.table-danger {
    background-color: #f8d7da;
}
</style>
@endpush
