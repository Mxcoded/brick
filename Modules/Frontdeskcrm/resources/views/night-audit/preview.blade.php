@extends('layouts.master')

@section('title', 'Night Audit Preview')
@section('page-content')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Night Audit Preview</h4>
        <a href="{{ route('frontdesk.night-audit.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <strong>In-House Rooms:</strong>
                    <span class="ms-2">{{ $occupiedCount }}</span>
                </div>
                <div class="col-md-4">
                    <strong>Total Room Revenue to Post:</strong>
                    <span class="ms-2">{{ number_format($totalRevenue, 2) }}</span>
                </div>
                <div class="col-md-4">
                    <strong>Date:</strong>
                    <span class="ms-2">{{ today()->format('M d, Y') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header bg-white">
            <h5 class="mb-0">Rooms to be Charged</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Guest</th>
                        <th>Room</th>
                        <th>Rate Code</th>
                        <th>Nightly Rate</th>
                        <th>Check In</th>
                        <th>Check Out</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($inHouse as $reg)
                    <tr>
                        <td>{{ $reg->guest?->full_name ?? $reg->full_name }}</td>
                        <td>{{ $reg->roomUnit?->room_number ?? 'N/A' }}</td>
                        <td>{{ $reg->rateCode?->code ?? 'Default' }}</td>
                        <td>{{ number_format($reg->room_rate, 2) }}</td>
                        <td>{{ $reg->check_in->format('M d') }}</td>
                        <td>{{ $reg->check_out->format('M d') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center py-4 text-muted">No in-house guests to charge.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        <form action="{{ route('frontdesk.night-audit.run') }}" method="POST"
              onsubmit="return confirm('Run night audit? This will post room charges for all in-house guests.')">
            @csrf
            <input type="hidden" name="date" value="{{ today()->format('Y-m-d') }}">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-moon me-2"></i>Confirm & Run Night Audit
            </button>
        </form>
    </div>
</div>
@endsection
