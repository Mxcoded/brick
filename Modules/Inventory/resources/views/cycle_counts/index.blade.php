@extends('layouts.master')

@section('title', 'Cycle Counts')

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 text-dark mb-0">Cycle Counts</h1>
            <p class="text-muted mb-0">Record physical stock counts and compare with system quantities</p>
        </div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#cycleCountModal">
            <i class="fas fa-clipboard-list me-2"></i>New Count
        </button>
    </div>

    @php
        $pendingCounts = $counts->where('status', 'pending')->count();
        $verifiedCounts = $counts->where('status', 'verified')->count();
        $totalDiscrepancy = $counts->sum('discrepancy');
    @endphp

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="text-xs fw-bold text-warning text-uppercase">Pending Review</div>
                        <div class="h5 mb-0 fw-bold">{{ $pendingCounts }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="text-xs fw-bold text-success text-uppercase">Verified</div>
                        <div class="h5 mb-0 fw-bold">{{ $verifiedCounts }}</div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between">
                        <div class="text-xs fw-bold text-info text-uppercase">Net Discrepancy</div>
                        <div class="h5 mb-0 fw-bold {{ $totalDiscrepancy < 0 ? 'text-danger' : 'text-success' }}">{{ $totalDiscrepancy > 0 ? '+' : '' }}{{ $totalDiscrepancy }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="cycleCountsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Date</th>
                            <th>Item</th>
                            <th>Store</th>
                            <th class="text-center">Expected</th>
                            <th class="text-center">Actual</th>
                            <th class="text-center">Discrepancy</th>
                            <th>Status</th>
                            <th>Counted By</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($counts as $count)
                        <tr>
                            <td class="ps-4">{{ $count->counted_at?->format('d/m/Y H:i') ?? '—' }}</td>
                            <td class="fw-bold">{{ $count->item->description ?? 'N/A' }}</td>
                            <td>{{ $count->store->name ?? 'N/A' }}</td>
                            <td class="text-center">{{ $count->expected_quantity }}</td>
                            <td class="text-center fw-bold">{{ $count->actual_quantity }}</td>
                            <td class="text-center fw-bold {{ $count->discrepancy < 0 ? 'text-danger' : ($count->discrepancy > 0 ? 'text-success' : '') }}">
                                {{ $count->discrepancy > 0 ? '+' : '' }}{{ $count->discrepancy }}
                            </td>
                            <td>
                                @if($count->status == 'verified')
                                    <span class="badge bg-success rounded-pill">Verified</span>
                                @elseif($count->status == 'approved')
                                    <span class="badge bg-info rounded-pill">Approved</span>
                                @else
                                    <span class="badge bg-warning text-dark rounded-pill">Pending</span>
                                @endif
                            </td>
                            <td>{{ $count->counter->name ?? 'N/A' }}</td>
                            <td>{{ $count->notes ?? '—' }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                {{ $counts->links() }}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="cycleCountModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Record Cycle Count</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="cycleCountForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Item</label>
                        <select class="form-select" name="item_id" required>
                            <option value="">Select item...</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}">{{ $item->description }}{{ $item->sku ? ' (' . $item->sku . ')' : '' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Store</label>
                        <select class="form-select" name="store_id" required>
                            <option value="">Select store...</option>
                            @foreach($stores as $store)
                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Actual Quantity (Physical Count)</label>
                        <input type="number" class="form-control" name="actual_quantity" min="0" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Notes (Optional)</label>
                        <textarea class="form-control" name="notes" rows="2"></textarea>
                    </div>
                    <div id="cycleCountAlert"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary">Record Count</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    $('#cycleCountForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const btn = form.find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-1"></i> Saving...');

        $.ajax({
            type: 'POST',
            url: '{{ route("inventory.cycle-counts.store") }}',
            data: form.serialize(),
            dataType: 'json',
            success: function(response) {
                $('#cycleCountAlert').html('<div class="alert alert-success">' + response.message + '</div>');
                setTimeout(() => window.location.reload(), 1500);
            },
            error: function(xhr) {
                const msg = xhr.responseJSON?.message || 'Error recording count.';
                $('#cycleCountAlert').html('<div class="alert alert-danger">' + msg + '</div>');
                btn.prop('disabled', false).html('Record Count');
            }
        });
    });
});
</script>
@endsection
