@extends('layouts.master')

@section('title', 'Procurement Dashboard')

@section('page-content')
<div class="container-fluid p-4">
    @php
        $roleLabel = match($role) {
            'line_manager' => 'Line Manager',
            'purchaser' => 'Purchaser',
            'gm' => 'General Manager',
            'finance' => 'Finance',
            'auditor' => 'Auditor',
            'ggm' => 'Group General Manager',
            default => $role,
        };
        $canCreate = auth()->user()->isProcurementRequester();
    @endphp

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 text-dark mb-0">Procurement</h1>
            <p class="text-muted mb-0">{{ $roleLabel }} Dashboard</p>
        </div>
        <div class="d-flex gap-2">
            @if($canCreate)
                <a href="{{ route('inventory.procurement.requests.create') }}" class="btn btn-primary shadow-sm">
                    <i class="fas fa-plus-circle me-2"></i>Create New Request
                </a>
            @endif
            <a href="{{ route('inventory.procurement.requests.index') }}" class="btn btn-outline-gold shadow-sm">
                <i class="fas fa-list me-1"></i>All Requests
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    {{-- Metrics Cards --}}
    <div class="row mb-4">
        @if($role === 'line_manager')
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="card border-left-primary shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total</div><div class="h5 mb-0 font-weight-bold text-gray-800">{{ $metrics['total'] }}</div></div><div class="col-auto"><i class="fas fa-file-invoice fa-2x text-gray-300"></i></div></div></div></div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="card border-left-warning shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending</div><div class="h5 mb-0 font-weight-bold text-gray-800">{{ $metrics['pending'] }}</div></div><div class="col-auto"><i class="fas fa-clock fa-2x text-gray-300"></i></div></div></div></div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="card border-left-success shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-success text-uppercase mb-1">Approved</div><div class="h5 mb-0 font-weight-bold text-gray-800">{{ $metrics['approved'] }}</div></div><div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div></div></div></div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="card border-left-danger shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Rejected</div><div class="h5 mb-0 font-weight-bold text-gray-800">{{ $metrics['rejected'] }}</div></div><div class="col-auto"><i class="fas fa-times-circle fa-2x text-gray-300"></i></div></div></div></div>
            </div>
            <div class="col-xl-2 col-md-4 col-6 mb-3">
                <div class="card border-left-info shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-info text-uppercase mb-1">Flagged</div><div class="h5 mb-0 font-weight-bold text-gray-800">{{ $metrics['flagged'] }}</div></div><div class="col-auto"><i class="fas fa-flag fa-2x text-gray-300"></i></div></div></div></div>
            </div>
        @else
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-warning shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Awaiting Action</div><div class="h5 mb-0 font-weight-bold text-gray-800">{{ $metrics['pending'] ?? $metrics['incoming'] ?? 0 }}</div></div><div class="col-auto"><i class="fas fa-inbox fa-2x text-gray-300"></i></div></div></div></div>
            </div>
            @if(isset($metrics['processed']))
            <div class="col-xl-3 col-md-6 mb-3">
                <div class="card border-left-success shadow h-100 py-2"><div class="card-body"><div class="row no-gutters align-items-center"><div class="col mr-2"><div class="text-xs font-weight-bold text-success text-uppercase mb-1">Processed</div><div class="h5 mb-0 font-weight-bold text-gray-800">{{ $metrics['processed'] }}</div></div><div class="col-auto"><i class="fas fa-check-double fa-2x text-gray-300"></i></div></div></div></div>
            </div>
            @endif
        @endif
    </div>

    {{-- Inbox: Actions Needed --}}
    @if($role !== 'line_manager' && isset($inbox) && $inbox->isNotEmpty())
    <div class="card shadow border-0 mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-warning"><i class="fas fa-inbox me-2"></i>Inbox — Requires Your Action</h5>
            <span class="badge bg-warning text-dark rounded-pill">{{ $inbox->count() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">PR #</th>
                            <th>Requester</th>
                            <th>Department</th>
                            <th>Items</th>
                            <th class="text-center">Urgency</th>
                            <th class="text-center">Status</th>
                            <th>Submitted</th>
                            <th class="text-center pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inbox as $pr)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $pr->pr_number }}</td>
                            <td>{{ $pr->requester->name ?? 'N/A' }}</td>
                            <td>{{ $pr->department ?? '—' }}</td>
                            <td><span class="badge bg-gold rounded-pill">{{ $pr->items->count() }}</span></td>
                            <td class="text-center">
                                <span class="badge {{ \Modules\Inventory\Models\PurchaseRequest::urgencyBadge($pr->urgency) }}">
                                    {{ ucfirst($pr->urgency) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ \Modules\Inventory\Models\PurchaseRequest::statusBadge($pr->status) }}">
                                    {{ \Modules\Inventory\Models\PurchaseRequest::statusLabel($pr->status) }}
                                </span>
                            </td>
                            <td>{{ $pr->created_at->format('d M Y') }}</td>
                            <td class="text-center pe-4">
                                <a href="{{ route('inventory.procurement.requests.show', $pr) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="fas fa-eye me-1"></i>Review
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Line Manager: Flagged Items --}}
    @if($role === 'line_manager' && isset($inbox) && $inbox->isNotEmpty())
    <div class="card shadow border-0 mb-4 border-start border-info border-4">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold text-info"><i class="fas fa-flag me-2"></i>Flagged — Needs Your Response</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">PR #</th>
                            <th>Department</th>
                            <th>Items</th>
                            <th>Justification</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inbox as $pr)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $pr->pr_number }}</td>
                            <td>{{ $pr->department ?? '—' }}</td>
                            <td><span class="badge bg-gold rounded-pill">{{ $pr->items->count() }}</span></td>
                            <td class="text-truncate" style="max-width:250px;">{{ Str::limit($pr->justification, 60) }}</td>
                            <td class="text-center">
                                <span class="badge bg-info text-dark">Flagged</span>
                            </td>
                            <td class="text-center pe-4">
                                <a href="{{ route('inventory.procurement.requests.edit', $pr) }}" class="btn btn-sm btn-info rounded-pill text-white">
                                    <i class="fas fa-edit me-1"></i>Edit &amp; Resubmit
                                </a>
                                <a href="{{ route('inventory.procurement.requests.show', $pr) }}" class="btn btn-sm btn-outline-gold rounded-pill">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- My / All Requests --}}
    @if($role === 'line_manager' && isset($requests))
    <div class="card shadow border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-list me-2"></i>My Requests</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">PR #</th>
                            <th>Department</th>
                            <th>Items</th>
                            <th class="text-center">Urgency</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Stage</th>
                            <th>Created</th>
                            <th class="text-center pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $pr)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $pr->pr_number }}</td>
                            <td>{{ $pr->department ?? '—' }}</td>
                            <td><span class="badge bg-gold rounded-pill">{{ $pr->items->count() }}</span></td>
                            <td class="text-center">
                                <span class="badge {{ \Modules\Inventory\Models\PurchaseRequest::urgencyBadge($pr->urgency) }}">
                                    {{ ucfirst($pr->urgency) }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge {{ \Modules\Inventory\Models\PurchaseRequest::statusBadge($pr->status) }}">
                                    {{ \Modules\Inventory\Models\PurchaseRequest::statusLabel($pr->status) }}
                                </span>
                            </td>
                            <td class="text-center">
                                @if($pr->current_role)
                                    <small class="text-muted">{{ ucfirst(str_replace('_', ' ', $pr->current_role)) }}</small>
                                @else
                                    <small class="text-muted">—</small>
                                @endif
                            </td>
                            <td>{{ $pr->created_at->format('d M Y') }}</td>
                            <td class="text-center pe-4">
                                <a href="{{ route('inventory.procurement.requests.show', $pr) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                                @if(in_array($pr->status, ['draft', 'flagged']))
                                    <a href="{{ route('inventory.procurement.requests.edit', $pr) }}" class="btn btn-sm btn-outline-gold rounded-pill">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted">
                                <i class="fas fa-file-invoice fa-3x mb-3 d-block"></i>
                                No purchase requests yet.
                                <a href="{{ route('inventory.procurement.requests.create') }}" class="d-block mt-2">Create your first request</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- Purchaser: Processed --}}
    @if($role === 'purchaser' && isset($processed) && $processed->isNotEmpty())
    <div class="card shadow border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-check-double me-2"></i>Processed Requests</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">PR #</th>
                            <th>Requester</th>
                            <th>Items</th>
                            <th class="text-center">Status</th>
                            <th>Processed</th>
                            <th class="text-center pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($processed as $pr)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $pr->pr_number }}</td>
                            <td>{{ $pr->requester->name ?? 'N/A' }}</td>
                            <td><span class="badge bg-gold rounded-pill">{{ $pr->items->count() }}</span></td>
                            <td class="text-center">
                                <span class="badge {{ \Modules\Inventory\Models\PurchaseRequest::statusBadge($pr->status) }}">
                                    {{ \Modules\Inventory\Models\PurchaseRequest::statusLabel($pr->status) }}
                                </span>
                            </td>
                            <td>{{ $pr->updated_at->format('d M Y') }}</td>
                            <td class="text-center pe-4">
                                <a href="{{ route('inventory.procurement.requests.show', $pr) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif

    {{-- GM/Finance/Auditor/GGM: History --}}
    @if(in_array($role, ['gm', 'finance', 'auditor', 'ggm']) && isset($history) && $history->isNotEmpty())
    <div class="card shadow border-0">
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i>History</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">PR #</th>
                            <th>Requester</th>
                            <th>Department</th>
                            <th class="text-center">Status</th>
                            <th>Updated</th>
                            <th class="text-center pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $pr)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $pr->pr_number }}</td>
                            <td>{{ $pr->requester->name ?? 'N/A' }}</td>
                            <td>{{ $pr->department ?? '—' }}</td>
                            <td class="text-center">
                                <span class="badge {{ \Modules\Inventory\Models\PurchaseRequest::statusBadge($pr->status) }}">
                                    {{ \Modules\Inventory\Models\PurchaseRequest::statusLabel($pr->status) }}
                                </span>
                            </td>
                            <td>{{ $pr->updated_at->format('d M Y') }}</td>
                            <td class="text-center pe-4">
                                <a href="{{ route('inventory.procurement.requests.show', $pr) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="fas fa-eye me-1"></i>View
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
