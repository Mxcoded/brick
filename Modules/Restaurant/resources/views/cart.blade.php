@extends('restaurant::layouts.master')
@section('title', 'Cart')
@section('content')
    <div class="container-fluid content py-4">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold text-dark">
                @if ($type === 'table')
                    Cart - Table {{ $sourceModel->number }}
                @elseif ($type === 'room')
                    Cart - Room {{ $sourceModel->name }}
                @else
                    Online Cart
                @endif
            </h1>
            <p class="lead text-muted">Review your items before placing your order.</p>
        </div>

        @if (empty($cart))
            <div class="alert alert-info text-center rounded-3 shadow-sm">
                <i class="bi bi-info-circle me-2"></i>Your cart is empty.
            </div>
        @else
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="shadow-lg border-0 rounded-3">
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
                                            $quantity = $item['quantity'] ?? 0;
                                            $unitPrice = $menuItem->price ?? 0;
                                            $totalPrice = $unitPrice * $quantity;
                                        @endphp
                                        <tr>
                                            <td>
                                                <img src="{{ $menuItem && $menuItem->image && file_exists(public_path('storage/' . $menuItem->image)) ? asset('storage/' . $menuItem->image) : asset('storage/images/menudefaultimage.png') }}"
                                                    alt="{{ $menuItem->name ?? 'No Image' }}"
                                                    class="img-fluid rounded"
                                                    style="width: 60px; height: 60px; object-fit: cover;">
                                            </td>
                                            <td>{{ $menuItem->name ?? 'Item not found' }}</td>
                                            <td>
                                                {{ $quantity }}
                                            </td>
                                            <td>{{ $menuItem ? '₦' . number_format($menuItem->price, 2) : 'N/A' }}</td>
                                            <td>{{ $item['instructions'] ?: 'None' }}</td>
                                            <td>{{ $menuItem ? '₦' . number_format($totalPrice, 2) : 'N/A' }}</td>
                                            <td>
                                                <form action="{{ route($type === 'online' ? 'restaurant.online.cart.remove' : 'restaurant.cart.remove', $type === 'online' ? [] : [$type, $sourceModel->id]) }}" method="POST">
                                                    @csrf
                                                    <input type="hidden" name="index" value="{{ $index }}">
                                                    <button type="submit" class="btn btn-danger btn-sm">Remove</button>
                                                </form>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="5" class="text-end fw-bold">Total:</td>
                                        <td>{{ '₦' . number_format(
                                            array_sum(
                                                array_map(function ($item) use ($items) {
                                                    return isset($items[$item['item_id']]) ? $items[$item['item_id']]->price * $item['quantity'] : 0;
                                                }, $cart)
                                            ), 2
                                        ) }}</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                            @if ($type === 'online')
                                <div class="col-md-6">
                                    <h3 class="fw-bold mb-3">Delivery Details</h3>
                                    <form action="{{ route('restaurant.online.order.submit') }}" method="POST" onsubmit="return validateDeliveryForm()">
                                        @csrf
                                        <div class="mb-3">
                                            <label for="customer_name" class="form-label">Name</label>
                                            <input type="text" name="customer_name" id="customer_name"
                                                class="form-control @error('customer_name') is-invalid @enderror"
                                                value="{{ old('customer_name') }}" required minlength="2">
                                            @error('customer_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="customer_phone" class="form-label">Phone Number</label>
                                            <input type="tel" name="customer_phone" id="customer_phone"
                                                class="form-control @error('customer_phone') is-invalid @enderror"
                                                value="{{ old('customer_phone') }}" required pattern="[0-9]{10,15}">
                                            @error('customer_phone')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="delivery_address" class="form-label">Delivery Address</label>
                                            <textarea name="delivery_address" id="delivery_address"
                                                class="form-control @error('delivery_address') is-invalid @enderror" rows="4" required minlength="10">{{ old('delivery_address') }}</textarea>
                                            @error('delivery_address')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-lg w-100">Place Order</button>
                                    </form>
                                </div>
                            @endif
                            @if ($type !== 'online')
                                <form action="{{ route($type === 'online' ? 'restaurant.online.order.submit' : 'restaurant.order.submit', $type === 'online' ? [] : [$type, $sourceModel->id]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-primary btn-lg w-100">Place Order</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="text-center mt-4">
            <a href="{{ route($type === 'online' ? 'restaurant.online.menu' : 'restaurant.menu', $type === 'online' ? [] : [$type, $sourceModel->id]) }}"
                class="btn btn-outline-primary btn-lg">Back to Menu</a>
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
            .form-control, .form-control-sm {
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

        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    </div>

    <script>
        function validateDeliveryForm() {
            const name = document.getElementById('customer_name').value;
            const phone = document.getElementById('customer_phone').value;
            const address = document.getElementById('delivery_address').value;

            if (name.length < 2) {
                alert('Name must be at least 2 characters long.');
                return false;
            }
            if (!/^[0-9]{10,15}$/.test(phone)) {
                alert('Phone number must be between 10 and 15 digits.');
                return false;
            }
            if (address.length < 10) {
                alert('Delivery address must be at least 10 characters long.');
                return false;
            }
            return true;
        }
    </script>
@endsection