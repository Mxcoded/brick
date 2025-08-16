@extends('restaurant::layouts.master')
@section('title', 'Waiter Dashboard')
@section('content')
    <div class="container-fluid content py-4">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold text-dark">Waiter Dashboard</h1>
            <p class="lead text-muted">Manage pending orders for tables and rooms.</p>
        </div>

        @if ($orders->isEmpty())
            <div class="alert alert-info text-center rounded-3 shadow-sm">
                <i class="bi bi-info-circle me-2"></i>No pending orders at the moment.
            </div>
        @else
            <div class="row g-4">
                @foreach ($orders as $order)
                    <div class="col-md-6 col-lg-4">
                        <div class="card h-100 shadow-lg border-0 rounded-3">
                            <div class="card-header bg-light border-bottom-0">
                                <h3 class="card-title fw-bold mb-0">
                                    Order #{{ $order->id }} - {{ ucfirst($order->type) }}
                                    <span class="float-end">
                                        <span class="badge bg-success rounded-pill">{{ ucfirst($order->status) }}</span>
                                    </span>
                                </h3>
                                <small class="text-muted">Placed: {{ $order->created_at->format('d M Y H:i') }}</small>
                            </div>
                            <div class="card-body">
                                <p><strong>{{ $order->type === 'table' ? 'Table' : 'Room' }}:</strong> {{ $order->source->name ?? 'N/A' }}</p>
                                @if ($order->type === 'room')
                                    <p><strong>Tracking:</strong> {{ ucfirst($order->tracking_status ?? 'Pending') }}</p>
                                @endif
                                <button class="btn btn-link text-decoration-none p-0 mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#orderDetails{{ $order->id }}" aria-expanded="false" aria-controls="orderDetails{{ $order->id }}">
                                    <i class="bi bi-chevron-down me-1"></i>View Order Details
                                </button>
                                <div class="collapse" id="orderDetails{{ $order->id }}">
                                    <table class="table table-sm table-borderless">
                                        <thead>
                                            <tr>
                                                <th>Image</th>
                                                <th>Item</th>
                                                <th>Qty</th>
                                                <th>Price</th>
                                                <th>Total</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($order->orderItems as $item)
                                                <tr>
                                                    <td>
                                                        @if ($item->menuItem && $item->menuItem->image)
                                                            <img src="{{ asset('storage/' . $item->menuItem->image) }}" alt="{{ $item->menuItem->name }}" class="img-fluid rounded" style="max-width: 50px; max-height: 50px; object-fit: cover;">
                                                        @else
                                                            <img src="https://via.placeholder.com/50x50?text=No+Image" alt="No Image" class="img-fluid rounded" style="max-width: 50px; max-height: 50px; object-fit: cover;">
                                                        @endif
                                                    </td>
                                                    <td>
                                                        {{ $item->menuItem ? $item->menuItem->name : 'Item not found' }}
                                                        @if ($item->instructions)
                                                            <small class="text-muted d-block">{{ $item->instructions }}</small>
                                                        @endif
                                                    </td>
                                                    <td>{{ $item->quantity }}</td>
                                                    <td>{{ $item->menuItem ? '₦' . number_format($item->menuItem->price, 2) : 'N/A' }}</td>
                                                    <td>{{ $item->menuItem ? '₦' . number_format($item->menuItem->price * $item->quantity, 2) : 'N/A' }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                        <tfoot>
                                            <tr>
                                                <td colspan="4" class="text-end fw-bold">Total:</td>
                                                <td>{{ '₦' . number_format($order->orderItems->sum(function($item) {
                                                    return $item->menuItem ? $item->menuItem->price * $item->quantity : 0;
                                                }), 2) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <form action="{{ route('restaurant.waiter.accept', $order->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary w-100">Accept Order</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <style>
            .card {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                border-radius: 0.75rem;
            }
            .card:hover {
                transform: translateY(-5px);
                box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
            }
            .card-header {
                background-color: #f8f9fa;
                padding: 1.25rem;
                border-radius: 0.75rem 0.75rem 0 0;
            }
            .btn-primary {
                background-color: #d9534f;
                border-color: #d9534f;
            }
            .btn-primary:hover {
                background-color: #c9302c;
                border-color: #c9302c;
            }
            .alert {
                padding: 1.25rem;
                border-radius: 0.75rem;
            }
            .table img {
                border-radius: 0.5rem;
            }
            .btn-link {
                color: #d9534f;
            }
            .btn-link:hover {
                color: #c9302c;
                text-decoration: underline;
            }
            .table-sm {
                font-size: 0.85rem;
            }
            .table-borderless th, .table-borderless td {
                padding: 0.5rem;
            }
            .badge {
                font-size: 0.9rem;
                padding: 0.5em 0.75em;
            }
        </style>

        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    </div>
@endsection