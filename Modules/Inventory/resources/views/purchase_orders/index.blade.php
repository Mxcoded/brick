@extends('layouts.master')

@section('title', 'Purchase Orders')

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 text-dark mb-0">Purchase Orders</h1>
            <p class="text-muted mb-0">Manage procurement from suppliers</p>
        </div>
        <div>
            <a href="{{ route('inventory.export.purchase-orders') }}" class="btn btn-success shadow-sm me-2">
                <i class="fas fa-file-excel me-1"></i>Export Excel
            </a>
            <a href="{{ route('inventory.purchase-orders.create') }}" class="btn btn-primary shadow-sm">
                <i class="fas fa-plus-circle me-2"></i>New Purchase Order
            </a>
        </div>
    </div>

    @php
        $totalOrders = $orders->count();
        $pendingOrders = $orders->whereIn('status', ['draft', 'pending_approval'])->count();
        $receivedOrders = $orders->where('status', 'received')->count();
    @endphp

    <div class="row mb-4">
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card border-left-primary shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Orders</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalOrders }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-shopping-cart fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Pending / Draft</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingOrders }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-clock fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-4 col-md-6 mb-3">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Received</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $receivedOrders }}</div>
                        </div>
                        <div class="col-auto"><i class="fas fa-check-circle fa-2x text-gray-300"></i></div>
                    </div>
                </div>
            </div>
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
            <h5 class="mb-0 fw-bold">All Purchase Orders</h5>
            <div class="input-group" style="max-width: 300px;">
                <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                <input type="text" class="form-control" id="tableSearch" placeholder="Search orders...">
            </div>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="ordersTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">PO #</th>
                            <th>Supplier</th>
                            <th>Store</th>
                            <th class="text-center">Items</th>
                            <th class="text-end">Total</th>
                            <th class="text-center">Status</th>
                            <th>Created</th>
                            <th class="text-center">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $order)
                            <tr>
                                <td class="ps-4 fw-bold">{{ $order->po_number }}</td>
                                <td>{{ $order->supplier->name }}</td>
                                <td>{{ $order->store->name }}</td>
                                <td class="text-center">
                                    <span class="badge bg-secondary rounded-pill">{{ $order->items->count() }}</span>
                                </td>
                                <td class="text-end fw-bold">
                                    ₦{{ number_format($order->items->sum(fn($i) => $i->quantity_ordered * $i->unit_price), 2) }}
                                </td>
                                <td class="text-center">
                                    @switch($order->status)
                                        @case('draft')
                                            <span class="badge bg-secondary">Draft</span>
                                            @break
                                        @case('pending_approval')
                                            <span class="badge bg-warning text-dark">Pending</span>
                                            @break
                                        @case('approved')
                                            <span class="badge bg-primary">Approved</span>
                                            @break
                                        @case('partially_received')
                                            <span class="badge bg-info">Partial</span>
                                            @break
                                        @case('received')
                                            <span class="badge bg-success">Received</span>
                                            @break
                                        @case('cancelled')
                                            <span class="badge bg-danger">Cancelled</span>
                                            @break
                                    @endswitch
                                </td>
                                <td>{{ $order->created_at->format('d M Y') }}</td>
                                <td class="text-center">
                                    <a href="{{ route('inventory.purchase-orders.show', $order) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                        <i class="fas fa-eye me-1"></i>View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-shopping-cart fa-3x mb-3 d-block"></i>
                                    No purchase orders yet.
                                    <a href="{{ route('inventory.purchase-orders.create') }}" class="d-block mt-2">Create your first order</a>
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
        const query = this.value.toLowerCase();
        document.querySelectorAll('#ordersTable tbody tr').forEach(row => {
            row.style.display = row.textContent.toLowerCase().includes(query) ? '' : 'none';
        });
    });
</script>
@endsection
