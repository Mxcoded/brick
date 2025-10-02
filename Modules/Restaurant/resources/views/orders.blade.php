@extends('restaurant::layouts.master')
@section('title', 'Order History')
@section('content')
    <div class="container-fluid content py-4">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold text-dark">Order History</h1>
            <p class="lead text-muted">Enter your phone number to view your past orders.</p>
        </div>

        <!-- Phone Number Form -->
        <div class="row mb-5">
            <div class="col-md-6 offset-md-3">
                <div class="card shadow-lg border-0 rounded-3">
                    <div class="card-body">
                        <form action="{{ route('restaurant.online.orders') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="customer_phone" class="form-label">Phone Number</label>
                                <input type="text" name="customer_phone" id="customer_phone"
                                    class="form-control @error('customer_phone') is-invalid @enderror"
                                    value="{{ old('customer_phone', $phone) }}" required>
                                @error('customer_phone')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-primary w-100">View Orders</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders List -->
        @if ($phone)
            @if ($orders->isEmpty())
                <div class="alert alert-info text-center rounded-3 shadow-sm">
                    <i class="bi bi-info-circle me-2"></i>No orders found for this phone number.
                </div>
            @else
                <div class="row g-4">
                    @foreach ($orders as $order)
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 shadow-lg border-0 rounded-3">
                                <div class="card-header bg-light border-bottom-0">
                                    <h3 class="card-title fw-bold mb-0">
                                        Order #{{ $order->id }}
                                        <span class="float-end">
                                            <span class="badge bg-success rounded-pill">{{ ucfirst($order->status) }}</span>
                                        </span>
                                    </h3>
                                    <small class="text-muted">Placed: {{ $order->created_at->format('d M Y H:i') }}</small>
                                </div>
                                <div class="card-body">
                                    <p><strong>Customer:</strong> {{ $order->customer_name }}</p>
                                    <p><strong>Phone:</strong> {{ $order->customer_phone }}</p>
                                    <p><strong>Address:</strong> {{ $order->delivery_address }}</p>
                                    <p><strong>Tracking:</strong> {{ ucfirst($order->tracking_status ?? 'Pending') }}</p>
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
                                                            <img src="{{ $item->menuItem && $item->menuItem->image && file_exists(public_path('storage/' . $item->menuItem->image)) ? asset('storage/' . $item->menuItem->image) : asset('storage/images/menudefaultimage.png') }}"
                                                                alt="{{ $item->menuItem ? $item->menuItem->name : 'No Image' }}"
                                                                class="img-fluid rounded"
                                                                style="max-width: 50px; max-height: 50px; object-fit: cover;">
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
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        @endif

        <div class="text-center mt-4">
            <a href="{{ route('restaurant.online.menu') }}" class="btn btn-outline-primary btn-lg">Back to Menu</a>
        </div>

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
            .form-control {
                border-radius: 0.5rem;
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