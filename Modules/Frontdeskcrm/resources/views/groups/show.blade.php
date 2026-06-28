@extends('layouts.master')

@section('title', 'Group: ' . $registration->full_name)

@section('page-content')
<div class="container-fluid py-4">
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1"><i class="fas fa-users text-gold me-2"></i>{{ $registration->full_name }}</h4>
            <p class="text-muted mb-0">
                <code>{{ $registration->reservation_code ?? 'No reference' }}</code>
                &middot; {{ $members->count() }} member(s)
                &middot; {{ $registration->check_in?->format('M d, Y') }} - {{ $registration->check_out?->format('M d, Y') }}
            </p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('frontdesk.registrations.show', $registration) }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Lead Stay
            </a>
            @if($registration->stay_status === 'checked_in')
            <form action="{{ route('frontdesk.groups.bulk-checkin', $registration) }}" method="POST" class="d-inline"
                onsubmit="return confirm('Check in all reserved/draft members?');">
                @csrf
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-sign-in-alt me-1"></i> Check In All
                </button>
            </form>
            <form action="{{ route('frontdesk.groups.bulk-checkout', $registration) }}" method="POST" class="d-inline"
                onsubmit="return confirm('Check out ALL checked-in members (including lead)?');">
                @csrf
                <input type="hidden" name="checkout_lead" value="1">
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-sign-out-alt me-1"></i> Check Out All
                </button>
            </form>
            @endif
        </div>
    </div>

    {{-- Financial Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <small class="text-muted">Lead Charges</small>
                    <h5 class="fw-bold mb-0">₦{{ number_format($financialSummary['lead_total'], 2) }}</h5>
                    <small class="text-success">Paid: ₦{{ number_format($financialSummary['lead_paid'], 2) }}</small>
                    @if($financialSummary['lead_balance'] > 0)
                    <small class="text-danger d-block">Balance: ₦{{ number_format($financialSummary['lead_balance'], 2) }}</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <small class="text-muted">Members Charges</small>
                    <h5 class="fw-bold mb-0">₦{{ number_format($financialSummary['members_total'], 2) }}</h5>
                    <small class="text-success">Paid: ₦{{ number_format($financialSummary['members_paid'], 2) }}</small>
                    @if($financialSummary['members_balance'] > 0)
                    <small class="text-danger d-block">Balance: ₦{{ number_format($financialSummary['members_balance'], 2) }}</small>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <small class="text-muted">Grand Total</small>
                    <h5 class="fw-bold mb-0">₦{{ number_format($financialSummary['grand_total'], 2) }}</h5>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body">
                    <small class="text-muted">Outstanding Balance</small>
                    <h5 class="fw-bold mb-0 {{ $financialSummary['grand_balance'] > 0 ? 'text-danger' : 'text-success' }}">
                        ₦{{ number_format($financialSummary['grand_balance'], 2) }}
                    </h5>
                    <small class="text-muted">
                        {{ $financialSummary['grand_balance'] > 0 ? 'Remaining' : ($financialSummary['grand_balance'] < 0 ? 'Credit' : 'Fully Paid') }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- Members List --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-list me-2 text-gold"></i>Group Members</h5>
            <button type="button" class="btn btn-sm btn-gold" data-bs-toggle="modal" data-bs-target="#addMemberModal">
                <i class="fas fa-user-plus me-1"></i> Add Member
            </button>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 ps-4">Guest</th>
                            <th class="border-0">Room</th>
                            <th class="border-0">Rate</th>
                            <th class="border-0">Stay</th>
                            <th class="border-0">Charges</th>
                            <th class="border-0">Paid</th>
                            <th class="border-0 text-center">Status</th>
                            <th class="border-0 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {{-- Lead row --}}
                        <tr class="table-secondary">
                            <td class="align-middle ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-dark p-1 me-2">
                                        <i class="fas fa-crown fa-sm text-white"></i>
                                    </div>
                                    <span class="fw-bold">{{ $registration->full_name }}</span>
                                    <span class="badge bg-dark ms-2">Lead</span>
                                </div>
                            </td>
                            <td class="align-middle">{{ $registration->room?->name ?? $registration->roomUnit?->room_number ?? 'N/A' }}</td>
                            <td class="align-middle">₦{{ number_format($registration->room_rate ?? 0, 2) }}</td>
                            <td class="align-middle">
                                <small>{{ $registration->check_in?->format('M d') }} - {{ $registration->check_out?->format('M d') }}</small>
                                <br><small class="text-muted">{{ $registration->no_of_nights ?? 0 }} night(s)</small>
                            </td>
                            <td class="align-middle fw-bold">₦{{ number_format($financialSummary['lead_total'], 2) }}</td>
                            <td class="align-middle text-success">₦{{ number_format($financialSummary['lead_paid'], 2) }}</td>
                            <td class="align-middle text-center">
                                @php
                                    $badges = ['checked_in' => ['bg-success', 'Checked In'], 'checked_out' => ['bg-secondary', 'Checked Out'], 'reserved' => ['bg-info', 'Reserved'], 'draft_by_guest' => ['bg-warning text-dark', 'Draft'], 'no_show' => ['bg-danger', 'No-Show']];
                                    $b = $badges[$registration->stay_status] ?? ['bg-light text-dark', $registration->stay_status];
                                @endphp
                                <span class="badge {{ $b[0] }}">{{ $b[1] }}</span>
                            </td>
                            <td class="align-middle text-end pe-4">
                                <a href="{{ route('frontdesk.registrations.show', $registration) }}" class="btn btn-sm btn-outline-gold" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        {{-- Member rows --}}
                        @forelse($members as $member)
                        <tr>
                            <td class="align-middle ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-light p-1 me-2">
                                        <i class="fas fa-user fa-sm text-gold"></i>
                                    </div>
                                    <span>{{ $member->full_name }}</span>
                                </div>
                            </td>
                            <td class="align-middle">{{ $member->room?->name ?? $member->roomUnit?->room_number ?? $member->room_allocation ?? 'N/A' }}</td>
                            <td class="align-middle">₦{{ number_format($member->room_rate ?? 0, 2) }}</td>
                            <td class="align-middle">
                                <small>{{ $member->check_in?->format('M d') }} - {{ $member->check_out?->format('M d') }}</small>
                                <br><small class="text-muted">{{ $member->no_of_nights ?? 0 }} night(s)</small>
                            </td>
                            <td class="align-middle fw-bold">
                                @php
                                    $mTotal = $member->folioCharges?->sum('amount') + ($member->discounted_rate ?? $member->room_rate ?? 0) * ($member->no_of_nights ?? 1);
                                @endphp
                                ₦{{ number_format($mTotal, 2) }}
                            </td>
                            <td class="align-middle text-success">
                                @php
                                    $mPaid = $member->payments?->sum('amount') ?? 0;
                                @endphp
                                ₦{{ number_format($mPaid, 2) }}
                            </td>
                            <td class="align-middle text-center">
                                @php
                                    $b2 = $badges[$member->stay_status] ?? ['bg-light text-dark', $member->stay_status];
                                @endphp
                                <span class="badge {{ $b2[0] }}">{{ $b2[1] }}</span>
                            </td>
                            <td class="align-middle text-end pe-4">
                                <div class="d-flex gap-1 justify-content-end">
                                    <a href="{{ route('frontdesk.registrations.show', $member) }}" class="btn btn-sm btn-outline-gold" title="View">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    @if($member->stay_status === 'checked_in')
                                    <form action="{{ route('frontdesk.registrations.checkout', $member) }}" method="POST" class="d-inline"
                                        onsubmit="return confirm('Check out {{ $member->full_name }}?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Check Out">
                                            <i class="fas fa-sign-out-alt"></i>
                                        </button>
                                    </form>
                                    @elseif(in_array($member->stay_status, ['draft_by_guest', 'reserved']))
                                    <a href="{{ route('frontdesk.registrations.finalize.form', $member) }}"
                                        class="btn btn-sm btn-outline-warning" title="Finalize">
                                        <i class="fas fa-check-double"></i>
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No members yet.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add Member Modal --}}
<div class="modal fade" id="addMemberModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('frontdesk.registrations.add-member', $registration) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold"><i class="fas fa-user-plus me-2"></i>Add Group Member</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="full_name" class="form-control" required placeholder="Guest name">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" placeholder="Phone number">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-gold">Add & Finalize</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
