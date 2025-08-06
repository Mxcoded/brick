@extends('restaurant::layouts.master')
@section('title', 'Online Cart')
@section('content')
    <div class="container-fluid content py-4">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold text-dark">Your Online Cart</h1>
            <p class="lead text-muted">Review your items and provide delivery details.</p>
        </div>

        @if (empty($cart))
            <div class="alert alert-info text-center rounded-3 shadow-sm">
                <i class="bi bi-info-circle me-2"></i>Your cart is empty.
            </div>
        @else
            <div class="row">
                <div class="col-md-10 offset-md-1">
                    <div class="card shadow-lg border-0 rounded-3">
                        <div class="card-body">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Image</th>
                                        <th>Item</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Instructions</th>
                                        <th>Total</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($cart as $index => $item)
                                        @php
                                            $menuItem = $items[$item['item_id']] ?? null;
                                        @endphp
                                        <tr>
                                            <td>
                                                @if ($menuItem && $menuItem->image)
                                                    <img src="{{ asset('storage/' . $menuItem->image) }}" alt="{{ $menuItem->name }}" class="img-fluid rounded" style="max-width: 60px; max-height: 60px; object-fit: cover;">
                                                @else
                                                    <img src="https://via.placeholder.com/60x60?text=No+Image" alt="No Image" class="img-fluid rounded" style="max-width: 60px; max-height: 60px; object-fit: cover;">
                                                @endif
                                            </td>
                                            <td>{{ $menuItem ? $menuItem->name : 'Item not found' }}</td>
                                            <td>
                                                <form action="{{ route('restaurant.online.cart.update') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="index" value="{{ $index }}">
                                                    <input type="number" name="quantity" value="{{ $item['quantity'] }}" min="1" class="form-control form-control-sm d-inline-block" style="width: 80px;">
                                                    <button type="submit" class="btn btn-sm btn-primary mt-1">Update</button>
                                                </form>
                                            </td>
                                            <td>{{ $menuItem ? '₦' . number_format($menuItem->price, 2) : 'N/A' }}</td>
                                            <td>{{ $item['instructions'] ?: 'None' }}</td>
                                            <td>{{ $menuItem ? '₦' . number_format($menuItem->price * $item['quantity'], 2) : 'N/A' }}</td>
                                            <td>
                                                <form action="{{ route('restaurant.online.cart.remove') }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="index" value="{{ $index }}">
                                                    <button type="submit" class="btn btn-sm btn-danger">Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-end fw-bold">Total:</td>
                                        <td>{{ '₦' . number_format(array_sum(array_map(function($item) use ($items) {
                                            return isset($items[$item['item_id']]) ? $items[$item['item_id']]->price * $item['quantity'] : 0;
                                        }, $cart)), 2) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                            <h3 class="fw-bold mt-4">Delivery Details</h3>
                            <form action="{{ route('restaurant.online.order.submit') }}" method="POST">
                                @csrf
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="customer_name" class="form-label">Name</label>
                                            <input type="text" name="customer_name" id="customer_name" class="form-control @error('customer_name') is-invalid @enderror" required>
                                            @error('customer_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="mb-3">
                                            <label for="customer_phone" class="form-label">Phone</label>
                                            <input type="text" name="customer_phone" id="customer_phone" class="form-control @error('customer_phone') is-invalid @enderror" required>
                                            @error('customer_phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="delivery_address" class="form-label">Delivery Address</label>
                                    <textarea name="delivery_address" id="delivery_address" class="form-control @error('delivery_address') is-invalid @enderror" rows="4" required></textarea>
                                    @error('delivery_address')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary btn-lg w-100">Place Order</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
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
            .btn-primary {
                background-color: #d9534f;
                border-color: #d9534f;
            }
            .btn-primary:hover {
                background-color: #c9302c;
                border-color: #c9302c;
            }
            .btn-danger {
                background-color: #dc3545;
                border-color: #dc3545;
            }
            .btn-danger:hover {
                background-color: #c82333;
                border-color: #c82333;
            }
            .form-control, .form-control-sm, textarea {
                border-radius: 0.5rem;
            }
            .alert {
                padding: 1.25rem;
                border-radius: 0.75rem;
            }
            .table img {
                border-radius: 0.5rem;
            }
        </style>

        <!-- Include Bootstrap Icons for alerts -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    </div>
@endsection