@extends('layouts.master')

@section('title', 'Purchase Requests')

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 text-dark mb-0">Purchase Requests</h1>
            <p class="text-muted mb-0">All procurement requests across the workflow</p>
        </div>
        <div>
            @if(auth()->user()->isProcurementRequester())
            <a href="{{ route('inventory.procurement.requests.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus-circle me-2"></i>New Request
            </a>
            @endif
            <a href="{{ route('inventory.procurement.dashboard') }}" class="btn btn-outline-gold shadow-sm">
                <i class="fas fa-tachometer-alt me-1"></i>Dashboard
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">All Requests</h5>
            <div class="input-group" style="max-width:300px;">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="text" class="form-control" id="tableSearch" placeholder="Search requests...">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="requestsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">PR #</th>
                            <th>Requester</th>
                            <th>Department</th>
                            <th class="text-center">Items</th>
                            <th class="text-center">Urgency</th>
                            <th class="text-center">Status</th>
                            <th class="text-center">Current</th>
                            <th>Created</th>
                            <th class="text-center pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $pr)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $pr->pr_number }}</td>
                            <td>{{ $pr->requester->name ?? 'N/A' }}</td>
                            <td>{{ $pr->department ?? '—' }}</td>
                            <td class="text-center"><span class="badge bg-gold rounded-pill">{{ $pr->items->count() }}</span></td>
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
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fas fa-file-invoice fa-3x mb-3 d-block"></i>
                                No purchase requests found.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('tableSearch')?.addEventListener('keyup', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#requestsTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });
</script>
@endsection
