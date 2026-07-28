@extends('layouts.master')

@section('title', 'Operations Dashboard')
@section('page-content')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-0 fw-bold">Operations Dashboard</h4>
            <small class="text-muted">{{ $today->format('l, F j, Y') }}</small>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('frontdesk.registrations.createWalkin') }}" class="btn btn-success btn-sm">
                <i class="fas fa-user-plus me-1"></i>Walk-In
            </a>
            <a href="{{ route('frontdesk.night-audit.index') }}" class="btn btn-outline-primary btn-sm">
                <i class="fas fa-moon me-1"></i>Night Audit
            </a>
            <a href="{{ route('frontdesk.reports.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-chart-bar me-1"></i>Reports
            </a>
        </div>
    </div>

    {{-- KPI Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <h3 class="mb-0 fw-bold text-primary">{{ $inHouseCount }}</h3>
                    <small class="text-muted">In-House</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <h3 class="mb-0 fw-bold text-success">{{ $arrivalsToday->count() }}</h3>
                    <small class="text-muted">Arrivals</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <h3 class="mb-0 fw-bold text-warning">{{ $dueOutToday->count() }}</h3>
                    <small class="text-muted">Due Out</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <h3 class="mb-0 fw-bold text-success">{{ $availableRooms }}</h3>
                    <small class="text-muted">Available</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <h3 class="mb-0 fw-bold text-info">{{ $occupancyRate }}%</h3>
                    <small class="text-muted">Occupancy</small>
                </div>
            </div>
        </div>
        <div class="col-md-2">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center py-3">
                    <h3 class="mb-0 fw-bold text-danger">{{ $pendingPayments }}</h3>
                    <small class="text-muted">Pending B/L</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Room Inventory Bar --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body py-2">
            <div class="d-flex align-items-center gap-4 flex-wrap">
                <span class="fw-semibold small">Room Inventory ({{ $totalRooms }}):</span>
                <span><span class="badge bg-success">{{ $cleanRooms }}</span> Clean</span>
                <span><span class="badge bg-warning text-dark">{{ $dirtyRooms }}</span> Dirty</span>
                <span><span class="badge bg-primary">{{ $occupiedRooms }}</span> Occupied</span>
                <span><span class="badge bg-secondary">{{ $outOfOrderRooms }}</span> Out of Order</span>
                <span class="ms-auto">
                    <a href="{{ route('frontdesk.rooms.rack') }}" class="text-decoration-none small">Room Rack &rarr;</a>
                </span>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Arrivals --}}
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Today's Arrivals</h5>
                    <span class="badge bg-success">{{ $arrivalsToday->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Guest</th>
                                    <th>Room Type</th>
                                    <th>Source</th>
                                    <th>Status</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($arrivalsToday as $reg)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $reg->full_name }}</div>
                                        @if($reg->email)
                                        <small class="text-muted">{{ $reg->email }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $reg->roomType?->name ?? 'N/A' }}</td>
                                    <td>
                                        @if($reg->booking)
                                            <span class="badge bg-info">Online</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $reg->bookingSource?->name ?? 'Walk-in' }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($reg->stay_status === 'checked_in')
                                            <span class="badge bg-success">Checked In</span>
                                        @elseif($reg->stay_status === 'reserved')
                                            <span class="badge bg-warning text-dark">Reserved</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $reg->stay_status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        @if($reg->stay_status === 'reserved' && $reg->booking)
                                            <a href="{{ route('frontdesk.bookings.checkin', $reg->booking->booking_reference) }}"
                                               class="btn btn-outline-success btn-sm" title="Check In">
                                                <i class="fas fa-sign-in-alt"></i>
                                            </a>
                                        @endif
                                        <a href="{{ route('frontdesk.registrations.show', $reg) }}"
                                           class="btn btn-outline-primary btn-sm" title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-calendar-check fa-2x mb-2 d-block"></i>
                                    No arrivals expected today.
                                </td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Due Out / Departures --}}
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Today's Departures (Due Out)</h5>
                    <span class="badge bg-warning text-dark">{{ $dueOutToday->count() }}</span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Guest</th>
                                    <th>Room</th>
                                    <th>Folio Bal.</th>
                                    <th>Nights</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dueOutToday as $reg)
                                <tr>
                                    <td>
                                        <div class="fw-semibold">{{ $reg->full_name }}</div>
                                        <small class="text-muted">{{ $reg->contact_number ?? '' }}</small>
                                    </td>
                                    <td>{{ $reg->roomUnit?->room_number ?? 'N/A' }}</td>
                                    <td>
                                        @php $bal = $reg->folio?->balance ?? 0; @endphp
                                        @if($bal > 0)
                                            <span class="text-danger fw-semibold">{{ number_format($bal, 2) }}</span>
                                        @elseif($bal < 0)
                                            <span class="text-success fw-semibold">{{ number_format(abs($bal), 2) }} cr</span>
                                        @else
                                            <span class="text-muted">0.00</span>
                                        @endif
                                    </td>
                                    <td>{{ $reg->no_of_nights }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('frontdesk.registrations.show', $reg) }}"
                                           class="btn btn-outline-primary btn-sm" title="View / Checkout">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @empty
                                <tr><td colspan="5" class="text-center py-4 text-muted">
                                    <i class="fas fa-door-open fa-2x mb-2 d-block"></i>
                                    No departures expected today.
                                </td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Recently Checked Out --}}
    @if($checkedOutToday->count())
    <div class="card shadow-sm mt-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <h5 class="mb-0">Checked Out Today</h5>
            <span class="badge bg-secondary">{{ $checkedOutToday->count() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-sm table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Guest</th>
                            <th>Room</th>
                            <th>Checked Out At</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($checkedOutToday as $reg)
                        <tr>
                            <td>{{ $reg->full_name }}</td>
                            <td>{{ $reg->roomUnit?->room_number ?? 'N/A' }}</td>
                            <td>{{ $reg->actual_checkout_at?->format('H:i') ?? 'N/A' }}</td>
                            <td class="text-end">
                                <a href="{{ route('frontdesk.registrations.show', $reg) }}"
                                   class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
