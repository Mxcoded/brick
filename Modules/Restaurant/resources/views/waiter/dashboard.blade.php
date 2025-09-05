@extends('restaurant::layouts.master')
@section('title', 'Waiter Dashboard')
@section('content')
    <div class="container-fluid content py-4">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold text-dark">Waiter Dashboard</h1>
            <p class="lead text-muted">Manage new and active orders for tables and rooms.</p>
        </div>

        <ul class="nav nav-pills nav-fill mb-4" id="pills-tab" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="pills-new-orders-tab" data-bs-toggle="pill" data-bs-target="#pills-new-orders" type="button" role="tab" aria-controls="pills-new-orders" aria-selected="true">
                    <i class="bi bi-bell-fill me-2"></i>New Orders <span class="badge bg-danger ms-1">{{ $pendingOrders->count() }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="pills-active-orders-tab" data-bs-toggle="pill" data-bs-target="#pills-active-orders" type="button" role="tab" aria-controls="pills-active-orders" aria-selected="false">
                    <i class="bi bi-person-walking me-2"></i>My Active Orders <span class="badge bg-secondary ms-1">{{ $activeOrders->count() }}</span>
                </button>
            </li>
        </ul>

        <div class="tab-content" id="pills-tabContent">
            <div class="tab-pane fade show active" id="pills-new-orders" role="tabpanel" aria-labelledby="pills-new-orders-tab">
                @if ($pendingOrders->isEmpty())
                    <div class="alert alert-info text-center rounded-3 shadow-sm"><i class="bi bi-info-circle me-2"></i>No new orders to accept.</div>
                @else
                    <div class="row g-4">
                        @foreach ($pendingOrders as $order)
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 shadow-lg border-0 rounded-3">
                                    <div class="card-header bg-light border-bottom-0">
                                        <h3 class="card-title fw-bold mb-0">Order #{{ $order->id }} - {{ ucfirst($order->type) }}</h3>
                                        <small class="text-muted">Placed: {{ $order->created_at->diffForHumans() }}</small>
                                    </div>
                                    <div class="card-body d-flex flex-column">
                                        <p class="mb-2"><strong>{{ $order->type === 'table' ? 'Table' : 'Room' }}:</strong> {{ $order->source->name ?? $order->source->number ?? 'N/A' }}</p>
                                        <button class="btn btn-link text-decoration-none p-0 mb-2 align-self-start" type="button" data-bs-toggle="collapse" data-bs-target="#orderDetails{{ $order->id }}">
                                            <i class="bi bi-chevron-down me-1"></i>View Details
                                        </button>
                                        <div class="collapse" id="orderDetails{{ $order->id }}">
                                            @include('restaurant::partials._order_details', ['order' => $order])
                                        </div>
                                    </div>
                                    <div class="card-footer bg-transparent border-0 p-3">
                                        <div class="btn-group w-100" role="group" aria-label="Order Actions">
                                            <form action="{{ route('restaurant.waiter.accept', $order->id) }}" method="POST" class="w-100">
                                                @csrf
                                                <button type="submit" class="btn btn-success w-100 rounded-end-0 fw-bold"><i class="bi bi-check-circle-fill me-1"></i>Accept</button>
                                            </form>
                                            <button type="button" class="btn btn-danger w-100 rounded-start-0 fw-bold" data-bs-toggle="modal" data-bs-target="#reasonModal" data-action="reject" data-order-id="{{ $order->id }}">
                                                <i class="bi bi-x-circle-fill me-1"></i>Reject
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            <div class="tab-pane fade" id="pills-active-orders" role="tabpanel" aria-labelledby="pills-active-orders-tab">
                @if ($activeOrders->isEmpty())
                    <div class="alert alert-secondary text-center rounded-3 shadow-sm"><i class="bi bi-info-circle me-2"></i>You have no active orders.</div>
                @else
                     <div class="row g-4">
                        @foreach ($activeOrders as $order)
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 shadow-lg border-0 rounded-3">
                                    <div class="card-header border-bottom-0 d-flex justify-content-between align-items-center" style="background-color: #e7f1ff;">
                                        <div>
                                            <h3 class="card-title fw-bold mb-0">Order #{{ $order->id }}</h3>
                                            <small class="text-muted">Accepted: {{ $order->updated_at->diffForHumans() }}</small>
                                        </div>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary rounded-circle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="width: 38px; height: 38px;">
                                                <i class="bi bi-three-dots-vertical"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li><button class="dropdown-item text-danger" type="button" data-bs-toggle="modal" data-bs-target="#reasonModal" data-action="void" data-order-id="{{ $order->id }}">Void Order</button></li>
                                            </ul>
                                        </div>
                                    </div>
                                    <div class="card-body">
                                         <p class="mb-2"><strong>{{ $order->type === 'table' ? 'Table' : 'Room' }}:</strong> {{ $order->source->name ?? $order->source->number ?? 'N/A' }}</p>
                                        <p><strong>Current Status:</strong> <span class="badge rounded-pill bg-primary">{{ ucfirst($order->tracking_status) }}</span></p>
                                        <button class="btn btn-link text-decoration-none p-0 mb-3 align-self-start" type="button" data-bs-toggle="collapse" data-bs-target="#activeOrderDetails{{ $order->id }}">
                                            <i class="bi bi-chevron-down me-1"></i>View Details
                                        </button>
                                        <div class="collapse" id="activeOrderDetails{{ $order->id }}">
                                            @include('restaurant::partials._order_details', ['order' => $order])
                                        </div>
                                    </div>
                                    <div class="card-footer bg-white border-0 p-3">
                                        <form action="{{ route('restaurant.waiter.update-status', $order->id) }}" method="POST" class="mb-2">
                                            @csrf
                                            <div class="input-group">
                                                <select name="tracking_status" class="form-select">
                                                    <option value="preparing" @if($order->tracking_status == 'preparing') selected @endif>Preparing</option>
                                                    <option value="ready" @if($order->tracking_status == 'ready') selected @endif>Ready for Pickup</option>
                                                    <option value="served" @if($order->tracking_status == 'served') selected @endif>Served</option>
                                                </select>
                                                <button class="btn btn-outline-primary" type="submit">Update</button>
                                            </div>
                                        </form>
                                        <form action="{{ route('restaurant.waiter.update-status', $order->id) }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="tracking_status" value="paid"/>
                                            <input type="hidden" name="status" value="completed"/>
                                            <button type="submit" class="btn btn-success w-100"><i class="bi bi-credit-card-fill me-2"></i>Mark as Paid</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="modal fade" id="reasonModal" tabindex="-1" aria-labelledby="reasonModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="reasonModalLabel">Provide Reason</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="reasonForm" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p>Please provide a reason for <strong id="modalActionText"></strong> order #<strong id="modalOrderIdText"></strong>.</p>
                            <textarea name="reason" class="form-control" rows="3" placeholder="e.g., Item out of stock, customer cancelled..." required></textarea>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn" id="modalSubmitButton">Confirm</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        
        <style>
            .nav-pills .nav-link {
                color: #6c757d;
                font-weight: 600;
                font-size: 1.1rem;
                padding: 0.75rem 1.5rem;
                border: 2px solid transparent;
                transition: all 0.3s ease;
            }
            .nav-pills .nav-link.active, .nav-pills .show>.nav-link {
                background-color: #0d6efd !important; /* Changed to blue for consistency */
                color: white;
                box-shadow: 0 4px 12px rgba(13, 110, 253, 0.4);
            }
            .card {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                border-radius: 0.75rem;
            }
            .card:hover {
                transform: translateY(-5px);
                box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
            }
        </style>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const reasonModal = document.getElementById('reasonModal');
    if (reasonModal) {
        reasonModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const action = button.getAttribute('data-action');
            const orderId = button.getAttribute('data-order-id');

            const form = reasonModal.querySelector('#reasonForm');
            const modalTitle = reasonModal.querySelector('#reasonModalLabel');
            const actionText = reasonModal.querySelector('#modalActionText');
            const orderIdText = reasonModal.querySelector('#modalOrderIdText');
            const submitButton = reasonModal.querySelector('#modalSubmitButton');

            let url = '';
            if (action === 'reject') {
                url = `/restaurant-waiter/order/${orderId}/reject`;
                modalTitle.textContent = 'Reject Order';
                actionText.textContent = 'rejecting';
                submitButton.className = 'btn btn-danger';
                submitButton.textContent = 'Reject Order';
            } else if (action === 'void') {
                url = `/restaurant-waiter/order/${orderId}/void`;
                modalTitle.textContent = 'Void Order';
                actionText.textContent = 'voiding';
                submitButton.className = 'btn btn-warning text-dark';
                submitButton.textContent = 'Void Order';
            }

            form.action = url;
            orderIdText.textContent = orderId;
        });
    }
});
</script>
@endpush