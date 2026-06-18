@extends('layouts.master')

@section('title', 'All Items')

@section('page-content')
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-5 text-dark mb-0">All Items</h1>
                <p class="text-muted mb-0">Browse, search, and manage all inventory items.</p>
            </div>
            <div>
                @can('inventory.create')
                <a href="{{ route('inventory.items.create') }}" class="btn btn-primary shadow-sm me-2">
                    <i class="fas fa-plus-circle me-2"></i>Add New Item
                </a>
                @endcan
                <a href="{{ route('inventory.export.items') }}" class="btn btn-success shadow-sm">
                    <i class="fas fa-file-excel me-2"></i>Export
                </a>
            </div>
        </div>

        <div class="card shadow border-0 mb-4">
            <div class="card-body p-3">
                <div class="row g-2 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small text-muted">Search</label>
                        <input type="text" id="searchBox" class="form-control" placeholder="Search by name, SKU, category...">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Category</label>
                        <select id="categoryFilter" class="form-select">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small text-muted">Supplier</label>
                        <select id="supplierFilter" class="form-select">
                            <option value="">All Suppliers</option>
                            @foreach ($suppliers as $sup)
                                <option value="{{ $sup->id }}">{{ $sup->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small text-muted">&nbsp;</label>
                        <button id="clearFilters" class="btn btn-outline-secondary w-100">Clear</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow border-0">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                <h5 class="mb-0 text-dark fw-bold">All Items <span class="badge bg-secondary ms-2">{{ $items->total() }}</span></h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0" id="itemsTable">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>SKU</th>
                                <th>Description</th>
                                <th>Category</th>
                                <th>Supplier</th>
                                <th>Total Qty</th>
                                <th>Total Value</th>
                                <th>Unit</th>
                                <th>Price</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                <tr>
                                    <td>{{ $item->id }}</td>
                                    <td><code>{{ $item->sku ?? '—' }}</code></td>
                                    <td>
                                        @if ($item->photo_path)
                                            <img src="{{ asset('storage/'.$item->photo_path) }}" class="rounded me-1" style="width: 28px; height: 28px; object-fit: cover;">
                                        @endif
                                        {{ $item->description }}
                                    </td>
                                    <td>{{ $item->category ?? 'N/A' }}</td>
                                    <td>{{ $item->supplier->name ?? 'N/A' }}</td>
                                    <td>{{ number_format($item->storeItems->sum('quantity')) }}</td>
                                    <td>₦{{ number_format($item->storeItems->sum('total_cost'), 2) }}</td>
                                    <td>{{ $item->unit_of_measurement ?? 'N/A' }}</td>
                                    <td>₦{{ number_format($item->price, 2) }}</td>
                                    <td>
                                        <button class="btn btn-sm btn-outline-success restock-btn" data-bs-toggle="modal" data-bs-target="#restockModal" data-item-id="{{ $item->id }}" data-item-description="{{ $item->description }}" title="Restock">
                                            <i class="fas fa-plus-circle"></i>
                                        </button>
                                        <a href="{{ route('inventory.items.edit', $item->id) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
                                        <form action="{{ route('inventory.items.destroy', $item->id) }}" method="POST" class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this item?');" title="Delete"><i class="fas fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="mt-3">{{ $items->links() }}</div>
    </div>

    @include('inventory::_restock_modal')
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(document).ready(function() {
    var table = $('#itemsTable').DataTable({
        pageLength: 25,
        order: [[0, 'desc']],
        dom: 'rt<"row"<"col-md-5"i><"col-md-7"p>>',
    });

    $('#searchBox').on('keyup', function() { table.search(this.value).draw(); });
    $('#categoryFilter').on('change', function() { table.column(3).search(this.value).draw(); });
    $('#supplierFilter').on('change', function() { table.column(4).search(this.value).draw(); });
    $('#clearFilters').on('click', function() {
        $('#searchBox').val(''); $('#categoryFilter').val(''); $('#supplierFilter').val('');
        table.search('').columns().search('').draw();
    });

    $('#itemsTable').on('click', '.restock-btn', function() {
        $('#restockItemId').val($(this).data('item-id'));
        $('#restockItemName').text($(this).data('item-description'));
        $('#restockForm')[0].reset();
        $('#restockAlertContainer').html('');
    });

    $('#restockForm').on('submit', function(e) {
        e.preventDefault();
        $.ajax({
            type: "POST", url: $(this).attr('action'), data: $(this).serialize(), dataType: 'json',
            success: function(res) {
                $('#restockAlertContainer').html('<div class="alert alert-success">' + res.message + '</div>');
                setTimeout(() => location.reload(), 1000);
            },
            error: function(xhr) {
                let errors = xhr.responseJSON.errors;
                let html = '<div class="alert alert-danger"><ul>';
                $.each(errors, function(k, v) { html += '<li>' + v[0] + '</li>'; });
                html += '</ul></div>';
                $('#restockAlertContainer').html(html);
            }
        });
    });
});
</script>
@endsection
