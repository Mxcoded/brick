@extends('layouts.master')

@section('title', 'Guest Registrations Dashboard')

@section('page-content')
<div class="container-fluid my-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-bed me-2"></i>Guest Registrations</h3>
        <a href="{{ route('frontdesk.registrations.create') }}" class="btn btn-primary">
            <i class="fas fa-user-plus me-2"></i>New Check-In
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card shadow-sm">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Guest</th>
                            <th>Contact</th>
                            <th>Stay Dates</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registrations as $reg)
                        <tr class="{{ $reg->stay_status === 'draft_by_guest' ? 'table-warning' : '' }}">
                            <td>
                                <strong>{{ $reg->guest->full_name ?? $reg->full_name }}</strong>
                                @if($reg->is_group_lead)
                                    <span class="badge bg-secondary ms-2">Group Lead</span>
                                @endif
                            </td>
                            <td>{{ $reg->guest->contact_number ?? $reg->contact_number }}</td>
                            <td>
                                @if($reg->check_in && $reg->check_out)
                                    {{ $reg->check_in->format('M d, Y') }} &rarr; {{ $reg->check_out->format('M d, Y') }} ({{ $reg->no_of_nights }} nights)
                                @else
                                    <span class="text-muted">Dates TBD</span>
                                @endif
                            </td>
                            <td>
                                @if($reg->stay_status === 'draft_by_guest')
                                    <span class="badge bg-warning text-dark">Pending Finalization</span>
                                @elseif($reg->stay_status === 'checked_in')
                                    <span class="badge bg-info">Checked-In</span>
                                @else
                                    <span class="badge bg-success">{{ ucfirst(str_replace('_', ' ', $reg->stay_status)) }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                {{-- **CRITICAL FIX**: Conditional Action Buttons --}}
                                @if($reg->stay_status === 'draft_by_guest')
                                    <a href="{{ route('frontdesk.registrations.finalize.form', $reg) }}" class="btn btn-warning btn-sm">
                                        <i class="fas fa-check-double me-1"></i> Finalize
                                    </a>
                                @else
                                    <a href="{{ route('frontdesk.registrations.show', $reg) }}" class="btn btn-info btn-sm">
                                        <i class="fas fa-eye me-1"></i> View
                                    </a>
                                    <a href="{{ route('frontdesk.registrations.print', $reg) }}" class="btn btn-secondary btn-sm" target="_blank">
                                        <i class="fas fa-print me-1"></i> Print
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-4">No registrations found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-3">
                {{ $registrations->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

