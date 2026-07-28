@extends('layouts.master')

@section('title', 'Arrivals & Departures')
@section('page-content')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Arrivals &amp; Departures</h4>
        <a href="{{ route('frontdesk.reports.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <form method="GET" action="{{ route('frontdesk.reports.arrivals-departures') }}" class="row g-2 mb-4">
        <div class="col-auto">
            <label class="form-label">Start Date</label>
            <input type="date" name="date" class="form-control" value="{{ $date->format('Y-m-d') }}">
        </div>
        <div class="col-auto">
            <label class="form-label">Days Ahead</label>
            <select name="days" class="form-select">
                @foreach([1,3,7,14,30] as $d)
                <option value="{{ $d }}" {{ $days == $d ? 'selected' : '' }}>{{ $d }} days</option>
                @endforeach
            </select>
        </div>
        <div class="col-auto d-flex align-items-end">
            <button type="submit" class="btn btn-primary">Refresh</button>
        </div>
    </form>

    <div class="row g-3">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Expected Arrivals
                        <span class="badge bg-success ms-2">{{ $arrivals->count() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Guest</th>
                                <th>Room Type</th>
                                <th>Source</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($arrivals as $reg)
                            <tr>
                                <td>{{ $reg->check_in->format('M d') }}</td>
                                <td>{{ $reg->guest?->full_name ?? $reg->full_name }}</td>
                                <td>{{ $reg->roomType?->name ?? 'N/A' }}</td>
                                <td>{{ $reg->bookingSource?->name ?? '—' }}</td>
                                <td>
                                    @if($reg->stay_status === 'checked_in')
                                        <span class="badge bg-success">In-House</span>
                                    @elseif($reg->stay_status === 'reserved')
                                        <span class="badge bg-warning">Reserved</span>
                                    @else
                                        <span class="badge bg-secondary">{{ $reg->stay_status }}</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-3 text-muted">No expected arrivals.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-white">
                    <h5 class="mb-0">Expected Departures
                        <span class="badge bg-warning ms-2">{{ $departures->count() }}</span>
                    </h5>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Guest</th>
                                <th>Room</th>
                                <th>Rate Code</th>
                                <th>Nights</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departures as $reg)
                            <tr>
                                <td>{{ $reg->check_out->format('M d') }}</td>
                                <td>{{ $reg->guest?->full_name ?? $reg->full_name }}</td>
                                <td>{{ $reg->roomUnit?->room_number ?? 'N/A' }}</td>
                                <td>{{ $reg->rateCode?->code ?? '—' }}</td>
                                <td>{{ $reg->no_of_nights }}</td>
                            </tr>
                            @empty
                            <tr><td colspan="5" class="text-center py-3 text-muted">No expected departures.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
