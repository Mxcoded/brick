@extends('layouts.master')

@section('page-content')
<div class="row">
    <div class="col-lg-8">
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between align-items-center">
                    <h4 class="mb-0">{{ $guestType->name }}</h4>
                    <span class="badge" style="background-color: {{ $guestType->color }}; color: white;">Sample Badge</span>
                </div>
            </div>
            <div class="card-body">
                <p><strong>Description:</strong> {{ $guestType->description ?: 'N/A' }}</p>
                <p><strong>Discount Rate:</strong> {{ $guestType->discount_rate }}%</p>
                <p><strong>Active:</strong> {{ $guestType->is_active ? 'Yes' : 'No' }}</p>
                <p><strong>Contract Period:</strong>
                    @if($guestType->valid_from || $guestType->valid_to)
                        {{ $guestType->valid_from ? $guestType->valid_from->format('M d, Y') : 'No start' }}
                        &mdash;
                        {{ $guestType->valid_to ? $guestType->valid_to->format('M d, Y') : 'No end' }}
                        @if($guestType->isValidNow())
                            <span class="badge bg-success">Active Contract</span>
                        @else
                            <span class="badge bg-secondary">Expired/Not Started</span>
                        @endif
                    @else
                        No contract period set
                    @endif
                </p>
                <p><strong>Total Revenue:</strong> ₦{{ number_format($guestType->total_revenue, 2) }}</p>
            </div>
        </div>

        {{-- Linked Registrations --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">Linked Registrations ({{ $guestType->registrations->count() }})</h5>
            </div>
            <div class="card-body">
                @if($guestType->registrations->isEmpty())
                    <p class="text-muted">No registrations yet.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>#</th><th>Guest</th><th>Check-In</th><th>Room Rate</th></tr>
                            </thead>
                            <tbody>
                                @foreach($guestType->registrations as $reg)
                                    <tr>
                                        <td>{{ $reg->id }}</td>
                                        <td>{{ $reg->guest?->full_name ?? 'N/A' }}</td>
                                        <td>{{ $reg->check_in?->format('M d, Y') ?? 'N/A' }}</td>
                                        <td>₦{{ number_format($reg->room_rate, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        {{-- Negotiated Rates --}}
        <div class="card shadow-sm mb-4">
            <div class="card-header">
                <h5 class="mb-0">Negotiated Rates</h5>
            </div>
            <div class="card-body">
                @if($guestType->rates->isEmpty())
                    <p class="text-muted">No negotiated rates. Discount rate ({{ $guestType->discount_rate }}%) will be used as fallback.</p>
                @else
                    <div class="table-responsive">
                        <table class="table table-sm">
                            <thead>
                                <tr><th>Room Type</th><th>Rate/Night</th><th>Valid</th><th></th></tr>
                            </thead>
                            <tbody>
                                @foreach($guestType->rates as $rate)
                                    <tr>
                                        <td>{{ $rate->roomType?->name ?? 'N/A' }}</td>
                                        <td>₦{{ number_format($rate->rate) }}</td>
                                        <td class="small">
                                            {{ $rate->valid_from ? $rate->valid_from->format('M d') : '∞' }}
                                            &mdash;
                                            {{ $rate->valid_to ? $rate->valid_to->format('M d, Y') : '∞' }}
                                        </td>
                                        <td>
                                            <form action="{{ route('frontdesk.guest-types.destroy-rate', [$guestType, $rate]) }}" method="POST" class="d-inline"
                                                onsubmit="return confirm('Remove this rate?')">
                                                @csrf @method('DELETE')
                                                <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <hr>
                <h6 class="fw-bold">Add Negotiated Rate</h6>
                <form action="{{ route('frontdesk.guest-types.store-rate', $guestType) }}" method="POST">
                    @csrf
                    <div class="mb-2">
                        <select name="room_type_id" class="form-select form-select-sm" required>
                            <option value="">Select Room Type</option>
                            @foreach($roomTypes as $rt)
                                <option value="{{ $rt->id }}">{{ $rt->name }} (₦{{ number_format($rt->price) }}/night)</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-2">
                        <input type="number" name="rate" class="form-control form-control-sm" placeholder="Negotiated rate per night" required min="0" step="100">
                    </div>
                    <div class="row mb-2">
                        <div class="col">
                            <input type="date" name="valid_from" class="form-control form-control-sm" placeholder="Start">
                        </div>
                        <div class="col">
                            <input type="date" name="valid_to" class="form-control form-control-sm" placeholder="End">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary w-100">Add Rate</button>
                </form>
            </div>
        </div>

        <a href="{{ route('frontdesk.guest-types.edit', $guestType) }}" class="btn btn-warning w-100 mb-2">Edit Guest Type</a>
        <a href="{{ route('frontdesk.guest-types.index') }}" class="btn btn-secondary w-100">Back to List</a>
    </div>
</div>
@endsection
