@extends('layouts.master')

@section('title', 'Inventory Reports')

@section('page-content')
    <div class="container-fluid p-4">
        <h1 class="display-5 text-dark mb-4">Inventory Reports</h1>
        <p class="lead text-muted">A consolidated view of current stock, usage, and restock alerts.</p>

        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                <h5 class="mb-0 text-dark fw-bold">Current Stock by Store</h5>
                <div class="d-flex align-items-center">
                    <div class="col-md-8 me-2">
                        <label for="store_selector" class="visually-hidden">Select Store</label>
                        <select class="form-select" id="store_selector">
                            <option value="">Select a Store</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->id }}">{{ $store->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button id="loadStockBtn" class="btn btn-primary">
                        <i class="fas fa-search me-1"></i> Load Stock
                    </button>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="currentStockTable">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Description</th>
                                <th>Lot Number</th>
                                <th>Quantity</th>
                                <th>Expiry Date</th>
                                <th>Total Cost</th>
                            </tr>
                        </thead>
                        <tbody>
                            </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                <h5 class="mb-0 text-dark fw-bold">Restock Alerts</h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="restockAlertTable">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Store</th>
                                <th>Current Quantity</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $item)
                                @php
                                    $lowStockItems = $item->storeItems->filter(function($storeItem) {
                                        return $storeItem->quantity < 10; // Restock threshold
                                    });
                                @endphp
                                @foreach ($lowStockItems as $storeItem)
                                    <tr>
                                        <td>{{ $item->description }}</td>
                                        <td>{{ $storeItem->store->name }}</td>
                                        <td><span class="badge bg-danger">{{ $storeItem->quantity }}</span></td>
                                        <td><a href="{{ route('inventory.transfers.index') }}" class="btn btn-sm btn-warning">Transfer</a></td>
                                    </tr>
                                @endforeach
                            @endforeach
                            @if ($items->isEmpty() || $items->pluck('storeItems')->flatten()->where('quantity', '<', 10)->isEmpty())
                                <tr><td colspan="4" class="text-center">No items currently require restocking.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        
        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                <h5 class="mb-0 text-dark fw-bold">Item Usage History</h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="usageReportTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Item</th>
                                <th>Store</th>
                                <th>Quantity Used</th>
                                <th>Used For</th>
                                <th>Technician</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($usageLogs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('Y-m-d') }}</td>
                                    <td>{{ $log->item->description }}</td>
                                    <td>{{ $log->store->name }}</td>
                                    <td>{{ $log->quantity_used }}</td>
                                    <td>{{ $log->used_for }}</td>
                                    <td>{{ $log->technician_name ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                            @if ($usageLogs->isEmpty())
                                <tr><td colspan="6" class="text-center">No usage logs found.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                <h5 class="mb-0 text-dark fw-bold">Item Restock History</h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="restockReportTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Item</th>
                                <th>Store</th>
                                <th>Quantity Added</th>
                                <th>Lot Number</th>
                                <th>Restocked By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($restockLogs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('Y-m-d') }}</td>
                                    <td>{{ $log->item->description }}</td>
                                    <td>{{ $log->store->name }}</td>
                                    <td>{{ $log->quantity }}</td>
                                    <td>{{ $log->lot_number ?? 'N/A' }}</td>
                                    <td>{{ $log->restocked_by ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                            @if ($restockLogs->isEmpty())
                                <tr><td colspan="6" class="text-center">No restock logs found.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                <h5 class="mb-0 text-dark fw-bold">Item Transfer History</h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="transferReportTable">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Item</th>
                                <th>From Store</th>
                                <th>To Store</th>
                                <th>Quantity</th>
                                <th>Notes</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($transferLogs as $log)
                                <tr>
                                    <td>{{ $log->created_at->format('Y-m-d') }}</td>
                                    <td>{{ $log->item->description }}</td>
                                    <td>{{ $log->fromStore->name }}</td>
                                    <td>{{ $log->toStore->name }}</td>
                                    <td>{{ $log->quantity }}</td>
                                    <td>{{ $log->notes ?? 'N/A' }}</td>
                                </tr>
                            @endforeach
                            @if ($transferLogs->isEmpty())
                                <tr><td colspan="6" class="text-center">No transfer logs found.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card shadow border-0 mb-4">
            <div class="card-header bg-white border-bottom d-flex align-items-center justify-content-between">
                <h5 class="mb-0 text-dark fw-bold">Item Price History</h5>
            </div>
            <div class="card-body p-4">
                <div class="table-responsive">
                    <table class="table table-striped table-hover" id="priceHistoryTable">
                        <thead>
                            <tr>
                                <th>Item</th>
                                <th>Supplier</th>
                                <th>Price</th>
                                <th>Effective Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($priceHistory as $record)
                                <tr>
                                    <td>{{ $record->item->description }}</td>
                                    <td>{{ $record->supplier->name }}</td>
                                    <td>₦{{ number_format($record->price, 2) }}</td>
                                    <td>{{ \Carbon\Carbon::parse($record->effective_date)->format('Y-m-d') }}</td>
                                </tr>
                            @endforeach
                            @if ($priceHistory->isEmpty())
                                <tr><td colspan="4" class="text-center">No price history records found.</td></tr>
                            @endif
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
    <script>
        $(document).ready(function() {
            // Initialize all DataTables
            $('#restockAlertTable, #usageReportTable, #restockReportTable, #transferReportTable, #priceHistoryTable').DataTable();

            // Handle Current Stock Report based on a button click
            $('#loadStockBtn').on('click', function() {
                const storeId = $('#store_selector').val();
                const table = $('#currentStockTable');
                const tableBody = table.find('tbody');

                if ($.fn.DataTable.isDataTable('#currentStockTable')) {
                    table.DataTable().destroy();
                    tableBody.empty();
                }

                if (storeId) {
                    $.ajax({
                        url: `/inventory/api/stores/${storeId}/items`,
                        method: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            tableBody.empty();
                            if (response.length > 0) {
                                response.forEach(stock => {
                                    let row = `
                                        <tr>
                                            <td>${stock.item.description}</td>
                                            <td>${stock.item.category ?? 'N/A'}</td>
                                            <td>${stock.lot_number ?? 'N/A'}</td>
                                            <td>${stock.quantity}</td>
                                            <td>${stock.expiry_date ?? 'N/A'}</td>
                                            <td>$${parseFloat(stock.total_cost).toFixed(2)}</td>
                                        </tr>
                                    `;
                                    tableBody.append(row);
                                });
                            }
                            table.DataTable();
                        },
                        error: function() {
                            tableBody.empty();
                            tableBody.append('<tr><td colspan="6" class="text-center">Error loading stock data.</td></tr>');
                        }
                    });
                } else {
                    tableBody.empty();
                    tableBody.append('<tr><td colspan="6" class="text-center">Please select a store to view stock.</td></tr>');
                }
            });
        });
    </script>
@endsection