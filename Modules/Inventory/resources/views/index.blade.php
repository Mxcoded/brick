@extends('layouts.master')

@section('title', 'Inventory Dashboard')

@section('page-content')
    <div class="container-fluid p-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <h1 class="display-5 text-dark mb-0">Inventory Dashboard</h1>
                <p class="text-muted mb-0">Manage your stock, track transfers, and monitor usage across all stores.</p>
            </div>
            <div>
                @can('inventory.create')
                <a href="{{ route('inventory.items.create') }}" class="btn btn-primary shadow-sm me-2">
                    <i class="fas fa-plus-circle me-2"></i>Add New Item
                </a>
                @endcan
                @can('inventory.transfer')
                <a href="{{ route('inventory.transfers.index') }}" class="btn btn-info shadow-sm">
                    <i class="fas fa-exchange-alt me-2"></i>Transfer Items
                </a>
                @endcan
            </div>
        </div>

        @php
            $totalItems = $items->count();
            $totalValue = $items->sum(fn($i) => $i->storeItems->sum('total_cost'));
            $totalStores = $stores->count();
            $totalStockQty = $items->sum(fn($i) => $i->storeItems->sum('quantity'));
        @endphp

        @if($lowStockCount > 0)
            <div class="alert alert-warning d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>{{ $lowStockCount }} item(s)</strong> are below minimum stock level.
                </div>
                <a href="{{ route('inventory.low-stock') }}" class="btn btn-warning btn-sm">View All</a>
            </div>
        @endif

        @if($expiredCount > 0)
            <div class="alert alert-danger d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-skull-crossbones me-2"></i>
                    <strong>{{ $expiredCount }} lot(s)</strong> have expired.
                </div>
                <a href="{{ route('inventory.stock-aging') }}" class="btn btn-danger btn-sm">View Expiry Report</a>
            </div>
        @endif

        @if($expiringSoonCount > 0)
            <div class="alert alert-info d-flex justify-content-between align-items-center">
                <div>
                    <i class="fas fa-clock me-2"></i>
                    <strong>{{ $expiringSoonCount }} lot(s)</strong> expiring within 30 days.
                </div>
                <a href="{{ route('inventory.stock-aging') }}" class="btn btn-info btn-sm">View Details</a>
            </div>
        @endif

        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-primary shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">Total Items</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalItems }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-boxes fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-success shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-success text-uppercase mb-1">Total Stock Value</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">₦{{ number_format($totalValue, 2) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Pending Purchase Orders</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $pendingPOCount }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-shopping-cart fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Stores / Warehouses</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $totalStores }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-store fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-warning shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">Low Stock Items</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $lowStockCount }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-exclamation-triangle fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-danger shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">Expired Lots</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $expiredCount }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-skull-crossbones fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Expiring ≤30 Days</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $expiringSoonCount }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-clock fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-xl-3 col-md-6 mb-4">
                <div class="card border-left-info shadow h-100 py-2">
                    <div class="card-body">
                        <div class="row no-gutters align-items-center">
                            <div class="col mr-2">
                                <div class="text-xs font-weight-bold text-info text-uppercase mb-1">Total Stock (Qty)</div>
                                <div class="h5 mb-0 font-weight-bold text-gray-800">{{ number_format($totalStockQty) }}</div>
                            </div>
                            <div class="col-auto">
                                <i class="fas fa-cubes fa-2x text-gray-300"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if($reorderSuggestions->count() > 0)
        <div class="card shadow border-0 mb-4 border-start border-4 border-warning">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                <h5 class="mb-0 fw-bold text-warning"><i class="fas fa-clipboard-list me-2"></i>Reorder Suggestions</h5>
                <a href="{{ route('inventory.purchase-orders.create') }}" class="btn btn-sm btn-warning">Create Purchase Order</a>
            </div>
            <div class="card-body p-0">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Current Stock</th>
                            <th>Min Stock</th>
                            <th>Daily Usage (30d)</th>
                            <th>Stockout In</th>
                            <th>Suggested Order</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($reorderSuggestions as $rs)
                            <tr>
                                <td>{{ $rs->item->description }} <code class="ms-1">{{ $rs->item->sku }}</code></td>
                                <td class="text-danger fw-bold">{{ $rs->total_qty }}</td>
                                <td>{{ $rs->min_stock }}</td>
                                <td>{{ $rs->daily_usage }}</td>
                                <td>{{ $rs->stockout_in }}</td>
                                <td><strong>{{ number_format($rs->suggested_order) }}</strong></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

        <div class="row">
            <div class="col-lg-8">
                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                        <h5 class="mb-0 text-dark fw-bold">Current Stock Overview</h5>
                        <a href="{{ route('inventory.export.items') }}" class="btn btn-sm btn-success shadow-sm">
                            <i class="fas fa-file-excel me-1"></i>Export Excel
                        </a>
                    </div>
                    <div class="card-body p-4">
                        <div class="table-responsive">
                            <table class="table table-striped table-hover" id="inventoryTable">
                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">SKU</th>
                                        <th scope="col">Description</th>
                                        <th scope="col">Category</th>
                                        <th scope="col">Supplier</th>
                                        <th scope="col">Total Quantity</th>
                                        <th scope="col">Total Cost</th>
                                        <th scope="col">Unit</th>
                                        <th scope="col">Price</th>
                                        <th scope="col">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($items as $item)
                                        <tr>
                                            <td>{{ $item->id }}</td>
                                            <td><code>{{ $item->sku ?? '—' }}</code></td>
                                            <td>{{ $item->description }}</td>
                                            <td>{{ $item->category ?? 'N/A' }}</td>
                                            <td>{{ $item->supplier->name ?? 'N/A' }}</td>
                                            <td>{{ $item->storeItems->sum('quantity') }}</td>
                                            <td>₦{{ number_format($item->storeItems->sum('total_cost'), 2) }}</td>
                                            <td>{{ $item->unit_of_measurement ?? 'N/A' }}</td>
                                            <td>₦{{ number_format($item->price, 2) }}</td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-success me-2 restock-btn" data-bs-toggle="modal" data-bs-target="#restockModal" data-item-id="{{ $item->id }}" data-item-description="{{ $item->description }}">
                                                    <i class="fas fa-plus-circle"></i>
                                                </button>
                                                <a href="{{ route('inventory.items.edit', $item->id) }}" class="btn btn-sm btn-outline-primary me-2"><i class="fas fa-edit"></i></a>
                                                <form action="{{ route('inventory.items.destroy', $item->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Are you sure you want to delete this item? This action cannot be undone.');"><i class="fas fa-trash"></i></button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-lg-4">
                <div class="card shadow border-0 mb-4">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-history me-2"></i>Recent Activity</h5>
                    </div>
                    <div class="card-body p-0" style="max-height: 500px; overflow-y: auto;">
                        @forelse ($recentMovements as $movement)
                            <div class="border-bottom p-3">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">{{ $movement->created_at->diffForHumans() }}</small>
                                    @if ($movement->type === 'restock')
                                        <span class="badge bg-success">Restock</span>
                                    @elseif ($movement->type === 'usage')
                                        <span class="badge bg-warning text-dark">Usage</span>
                                    @elseif ($movement->type === 'transfer_out')
                                        <span class="badge bg-info">Transfer Out</span>
                                    @elseif ($movement->type === 'transfer_in')
                                        <span class="badge bg-info">Transfer In</span>
                                    @elseif ($movement->type === 'adjustment')
                                        <span class="badge bg-secondary">Adjustment</span>
                                    @elseif ($movement->type === 'stock_take')
                                        <span class="badge bg-primary">Stock Take</span>
                                    @else
                                        <span class="badge bg-dark">{{ $movement->type }}</span>
                                    @endif
                                </div>
                                <p class="mb-0 small">
                                    <strong>{{ $movement->item->description ?? 'N/A' }}</strong>
                                    ({{ $movement->quantity_delta > 0 ? '+' : '' }}{{ $movement->quantity_delta }})
                                    @if ($movement->store)
                                        at {{ $movement->store->name }}
                                    @endif
                                </p>
                                @if ($movement->user)
                                    <small class="text-muted">by {{ $movement->user->name }}</small>
                                @endif
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                No recent activity.
                            </div>
                        @endforelse
                    </div>
                </div>

                <div class="card shadow border-0">
                    <div class="card-header bg-white border-bottom">
                        <h5 class="mb-0 fw-bold"><i class="fas fa-balance-scale me-2"></i>Recent Adjustments</h5>
                    </div>
                    <div class="card-body p-0" style="max-height: 300px; overflow-y: auto;">
                        @forelse ($recentAdjustments as $adj)
                            <div class="border-bottom p-3">
                                <div class="d-flex justify-content-between">
                                    <small class="text-muted">{{ $adj->created_at->diffForHumans() }}</small>
                                    <span class="badge {{ $adj->type === 'write_off' ? 'bg-danger' : 'bg-info' }}">
                                        {{ str_replace('_', ' ', ucfirst($adj->type)) }}
                                    </span>
                                </div>
                                <p class="mb-0 small">
                                    <strong>{{ $adj->item->description ?? 'N/A' }}</strong>
                                    ({{ $adj->quantity_change > 0 ? '+' : '' }}{{ $adj->quantity_change }})
                                </p>
                                <small class="text-muted">{{ $adj->reason }}</small>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted">
                                <i class="fas fa-inbox fa-2x mb-2 d-block"></i>
                                No adjustments recorded.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    @include('inventory::_restock_modal')
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#inventoryTable').DataTable({
                "lengthMenu": [
                    [10, 25, 50, -1],
                    [10, 25, 50, "All"]
                ],
                "pageLength": 10
            });

            $('#inventoryTable').on('click', '.restock-btn', function() {
                const itemId = $(this).data('item-id');
                const itemDescription = $(this).data('item-description');
                $('#restockItemId').val(itemId);
                $('#restockItemName').text(itemDescription);
                $('#restockForm')[0].reset();
                $('#restockAlertContainer').html('');
            });

            $('#restockForm').on('submit', function(e) {
                e.preventDefault();
                const form = $(this);
                const alertContainer = $('#restockAlertContainer');
                $.ajax({
                    type: "POST",
                    url: form.attr('action'),
                    data: form.serialize(),
                    dataType: 'json',
                    success: function(response) {
                        alertContainer.html('<div class="alert alert-success alert-dismissible fade show" role="alert">' + response.message + '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>');
                        form.trigger('reset');
                        setTimeout(() => {
                             window.location.reload();
                        }, 1000);
                    },
                    error: function(xhr) {
                        let errors = xhr.responseJSON.errors;
                        let errorHtml = '<div class="alert alert-danger alert-dismissible fade show" role="alert"><strong>Oops! There were some errors.</strong><ul>';
                        $.each(errors, function(key, value) {
                            errorHtml += '<li>' + value[0] + '</li>';
                        });
                        errorHtml += '</ul><button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
                        alertContainer.html(errorHtml);
                    }
                });
            });
        });
    </script>
@endsection
