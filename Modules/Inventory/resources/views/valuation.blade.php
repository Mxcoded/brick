@extends('layouts.master')

@section('title', 'Stock Valuation')

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 text-dark">Stock Valuation</h1>
            <p class="lead text-muted">Current inventory value by item and store location.</p>
        </div>
        <div class="text-end">
            <h4 class="mb-0">Grand Total: <span class="text-primary">₦{{ number_format($grandTotal, 2) }}</span></h4>
        </div>
    </div>

    <form method="GET" class="row g-3 mb-4">
        <div class="col-md-4">
            <select name="store_id" class="form-select">
                <option value="">All Stores</option>
                @foreach ($stores as $store)
                    <option value="{{ $store->id }}" {{ request('store_id') == $store->id ? 'selected' : '' }}>
                        {{ $store->name }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <select name="category" class="form-select">
                <option value="">All Categories</option>
                @foreach ($categories as $cat)
                    <option value="{{ $cat }}" {{ request('category') == $cat ? 'selected' : '' }}>
                        {{ $cat }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary me-2"><i class="fas fa-filter me-1"></i> Filter</button>
            <a href="{{ route('inventory.valuation') }}" class="btn btn-outline-secondary"><i class="fas fa-times me-1"></i> Clear</a>
        </div>
    </form>

    <div class="card shadow border-0 mb-4">
        <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
            <h5 class="mb-0 fw-bold">Valuation Summary</h5>
            <span class="badge bg-secondary">{{ $items->count() }} items</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-striped table-hover mb-0" id="valuationTable">
                    <thead class="table-dark">
                        <tr>
                            <th>SKU</th>
                            <th>Item</th>
                            <th>Category</th>
                            <th>Supplier</th>
                            @foreach ($stores as $store)
                                <th class="text-end">{{ $store->name }} (Qty)</th>
                                <th class="text-end">{{ $store->name }} (Value)</th>
                            @endforeach
                            <th class="text-end">Total Qty</th>
                            <th class="text-end">Total Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $item)
                            <tr>
                                <td><code>{{ $item->sku }}</code></td>
                                <td>{{ $item->description }}</td>
                                <td>{{ $item->category ?? 'N/A' }}</td>
                                <td>{{ $item->supplier->name ?? 'N/A' }}</td>
                                @foreach ($stores as $store)
                                    @php
                                        $si = $item->storeItems->firstWhere('store_id', $store->id);
                                    @endphp
                                    <td class="text-end">{{ $si ? $si->quantity : 0 }}</td>
                                    <td class="text-end">₦{{ number_format($si->total_cost ?? 0, 2) }}</td>
                                @endforeach
                                <td class="text-end fw-bold">{{ $item->totalQty }}</td>
                                <td class="text-end fw-bold">₦{{ number_format($item->totalValue, 2) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ 3 + count($stores) * 2 }}" class="text-center py-4 text-muted">
                                    <i class="fas fa-box-open fa-3x mb-3 d-block"></i>
                                    No items found matching your filters.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light fw-bold">
                        <tr>
                            <td colspan="4" class="text-end">Totals</td>
                            @foreach ($stores as $store)
                                <td class="text-end">—</td>
                                <td class="text-end">₦{{ number_format($storeTotals[$store->id] ?? 0, 2) }}</td>
                            @endforeach
                            <td class="text-end">{{ $items->sum('totalQty') }}</td>
                            <td class="text-end">₦{{ number_format($grandTotal, 2) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
    $(document).ready(function () {
        $('#valuationTable').DataTable({
            paging: false,
            info: false,
            ordering: true,
            dom: 'Bfrt',
            buttons: ['copy', 'csv', 'excel', 'pdf']
        });
    });
</script>
@endsection