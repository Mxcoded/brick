@extends('restaurant::layouts.adminMaster')
@section('title', 'Stock Management')
@section('admin-content')
<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold mb-0"><i class="bi bi-box-seam me-2"></i>Stock Management</h4>
        <button class="btn btn-primary btn-sm rounded-pill" data-bs-toggle="modal" data-bs-target="#stockModal">
            <i class="bi bi-plus-lg me-1"></i>Add Item
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($lowStock->count() > 0)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle me-2"></i>
        <strong>{{ $lowStock->count() }} item(s)</strong> are below minimum stock level:
        @foreach($lowStock as $item)
            <span class="badge bg-warning text-dark me-1">{{ $item->name }} ({{ $item->stock_quantity }} {{ $item->unit }})</span>
        @endforeach
    </div>
    @endif

    <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="py-3">Name</th>
                            <th class="py-3">Unit</th>
                            <th class="py-3 text-end">Stock</th>
                            <th class="py-3 text-end">Min Level</th>
                            <th class="py-3 text-end">Unit Cost</th>
                            <th class="py-3 text-end">Used In</th>
                            <th class="py-3 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                        <tr class="{{ $item->isLowStock() ? 'table-warning' : '' }}">
                            <td class="fw-medium">{{ $item->name }}</td>
                            <td>{{ $item->unit }}</td>
                            <td class="text-end">{{ number_format($item->stock_quantity, 2) }}</td>
                            <td class="text-end">{{ number_format($item->min_stock_level, 2) }}</td>
                            <td class="text-end">₦{{ number_format($item->unit_cost ?? 0, 2) }}</td>
                            <td class="text-end">{{ $item->recipe_items_count }}</td>
                            <td class="text-end">
                                <button class="btn btn-outline-primary btn-sm" onclick="editStock({{ $item->id }})">Edit</button>
                                <button class="btn btn-outline-success btn-sm" onclick="addMovement({{ $item->id }})">Stock</button>
                                <form action="{{ route('restaurant.admin.stock.destroy', $item) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this item?')">
                                    @csrf
                                    <button class="btn btn-outline-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center text-muted py-4">No stock items yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

{{-- Add Stock Modal --}}
<div class="modal fade" id="stockModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('restaurant.admin.stock.store') }}" method="POST">
                @csrf
                <div class="modal-header"><h6 class="modal-title">Add Stock Item</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-2"><label class="form-label small fw-medium">Name</label><input type="text" name="name" class="form-control" required></div>
                    <div class="row g-2 mb-2">
                        <div class="col"><label class="form-label small fw-medium">Unit</label>
                            <select name="unit" class="form-select" required>
                                <option value="kg">kg</option><option value="g">g</option><option value="L">L</option>
                                <option value="ml">ml</option><option value="pcs">pcs</option><option value="pack">pack</option>
                            </select>
                        </div>
                        <div class="col"><label class="form-label small fw-medium">Stock Qty</label><input type="number" name="stock_quantity" class="form-control" step="0.001" min="0" required></div>
                        <div class="col"><label class="form-label small fw-medium">Min Level</label><input type="number" name="min_stock_level" class="form-control" step="0.001" min="0" required></div>
                    </div>
                    <div class="mb-2"><label class="form-label small fw-medium">Unit Cost (₦)</label><input type="number" name="unit_cost" class="form-control" step="0.01" min="0"></div>
                    <div><label class="form-label small fw-medium">Description</label><textarea name="description" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Stock Movement Modal --}}
<div class="modal fade" id="movementModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <form action="{{ route('restaurant.admin.stock.movement') }}" method="POST">
                @csrf
                <input type="hidden" name="restaurant_stock_item_id" id="movementStockId">
                <div class="modal-header"><h6 class="modal-title">Record Stock Movement</h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
                <div class="modal-body">
                    <div class="mb-2">
                        <label class="form-label small fw-medium">Type</label>
                        <select name="type" class="form-select" required>
                            <option value="purchase">Purchase (add stock)</option>
                            <option value="usage">Usage (remove stock)</option>
                            <option value="wastage">Wastage (remove stock)</option>
                            <option value="adjustment">Adjustment</option>
                        </select>
                    </div>
                    <div class="mb-2"><label class="form-label small fw-medium">Quantity</label><input type="number" name="quantity" class="form-control" step="0.001" min="0.001" required></div>
                    <div class="mb-2"><label class="form-label small fw-medium">Unit Cost (₦, for purchases)</label><input type="number" name="unit_cost" class="form-control" step="0.01" min="0"></div>
                    <div class="mb-2"><label class="form-label small fw-medium">Reference</label><input type="text" name="reference" class="form-control" placeholder="Invoice #"></div>
                    <div><label class="form-label small fw-medium">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm">Record</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function editStock(id) { alert('Edit functionality - open edit modal with ID: ' + id); }
function addMovement(id) {
    document.getElementById('movementStockId').value = id;
    new bootstrap.Modal(document.getElementById('movementModal')).show();
}
</script>
@endpush
@endSection
