@extends('layouts.master')

@section('title', 'PO ' . $order->po_number)

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <div class="d-flex align-items-center gap-3">
                <h1 class="display-5 text-dark mb-0">PO: {{ $order->po_number }}</h1>
                @switch($order->status)
                    @case('draft')
                        <span class="badge bg-secondary fs-6">Draft</span>
                        @break
                    @case('pending_approval')
                        <span class="badge bg-warning text-dark fs-6">Pending Approval</span>
                        @break
                    @case('approved')
                        <span class="badge bg-primary fs-6">Approved</span>
                        @break
                    @case('partially_received')
                        <span class="badge bg-info fs-6">Partially Received</span>
                        @break
                    @case('received')
                        <span class="badge bg-success fs-6">Received</span>
                        @break
                    @case('cancelled')
                        <span class="badge bg-danger fs-6">Cancelled</span>
                        @break
                @endswitch
            </div>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('inventory.purchase-orders.pdf', $order) }}" class="btn btn-outline-danger shadow-sm">
                <i class="fas fa-file-pdf me-1"></i>PDF
            </a>
            @if($order->status === 'draft')
                <form action="{{ route('inventory.purchase-orders.approve', $order) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-primary shadow-sm" onclick="return confirm('Approve this order? This will allow stock to be received against it.')">
                        <i class="fas fa-check me-1"></i>Approve
                    </button>
                </form>
            @endif
            @if(in_array($order->status, ['approved', 'partially_received']))
                <button class="btn btn-success shadow-sm" data-bs-toggle="modal" data-bs-target="#receiveModal">
                    <i class="fas fa-boxes me-1"></i>Receive Stock
                </button>
            @endif
            @if(!in_array($order->status, ['received', 'cancelled']))
                <form action="{{ route('inventory.purchase-orders.cancel', $order) }}" method="POST" class="d-inline">
                    @csrf
                    <button class="btn btn-outline-danger shadow-sm" onclick="return confirm('Cancel this purchase order?')">
                        <i class="fas fa-times me-1"></i>Cancel
                    </button>
                </form>
            @endif
            <a href="{{ route('inventory.purchase-orders.index') }}" class="btn btn-secondary shadow-sm">
                <i class="fas fa-arrow-left me-1"></i>Back
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-file-invoice me-2"></i>Order Details</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <small class="text-muted d-block">PO Number</small>
                            <span class="fw-bold">{{ $order->po_number }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Supplier</small>
                            <span class="fw-bold">{{ $order->supplier->name }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Store</small>
                            <span class="fw-bold">{{ $order->store->name }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Created By</small>
                            <span>{{ $order->createdBy->name ?? 'N/A' }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Created At</small>
                            <span>{{ $order->created_at->format('d M Y - h:i A') }}</span>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">Approved By</small>
                            <span>{{ $order->approvedBy->name ?? '—' }}</span>
                        </div>
                        @if($order->notes)
                            <div class="col-12">
                                <small class="text-muted d-block">Notes</small>
                                <p class="mb-0">{{ $order->notes }}</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>

            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-list me-2"></i>Order Items</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-4">Item</th>
                                    <th class="text-center">Ordered</th>
                                    <th class="text-center">Received</th>
                                    <th class="text-center">Remaining</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end pe-4">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    @php $remaining = $item->quantity_ordered - $item->quantity_received; @endphp
                                    <tr>
                                        <td class="ps-4">{{ $item->item->description }}</td>
                                        <td class="text-center fw-bold">{{ $item->quantity_ordered }}</td>
                                        <td class="text-center">
                                            @if($item->quantity_received >= $item->quantity_ordered)
                                                <span class="text-success fw-bold">{{ $item->quantity_received }}</span>
                                            @elseif($item->quantity_received > 0)
                                                <span class="text-warning fw-bold">{{ $item->quantity_received }}</span>
                                            @else
                                                {{ $item->quantity_received }}
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($remaining > 0)
                                                <span class="fw-bold">{{ $remaining }}</span>
                                            @else
                                                <span class="text-success"><i class="fas fa-check"></i></span>
                                            @endif
                                        </td>
                                        <td class="text-end">₦{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-end pe-4 fw-bold">₦{{ number_format($item->quantity_ordered * $item->unit_price, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot class="table-light">
                                <tr>
                                    <th colspan="5" class="text-end ps-4">Total:</th>
                                    <th class="text-end pe-4">₦{{ number_format($order->items->sum(fn($i) => $i->quantity_ordered * $i->unit_price), 2) }}</th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-timeline me-2"></i>Status Timeline</h5>
                </div>
                <div class="card-body p-4">
                    <ul class="timeline">
                        <li class="timeline-item">
                            <div class="timeline-badge bg-success"><i class="fas fa-check"></i></div>
                            <div class="timeline-content">
                                <strong>Created</strong>
                                <small class="text-muted d-block">{{ $order->created_at->format('d M Y - h:i A') }}</small>
                                <small>by {{ $order->createdBy->name ?? 'N/A' }}</small>
                            </div>
                        </li>
                        @if($order->approved_at)
                            <li class="timeline-item">
                                <div class="timeline-badge bg-primary"><i class="fas fa-check"></i></div>
                                <div class="timeline-content">
                                    <strong>Approved</strong>
                                    <small class="text-muted d-block">{{ \Carbon\Carbon::parse($order->approved_at)->format('d M Y - h:i A') }}</small>
                                    <small>by {{ $order->approvedBy->name ?? 'N/A' }}</small>
                                </div>
                            </li>
                        @elseif($order->status !== 'cancelled')
                            <li class="timeline-item">
                                <div class="timeline-badge bg-secondary"><i class="fas fa-clock"></i></div>
                                <div class="timeline-content">
                                    <strong>Pending Approval</strong>
                                    <small class="text-muted d-block">Awaiting approval</small>
                                </div>
                            </li>
                        @endif
                        @if(in_array($order->status, ['partially_received', 'received']))
                            <li class="timeline-item">
                                <div class="timeline-badge bg-{{ $order->status === 'received' ? 'success' : 'info' }}">
                                    <i class="fas fa-{{ $order->status === 'received' ? 'check-double' : 'box' }}"></i>
                                </div>
                                <div class="timeline-content">
                                    <strong>{{ $order->status === 'received' ? 'Fully Received' : 'Partially Received' }}</strong>
                                    <small class="text-muted d-block">{{ $order->updated_at->format('d M Y - h:i A') }}</small>
                                </div>
                            </li>
                        @endif
                        @if($order->status === 'cancelled')
                            <li class="timeline-item">
                                <div class="timeline-badge bg-danger"><i class="fas fa-times"></i></div>
                                <div class="timeline-content">
                                    <strong>Cancelled</strong>
                                    <small class="text-muted d-block">{{ $order->updated_at->format('d M Y - h:i A') }}</small>
                                </div>
                            </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .timeline {
        list-style: none;
        padding: 0;
        margin: 0;
        position: relative;
    }
    .timeline::before {
        content: '';
        position: absolute;
        left: 18px;
        top: 0;
        bottom: 0;
        width: 2px;
        background: #e3e6f0;
    }
    .timeline-item {
        position: relative;
        padding-left: 50px;
        padding-bottom: 24px;
    }
    .timeline-item:last-child {
        padding-bottom: 0;
    }
    .timeline-badge {
        position: absolute;
        left: 0;
        width: 38px;
        height: 38px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #fff;
        font-size: 0.85rem;
        z-index: 1;
    }
    .timeline-content strong {
        display: block;
        margin-bottom: 2px;
    }
    .timeline-content small {
        font-size: 0.8rem;
    }
</style>

@if(in_array($order->status, ['approved', 'partially_received']))
<div class="modal fade" id="receiveModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Receive Stock — {{ $order->po_number }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST" action="{{ route('inventory.purchase-orders.receive', $order) }}">
                @csrf
                <div class="modal-body">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th class="text-center">Ordered</th>
                                    <th class="text-center">Received</th>
                                    <th class="text-center">To Receive</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($order->items as $item)
                                    @php $canReceive = $item->quantity_ordered - $item->quantity_received; @endphp
                                    <tr>
                                        <td>{{ $item->item->description }}</td>
                                        <td class="text-center fw-bold">{{ $item->quantity_ordered }}</td>
                                        <td class="text-center">{{ $item->quantity_received }}</td>
                                        <td class="text-center" style="width:150px;">
                                            <input type="hidden" name="items[{{ $loop->index }}][id]" value="{{ $item->id }}">
                                            @if($canReceive > 0)
                                                <input type="number" class="form-control text-center" name="items[{{ $loop->index }}][quantity_received]"
                                                       max="{{ $canReceive }}" min="0" placeholder="0" value="{{ $canReceive }}">
                                                <small class="text-muted">Max: {{ $canReceive }}</small>
                                            @else
                                                <span class="text-success"><i class="fas fa-check-circle"></i> Complete</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-success px-4">
                        <i class="fas fa-boxes me-2"></i>Receive Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection
