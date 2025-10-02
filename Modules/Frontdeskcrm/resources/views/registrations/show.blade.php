@extends('layouts.master')

@section('page-content')
<div class="card shadow-sm">
    <div class="card-header">
        <h4>Registration #{{ $registration->id }} - {{ $registration->guest?->full_name ?? $registration->full_name }}</h4>
    </div>
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h6>Guest Details</h6>
                <p><strong>Name:</strong> {{ $registration->guest?->full_name ?? $registration->full_name }}</p>
                <p><strong>Email:</strong> {{ $registration->guest?->email ?? $registration->email }}</p>
                <p><strong>Type:</strong> <span class="badge" style="background-color: {{ $registration->guestType?->color }}">{{ $registration->guestType?->name }}</span></p>
                {{-- More: nationality, contact, etc. --}}
            </div>
            <div class="col-md-6">
                <h6>Booking</h6>
                <p><strong>Room:</strong> {{ $registration->room_type }} @ &#8358;{{ number_format($registration->room_rate, 2) }}</p>
                <p><strong>Check-in/Out:</strong> {{ $registration->check_in->format('M d, Y') }} to {{ $registration->check_out->format('M d, Y') }} ({{ $registration->no_of_nights }} nights)</p>
                <p><strong>Total:</strong> &#8358;{{ number_format($registration->total_amount ?? ($registration->room_rate * $registration->no_of_nights), 2) }}</p>
                <p><strong>Status:</strong> <span class="badge {{ $registration->stay_status == 'checked_in' ? 'bg-info' : 'bg-success' }}">{{ ucfirst($registration->stay_status) }}</span></p>
            </div>
        </div>

        @if($registration->is_group_lead && $registration->groupMembers->count() > 0)
            <h6 class="mt-4">Group Members ({{ $registration->groupMembers->count() }})</h6>
            <table class="table">
                <thead><tr><th>Name</th><th>Contact</th><th>Room</th></tr></thead>
                <tbody>
                    @foreach($registration->groupMembers as $member)
                        <tr><td>{{ $member->full_name }}</td><td>{{ $member->contact_number }}</td><td>{{ $member->room_assignment ?? 'TBD' }}</td></tr>
                    @endforeach
                </tbody>
            </table>
        @endif

        <a href="{{ route('frontdesk.registrations.index') }}" class="btn btn-secondary">Back</a>
    </div>
</div>
@endsection