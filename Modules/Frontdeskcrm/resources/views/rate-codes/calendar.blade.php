@extends('layouts.master')

@section('title', "Rate Calendar - {$rateCode->name}")
@section('page-content')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Rate Calendar: {{ $rateCode->name }} ({{ $rateCode->code }})</h4>
        <div>
            <a href="{{ route('frontdesk.rate-codes.calendar', [$rateCode, 'year' => $month == 1 ? $year - 1 : $year, 'month' => $month == 1 ? 12 : $month - 1]) }}"
               class="btn btn-outline-secondary btn-sm me-1">&larr; Prev</a>
            <span class="mx-2 fw-semibold">{{ Carbon\Carbon::create($year, $month)->format('F Y') }}</span>
            <a href="{{ route('frontdesk.rate-codes.calendar', [$rateCode, 'year' => $month == 12 ? $year + 1 : $year, 'month' => $month == 12 ? 1 : $month + 1]) }}"
               class="btn btn-outline-secondary btn-sm ms-1">Next &rarr;</a>
            <a href="{{ route('frontdesk.rate-codes.index') }}" class="btn btn-outline-secondary btn-sm ms-3">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            <p class="text-muted mb-3">
                Default rate: <strong>{{ number_format($rateCode->default_rate, 2) }} {{ $rateCode->currency }}</strong>
                &middot; Click a rate to edit it inline.
            </p>

            <form action="{{ route('frontdesk.rate-codes.calendar.update', $rateCode) }}" method="POST">
                @csrf

                <div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="table-light">
                            <tr>
                                <th style="width:40px">#</th>
                                <th style="width:120px">Date</th>
                                <th>Day</th>
                                <th style="width:150px">Rate</th>
                                <th style="width:100px">Available</th>
                                <th style="width:120px">Available Rooms</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php $daysInMonth = Carbon\Carbon::create($year, $month)->daysInMonth; @endphp
                            @for($d = 1; $d <= $daysInMonth; $d++)
                                @php
                                    $date = Carbon\Carbon::create($year, $month, $d);
                                    $key = $date->format('Y-m-d');
                                    $entry = $entries[$key] ?? null;
                                    $isWeekend = $date->isWeekend();
                                @endphp
                                <tr class="{{ $isWeekend ? 'table-light' : '' }}">
                                    <td class="text-muted">{{ $d }}</td>
                                    <td>{{ $date->format('M d, Y') }}</td>
                                    <td>{{ $date->format('D') }}</td>
                                    <td>
                                        <input type="hidden" name="entries[{{ $d }}][date]" value="{{ $key }}">
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">{{ $rateCode->currency }}</span>
                                            <input type="number" step="0.01" name="entries[{{ $d }}][rate]"
                                                   class="form-control form-control-sm"
                                                   value="{{ old('entries.' . $d . '.rate', $entry?->rate ?? '') }}"
                                                   placeholder="{{ number_format($rateCode->default_rate, 2) }}">
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-inline-block">
                                            <input type="checkbox" name="entries[{{ $d }}][is_available]"
                                                   class="form-check-input" value="1"
                                                   {{ ($entry ? $entry->is_available : true) ? 'checked' : '' }}>
                                        </div>
                                        <input type="hidden" name="entries[{{ $d }}][is_available]" value="0">
                                    </td>
                                    <td>
                                        <input type="number" name="entries[{{ $d }}][available_rooms]"
                                               class="form-control form-control-sm"
                                               value="{{ old('entries.' . $d . '.available_rooms', $entry?->available_rooms ?? '') }}"
                                               min="0" placeholder="Unlimited">
                                    </td>
                                </tr>
                            @endfor
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-gold">Save Calendar</button>
                    <a href="{{ route('frontdesk.rate-codes.index') }}" class="btn btn-outline-secondary ms-2">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
