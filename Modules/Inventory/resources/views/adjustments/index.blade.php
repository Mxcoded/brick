@extends('layouts.master')

@section('title', 'Inventory Adjustments')

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 text-dark mb-0">Inventory Adjustments</h1>
            <p class="text-muted mb-0">Record write-offs and stock corrections</p>
        </div>
        <div>
            <a href="{{ route('inventory.export.adjustments') }}" class="btn btn-success shadow-sm me-2">
                <i class="fas fa-file-excel me-1"></i>Export Excel
            </a>
            <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#adjustmentModal">
                <i class="fas fa-plus-circle me-2"></i>New Adjustment
            </button>
        </div>
    </div>

    @php
        $totalAdjustments = $adjustments->count();
        $totalWriteOffs = $adjustments->where('type', 'write_off')->sum('quantity_change');
        $totalCorrections = $adjustments->where('type', 'correction')->where('quantity_change', '>', 0)->sum('quantity_change');
    @endphp

    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Adjustments</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalAdjustments }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-balance-scale fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Total Written Off</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ abs($totalWriteOffs) }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-trash fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Corrected</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalCorrections }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-wrench fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow border-0">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Adjustment History</h5>
            <div class="input-group" style="max-width: 250px;">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="text" class="form-control" id="tableSearch" placeholder="Search...">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="adjustmentsTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">#</th>
                            <th>Date</th>
                            <th>Item</th>
                            <th>Store</th>
                            <th>Type</th>
                            <th class="text-center">Qty Change</th>
                            <th>Reason</th>
                            <th>By</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adjustments as $adj)
                            <tr>
                                <td class="ps-4">{{ $adj->id }}</td>
                                <td>{{ $adj->created_at->format('d/m/Y H:i') }}</td>
                                <td>{{ $adj->item->description }}</td>
                                <td>{{ $adj->store->name }}</td>
                                <td>
                                    @if($adj->type === 'write_off')
                                        <span class="badge bg-danger rounded-pill">Write-Off</span>
                                    @else
                                        <span class="badge bg-warning text-dark rounded-pill">Correction</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold {{ $adj->quantity_change < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ $adj->quantity_change > 0 ? '+' : '' }}{{ $adj->quantity_change }}
                                </td>
                                <td>{{ $adj->reason }}</td>
                                <td>{{ $adj->adjustedBy->name ?? 'N/A' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-balance-scale fa-3x mb-3 d-block"></i>
                                    No adjustments recorded yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white">
                {{ $adjustments->links() }}
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="adjustmentModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">New Inventory Adjustment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="adjustmentForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Item</label>
                        <select class="form-select" name="item_id" required>
                            <option value="">Select item...</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}">{{ $item->description }}</option>
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
                        <label class="form-label fw-bold">Type</label>
                        <select class="form-select" name="type" id="adjType" required>
                            <option value="write_off">Write-Off (remove stock)</option>
                            <option value="correction">Correction (add/remove stock)</option>
                        </select>
                        <div class="form-text" id="adjTypeHint">Permanently removes stock from inventory.</div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Quantity</label>
                        <input type="number" class="form-control" name="quantity_change" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Reason</label>
                        <textarea class="form-control" name="reason" rows="2" required placeholder="Why is this adjustment needed?"></textarea>
                    </div>
                    <div id="adjustmentAlert"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save me-2"></i>Record Adjustment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    document.getElementById('tableSearch')?.addEventListener('keyup', function() {
        const q = this.value.toLowerCase();
        document.querySelectorAll('#adjustmentsTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(q) ? '' : 'none';
        });
    });

    document.getElementById('adjType')?.addEventListener('change', function() {
        const hint = document.getElementById('adjTypeHint');
        if (this.value === 'write_off') {
            hint.textContent = 'Permanently removes stock from inventory.';
        } else {
            hint.textContent = 'Adds or removes stock to correct records.';
        }
    });

    $('#adjustmentForm').on('submit', function(e) {
        e.preventDefault();
        const form = $(this);
        const alertDiv = $('#adjustmentAlert');
        const btn = form.find('button[type="submit"]');
        btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin me-2"></i>Saving...');

        $.ajax({
            type: 'POST',
            url: '{{ route("inventory.adjustments.store") }}',
            data: form.serialize(),
            dataType: 'json',
            success: function(res) {
                alertDiv.html('<div class="alert alert-success">' + res.message + '</div>');
                setTimeout(() => window.location.reload(), 1000);
            },
            error: function(xhr) {
                let html = '<div class="alert alert-danger"><ul class="mb-0">';
                const errors = xhr.responseJSON?.errors || { message: [xhr.responseJSON?.message || 'Error'] };
                $.each(errors, function(k, v) { html += '<li>' + (Array.isArray(v) ? v[0] : v) + '</li>'; });
                html += '</ul></div>';
                alertDiv.html(html);
                btn.prop('disabled', false).html('<i class="fas fa-save me-2"></i>Record Adjustment');
            }
        });
    });
</script>
@endsection
