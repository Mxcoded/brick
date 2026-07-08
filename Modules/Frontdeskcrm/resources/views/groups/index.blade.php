@extends('layouts.master')

@section('title', 'Group Bookings')

@section('page-content')
<div class="container-fluid py-4">
    {{-- Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3">
                        <i class="fas fa-users fa-lg text-primary"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">{{ $summary['total_groups'] }}</h6>
                        <small class="text-muted">Total Groups</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3">
                        <i class="fas fa-check-circle fa-lg text-success"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">{{ $summary['active_groups'] }}</h6>
                        <small class="text-muted">Active (Checked In)</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-info bg-opacity-10 p-3">
                        <i class="fas fa-user-friends fa-lg text-info"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">{{ $summary['total_members'] }}</h6>
                        <small class="text-muted">Total Members</small>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-gold bg-opacity-10 p-3">
                        <i class="fas fa-money-bill-wave fa-lg text-gold"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0">₦{{ number_format($summary['total_revenue'], 2) }}</h6>
                        <small class="text-muted">Total Revenue</small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body">
            <form method="GET" class="row g-3">
                <div class="col-md-5">
                    <label class="form-label fw-semibold small">Search</label>
                    <input type="text" name="search" class="form-control" value="{{ request('search') }}"
                        placeholder="Search by group name, reference, or member name...">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold small">Status</label>
                    <select name="status" class="form-select">
                        <option value="">All Statuses</option>
                        <option value="draft_by_guest" @selected(request('status') == 'draft_by_guest')>Draft</option>
                        <option value="reserved" @selected(request('status') == 'reserved')>Reserved</option>
                        <option value="checked_in" @selected(request('status') == 'checked_in')>Checked In</option>
                        <option value="checked_out" @selected(request('status') == 'checked_out')>Checked Out</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-gold"><i class="fas fa-search me-1"></i> Filter</button>
                    <a href="{{ route('frontdesk.groups.index') }}" class="btn btn-light border">Clear</a>
                </div>
            </form>
        </div>
    </div>

    {{-- Groups Table --}}
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold"><i class="fas fa-users me-2 text-gold"></i>All Groups</h5>
            <span class="text-muted small">{{ $groups->total() }} group(s) found</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="border-0 ps-4">Lead Guest</th>
                            <th class="border-0">Reference</th>
                            <th class="border-0 text-center">Members</th>
                            <th class="border-0 text-center">Checked In</th>
                            <th class="border-0 text-center">Draft</th>
                            <th class="border-0 text-center">Status</th>
                            <th class="border-0 text-end">Total Revenue</th>
                            <th class="border-0 text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($groups as $group)
                        <tr>
                            <td class="align-middle ps-4">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-circle bg-light p-1 me-2">
                                        <i class="fas fa-user-tie fa-sm text-gold"></i>
                                    </div>
                                    <span class="fw-semibold">{{ $group->full_name }}</span>
                                </div>
                            </td>
                            <td class="align-middle">
                                <code>{{ $group->reservation_code ?? 'N/A' }}</code>
                            </td>
                            <td class="align-middle text-center">{{ $group->no_of_guests ?? 0 }}</td>
                            <td class="align-middle text-center">
                                <span class="badge bg-success">{{ $group->checked_in_count }}</span>
                            </td>
                            <td class="align-middle text-center">
                                <span class="badge bg-warning text-dark">{{ $group->draft_count }}</span>
                            </td>
                            <td class="align-middle text-center">
                                @php
                                    $statusBadges = [
                                        'checked_in' => ['bg-success', 'Checked In'],
                                        'checked_out' => ['bg-secondary', 'Checked Out'],
                                        'reserved' => ['bg-info', 'Reserved'],
                                        'draft_by_guest' => ['bg-warning text-dark', 'Draft'],
                                        'no_show' => ['bg-danger', 'No-Show'],
                                    ];
                                    $badge = $statusBadges[$group->stay_status] ?? ['bg-light text-dark', ucfirst($group->stay_status)];
                                @endphp
                                <span class="badge {{ $badge[0] }}">{{ $badge[1] }}</span>
                            </td>
                            <td class="align-middle text-end fw-bold">
                                ₦{{ number_format($group->total_amount + $group->total_member_revenue, 2) }}
                            </td>
                            <td class="align-middle text-end pe-4">
                                <a href="{{ route('frontdesk.groups.show', $group) }}"
                                    class="btn btn-sm btn-outline-gold" title="View Group">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-users fa-3x mb-3 d-block"></i>
                                No group bookings found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($groups->hasPages())
        <div class="card-footer bg-white border-top">
            {{ $groups->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
