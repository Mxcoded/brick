@extends('restaurant::layouts.master')
@section('title', 'Waiter Dashboard')
@section('content')
    <div class="container-fluid content py-4">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold text-dark">Waiter Dashboard</h1>
            <p class="lead text-muted">Manage pending orders for your restaurant.</p>
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
                                    Order #{{ $order->id }} - Table {{ $order->table->number }}
                                    <span class="float-end">
                                        <span class="badge bg-success rounded-pill">{{ ucfirst($order->status) }}</span>
                                    </span>
                                </h3>
                                <small class="text-muted">Placed: {{ $order->created_at->format('d M Y H:i') }}</small>
                            </div>
                            <div class="card-body">
                                <button class="btn btn-link text-decoration-none p-0 mb-2" type="button" data-bs-toggle="collapse" data-bs-target="#orderDetails{{ $order->id }}" aria-expanded="false" aria-controls="orderDetails{{ $order->id }}">
                                    <i class="bi bi-chevron-down me-1"></i>View Order Details
                                </button>
                                <div class="collapse" id="orderDetails{{ $order->id }}">
                                    <table class="table table-sm table-borderless">
                                        <thead>
                                            <tr>
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
                                                <td colspan="3" class="text-end fw-bold">Total:</td>
                                                <td>{{ '₦' . number_format($order->orderItems->sum(function($item) {
                                                    return $item->menuItem ? $item->menuItem->price * $item->quantity : 0;
                                                }), 2) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                @if ($order->status === 'pending')
                                    <form action="{{ route('restaurant.waiter.accept', $order->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="btn btn-primary w-100 mt-3">Accept Order</button>
                                    </form>
                                @endif
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
                box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15) !important;
            }
            .card-header {
                background-color: #f8f9fa;
                padding: 1.25rem;
                border-radius: 0.75rem 0.75rem 0 0;
            }
            .btn-primary {
                background-color: #d9534f;
                border-color: #d9534f;
                transition: background-color 0.2s ease;
            }
            .btn-primary:hover {
                background-color: #c9302c;
                border-color: #c9302c;
            }
            .badge {
                font-size: 0.9rem;
                padding: 0.5em 0.75em;
            }
            .table-sm {
                font-size: 0.85rem;
            }
            .table-borderless th, .table-borderless td {
                padding: 0.5rem;
            }
            .btn-link {
                color: #d9534f;
            }
            .btn-link:hover {
                color: #c9302c;
                text-decoration: underline;
            }
            .alert {
                padding: 1.25rem;
                border-radius: 0.75rem;
            }
        </style>
    </div>

    <!-- Include Bootstrap Icons for chevrons and alerts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
@endsection