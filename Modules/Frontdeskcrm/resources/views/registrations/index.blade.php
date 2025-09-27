@extends('layouts.master')

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0">Registrations</h4>
    <a href="{{ route('frontdesk.registrations.create') }}" class="btn btn-primary">New Registration</a>
</div>

<div class="card shadow-sm">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Guest Name</th>
                        <th>Check-in</th>
                        <th>Room Type</th>
                        <th>Payment</th>
                        <th>Agent</th>
                        <th>Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $reg)
                    <tr>
                        <td>{{ $reg->full_name }}</td>
                        <td>{{ $reg->check_in->format('M d, Y') }}</td>
                        <td>{{ $reg->room_type }}</td>
                        <td>{{ ucfirst($reg->payment_method) }}</td>
                        <td>{{ $reg->front_desk_agent }}</td>
                        <td>{{ $reg->registration_date }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center">No registrations yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $registrations->links() }}
    </div>
</div>
@endsection