@extends('layouts.master')

@section('title', 'Guest Registrations Dashboard')

@section('page-content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-light p-2 me-3">
                    <i class="fas fa-bed fa-lg text-gold"></i>
                </div>
                <div>
                    <h3 class="mb-1 text-dark fw-bold">Guest Registrations</h3>
                    <p class="text-muted mb-0">Manage and track guest check-ins and stays</p>
                </div>
            </div>
        </div>
        
        <div class="d-flex flex-wrap gap-2">
            <a href="{{ route('frontdesk.registrations.createWalkin') }}" class="btn btn-gold">
                <i class="fas fa-plus me-2"></i> New Walk-in
            </a>
        </div>
    </div>

    {{-- Alerts --}}
    @if (session('success'))
        <div class="alert alert-success border-0 bg-success bg-opacity-10 border-start border-3 border-success rounded-2 mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle text-success me-2"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger border-0 bg-danger bg-opacity-10 border-start border-3 border-danger rounded-2 mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle text-danger me-2"></i>
                {{ session('error') }}
            </div>
        </div>
    @endif

    {{-- Search & Filter Card --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('frontdesk.registrations.index') }}" method="GET" class="row g-3 align-items-end">
                {{-- Search Input --}}
                <div class="col-md-4">
                    <label for="search" class="form-label fw-semibold text-dark">Search Guest</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">
                            <i class="fas fa-search text-muted"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0" 
                               placeholder="Name, Phone, or Email..." value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Status Filter --}}
                <div class="col-md-3">
                    <label for="status" class="form-label fw-semibold text-dark">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="draft_by_guest" @selected(request('status') == 'draft_by_guest')>Pending Finalization</option>
                        <option value="checked_in" @selected(request('status') == 'checked_in')>Checked In</option>
                        <option value="checked_out" @selected(request('status') == 'checked_out')>Checked Out</option>
                        <option value="no_show" @selected(request('status') == 'no_show')>No Show</option>
                    </select>
                </div>

                {{-- Date Filter --}}
                <div class="col-md-3">
                    <label for="date" class="form-label fw-semibold text-dark">Check-in Date</label>
                    <input type="date" name="date" class="form-control" value="{{ request('date') }}">
                </div>

                {{-- Actions --}}
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-dark w-100">
                        <i class="fas fa-filter me-1"></i> Filter
                    </button>
                    <a href="{{ route('frontdesk.registrations.index') }}" class="btn btn-outline-dark" title="Clear">
                        <i class="fas fa-undo"></i>
                    </a>
                </div>
            </form>
        </div>
    </div>

    {{-- Registrations Table Card --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 py-3 ps-4">Guest</th>
                            <th class="border-0 py-3">Contact</th>
                            <th class="border-0 py-3">Stay Dates</th>
                            <th class="border-0 py-3">Status</th>
                            <th class="border-0 py-3 pe-4 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($registrations as $reg)
                        <tr class="{{ $reg->stay_status === 'draft_by_guest' ? 'bg-warning bg-opacity-10' : '' }}">
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-light p-1 me-3">
                                        <i class="fas {{ $reg->is_group_lead ? 'fa-user-tie' : 'fa-user' }} fa-sm text-gold"></i>
                                    </div>
                                    <div>
                                        <strong class="d-block text-dark">{{ $reg->guest->full_name ?? $reg->full_name }}</strong>
                                        @if($reg->is_group_lead)
                                            <span class="badge bg-secondary">Group Lead</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="d-flex align-items-center">
                                    <i class="fas fa-phone text-muted me-2"></i>
                                    <span class="text-dark">{{ $reg->guest->contact_number ?? $reg->contact_number }}</span>
                                </div>
                            </td>
                            <td>
                                @if($reg->check_in && $reg->check_out)
                                    <div class="d-flex align-items-center">
                                        <i class="fas fa-calendar text-muted me-2"></i>
                                        <div>
                                            <span class="d-block text-dark">{{ $reg->check_in->format('M d, Y') }} → {{ $reg->check_out->format('M d, Y') }}</span>
                                            <small class="text-muted">{{ $reg->no_of_nights }} nights</small>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-muted">Dates TBD</span>
                                @endif
                            </td>
                            <td>
                                @if($reg->stay_status === 'draft_by_guest')
                                    <span class="badge bg-warning text-dark">
                                        <i class="fas fa-clock me-1"></i> Pending Finalization
                                    </span>
                                @elseif($reg->stay_status === 'checked_in')
                                    <span class="badge bg-info">
                                        <i class="fas fa-check-circle me-1"></i> Checked-In
                                    </span>
                                @else
                                    <span class="badge bg-success">
                                        {{ ucfirst(str_replace('_', ' ', $reg->stay_status)) }}
                                    </span>
                                @endif
                            </td>
                            <td class="pe-4 text-end">
                                @if($reg->stay_status === 'draft_by_guest')
                                    <div class="d-flex justify-content-end gap-2">
                                        <a href="{{ route('frontdesk.registrations.finalize.form', $reg) }}" class="btn btn-gold btn-sm">
                                            <i class="fas fa-check-double me-1"></i> Finalize
                                        </a>
                                        <form action="{{ route('frontdesk.registrations.destroy', $reg) }}" method="POST" class="d-inline"
                                            onsubmit="return confirm('Are you sure you want to delete this draft? This cannot be undone.');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                @else
                                    <a href="{{ route('frontdesk.registrations.show', $reg) }}" class="btn btn-outline-dark btn-sm">
                                        <i class="fas fa-eye me-1"></i> View
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5">
                                <div class="py-4">
                                    <i class="fas fa-bed fa-3x text-muted mb-3"></i>
                                    <h5 class="text-dark mb-2">No registrations found</h5>
                                    <p class="text-muted">Try adjusting your search or filter criteria</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            {{-- Pagination --}}
            @if($registrations->hasPages())
                <div class="card-footer border-0 bg-white py-3">
                    <div class="d-flex justify-content-center">
                        {{ $registrations->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection