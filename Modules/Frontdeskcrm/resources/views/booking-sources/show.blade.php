@extends('layouts.master')

@section('page-content')
<div class="card shadow-sm">
    <div class="card-header">
        <h4>{{ $bookingSource->name }}</h4>
    </div>
    <div class="card-body">
        <p><strong>Description:</strong> {{ $bookingSource->description }}</p>
        <p><strong>Type:</strong> {{ ucfirst($bookingSource->type ?? 'General') }}</p>
        <p><strong>Commission:</strong> {{ $bookingSource->commission_rate }}%</p>
        <p><strong>Active:</strong> {{ $bookingSource->is_active ? 'Yes' : 'No' }}</p>
        <p><strong>Total Revenue:</strong> &#8358;{{ number_format($bookingSource->total_revenue, 2) }}</p>

        <h6 class="mt-4">Linked Registrations ({{ $bookingSource->registrations->count() }})</h6>
        <table class="table">
            <thead><tr><th>Guest</th><th>Check-in</th><th>Total</th></tr></thead>
            <tbody>
                @forelse($bookingSource->registrations as $reg)
                    <tr><td>{{ $reg->guest?->full_name }}</td><td>{{ $reg->check_in->format('M d, Y') }}</td><td>${{ number_format($reg->total_amount, 2) }}</td></tr>
                @empty
                    <tr><td colspan="3">No bookings.</td></tr>
                @endforelse
            </tbody>
        </table>

        <a href="{{ route('frontdesk.booking-sources.edit', $bookingSource) }}" class="btn btn-warning">Edit</a>
        <a href="{{ route('frontdesk.booking-sources.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>
@endsection