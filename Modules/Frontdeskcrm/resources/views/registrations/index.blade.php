@extends('layouts.master')

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Guest Registrations</h4>
    <a href="{{ route('frontdesk.registrations.create') }}" class="btn btn-primary">New Registration</a>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif

<div class="card shadow-sm" style="background: var(--glass-effect); border: 1px solid var(--glass-border);">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Guest</th>
                        <th>Room Type</th>
                        <th>Check-in / Nights</th>
                        <th>Type</th>
                        <th>Source</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $reg)
                    <tr>
                        <td>{{ $reg->guest?->full_name ?? $reg->full_name }}<br><small>{{ $reg->contact_number }}</small></td>
                        <td>{{ $reg->room_type }}</td>
                        <td>{{ $reg->check_in->format('M d') }} / {{ $reg->no_of_nights }} nights</td>
                        <td><span class="badge" style="background-color: {{ $reg->guestType?->color ?? '#6c757d' }}">{{ $reg->guestType?->name ?? 'Other' }}</span></td>
                        <td>{{ $reg->bookingSource?->name ?? 'Direct' }}</td>
                        <td><span class="badge {{ $reg->stay_status == 'checked_in' ? 'bg-info' : 'bg-success' }}">{{ ucfirst($reg->stay_status) }}</span></td>
                        <td>&#8358;{{ number_format($reg->total_amount ?? ($reg->room_rate * $reg->no_of_nights), 2) }}</td>
                        <td>
                            <a href="{{ route('frontdesk.registrations.show', $reg) }}" class="btn btn-sm btn-info">View</a>
                            @if($reg->is_group_lead)
                                <a href="{{ route('frontdesk.registrations.add-member.form', $reg) }}" class="btn btn-sm btn-secondary">Add Member</a>
                            @endif
                            <a href="{{ route('frontdesk.registrations.print', $reg) }}" class="btn btn-sm btn-success" target="_blank">Print</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="8" class="text-center">No registrations yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $registrations->links() }}
    </div>
</div>
@endsection