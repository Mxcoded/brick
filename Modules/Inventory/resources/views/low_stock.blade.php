@extends('layouts.master')

@section('title', 'Low Stock Items')

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 text-dark mb-0">Low Stock Items</h1>
            <p class="text-muted mb-0">Items that have fallen below their minimum stock level</p>
        </div>
        <a href="{{ route('inventory.dashboard') }}" class="btn btn-secondary shadow-sm">
            <i class="fas fa-arrow-left me-2"></i>Back to Dashboard
        </a>
    </div>

    @if($items->isEmpty())
        <div class="card shadow border-0">
            <div class="card-body text-center py-5">
                <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                <h4>All Stocked Up</h4>
                <p class="text-muted mb-0">All items are adequately stocked. No items below minimum levels.</p>
            </div>
        </div>
    @else
        @php
            $outOfStock = $items->filter(fn($i) => $i->storeItems->sum('quantity') == 0);
            $lowStock = $items->filter(fn($i) => $i->storeItems->sum('quantity') > 0);
        @endphp

        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Out of Stock</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $outOfStock->count() }}</div>
                            </div>
                            <div class="col-auto"><i class="fas fa-times-circle fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-6 mb-3">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Low Stock</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $lowStock->count() }}</div>
                            </div>
                            <div class="col-auto"><i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card shadow border-0">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Items Needing Attention</h5>
            </div>
            <div class="card-body p-4">
                <div class="row">
                    @foreach($items as $item)
                        @php
                            $totalQty = $item->storeItems->sum('quantity');
                            $maxStock = $item->max_stock ?? $item->min_stock * 2;
                            $percentage = $maxStock > 0 ? min(100, ($totalQty / $maxStock) * 100) : 0;
                            $barClass = $totalQty == 0 ? 'bg-danger' : ($percentage < 50 ? 'bg-warning' : 'bg-info');
                        @endphp
                        <div class="col-md-6 mb-3">
                            <div class="card border-0 bg-light h-100">
                                <div class="card-body">
                                    <div class="d-flex justify-content-between align-items-start mb-2">
                                        <div>
                                            <h6 class="fw-bold mb-0">{{ $item->description }}</h6>
                                            <small class="text-muted">{{ $item->category ?? 'Uncategorized' }} — {{ $item->supplier->name ?? 'N/A' }}</small>
                                        </div>
                                        @if($totalQty == 0)
                                            <span class="badge bg-danger rounded-pill">Out of Stock</span>
                                        @else
                                            <span class="badge bg-warning text-dark rounded-pill">Low Stock</span>
                                        @endif
                                    </div>
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span>Current: <strong>{{ $totalQty }}</strong></span>
                                        <span>Min: <strong>{{ $item->min_stock }}</strong></span>
                                        @if($item->max_stock)
                                            <span>Max: <strong>{{ $item->max_stock }}</strong></span>
                                        @endif
                                    </div>
                                    <div class="progress" style="height: 8px;">
                                        <div class="progress-bar {{ $barClass }}" role="progressbar"
                                             style="width: {{ $percentage }}%;"
                                             aria-valuenow="{{ $totalQty }}" aria-valuemin="0" aria-valuemax="{{ $maxStock }}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="card shadow border-0 mt-4">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0 fw-bold">Detailed View</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Item</th>
                                <th>Category</th>
                                <th>Supplier</th>
                                <th class="text-center">Current Stock</th>
                                <th class="text-center">Min Stock</th>
                                <th class="text-center">Max Stock</th>
                                <th class="text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($items as $item)
                                @php $totalQty = $item->storeItems->sum('quantity'); @endphp
                                <tr>
                                    <td class="ps-4 fw-bold">{{ $item->description }}</td>
                                    <td>{{ $item->category ?? 'N/A' }}</td>
                                    <td>{{ $item->supplier->name ?? 'N/A' }}</td>
                                    <td class="text-center fw-bold {{ $totalQty == 0 ? 'text-danger' : 'text-warning' }}">{{ $totalQty }}</td>
                                    <td class="text-center">{{ $item->min_stock }}</td>
                                    <td class="text-center">{{ $item->max_stock ?? 'N/A' }}</td>
                                    <td class="text-center">
                                        @if($totalQty == 0)
                                            <span class="badge bg-danger rounded-pill">Out of Stock</span>
                                        @else
                                            <span class="badge bg-warning text-dark rounded-pill">Low Stock</span>
                                        @endif
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
