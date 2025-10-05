@extends('layouts.master')

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Guest Registrations</h4>
    {{-- CRITICAL FIX: Link Agent to the explicit agent check-in form --}}
    <a href="{{ route('frontdesk.registrations.agent-checkin') }}" class="btn btn-primary">New Registration</a>
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
                        {{-- FIX: Use correct badge for draft status --}}
                        <td><span class="badge {{ $reg->stay_status == 'checked_in' ? 'bg-info' : ($reg->stay_status == 'draft_by_guest' ? 'bg-warning text-dark' : 'bg-success') }}">{{ ucfirst(str_replace('_', ' ', $reg->stay_status)) }}</span></td>
                        <td>&#8358;{{ number_format($reg->total_amount ?? ($reg->room_rate * $reg->no_of_nights), 2) }}</td>
                        <td>
                            <a href="{{ route('frontdesk.registrations.show', $reg) }}" class="btn btn-sm btn-info">View</a>
                            
                            @if($reg->stay_status === 'draft_by_guest')
                                {{-- Link to Agent Finalization form --}}
                                <a href="{{ route('frontdesk.registrations.finish-draft.form', $reg) }}" class="btn btn-sm btn-warning">Finalize Draft</a>
                            @else
                                @if($reg->is_group_lead)
                                    <a href="{{ route('frontdesk.registrations.add-member.form', $reg) }}" class="btn btn-sm btn-secondary">Add Member</a>
                                @endif
                                <a href="{{ route('frontdesk.registrations.print', $reg) }}" class="btn btn-sm btn-success" target="_blank">Print</a>
                            @endif
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
