@extends('layouts.master')

@section('page-content')
<div class="card shadow-sm">
    <div class="card-header">
        <h4>{{ $guestType->name }}</h4>
    </div>
    <div class="card-body">
        <p><strong>Description:</strong> {{ $guestType->description }}</p>
        <p><strong>Color:</strong> <span class="badge" style="background-color: {{ $guestType->color }}; color: white;">Sample Badge</span></p>
        <p><strong>Discount:</strong> {{ $guestType->discount_rate }}%</p>
        <p><strong>Active:</strong> {{ $guestType->is_active ? 'Yes' : 'No' }}</p>
        <p><strong>Total Revenue:</strong> &#8358;{{ number_format($guestType->total_revenue, 2) }}</p>

        <h6 class="mt-4">Linked Registrations ({{ $guestType->registrations->count() }})</h6>
        <div class="table-responsive">
            <table class="table">
                <thead><tr><th>Guest</th><th>Check-in</th><th>Room Rate</th><th>Total</th></tr></thead>
                <tbody>
                    @forelse($guestType->registrations as $reg)
                    <tr>
                        <td>{{ $reg->guest?->full_name }}</td>
                        <td>{{ $reg->check_in->format('M d, Y') }}</td>
                        <td>&#8358;{{ number_format($reg->room_rate, 2) }}</td>
                        <td>&#8358;{{ number_format($reg->total_amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4">No registrations.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <a href="{{ route('frontdesk.guest-types.edit', $guestType) }}" class="btn btn-warning">Edit</a>
        <a href="{{ route('frontdesk.guest-types.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>
@endsection