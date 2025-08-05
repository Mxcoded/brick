@extends('restaurant::layouts.master')
@section('title', 'Your Cart')
@section('content')
    <div class="container-fluid content">
        <div class="text-center mb-4">
            <h1 class="display-4 fw-bold">Your Cart - Table {{ $table->number }}</h1>
            <p class="lead text-muted">Review your selections before placing your order.</p>
        </div>

        @if (empty($cart))
            <div class="alert alert-info text-center">
                Your cart is empty. <a href="{{ route('restaurant.menu', $table) }}" class="alert-link">Browse the menu</a> to add items.
            </div>
        @else
            <div class="row">
                @foreach ($cart as $index => $cartItem)
                    @if (isset($items[$cartItem['item_id']]))
                        <div class="col-md-6 col-lg-4 mb-4">
                            <div class="card h-100 shadow-sm border-0">
                                <div class="card-body">
                                    <h3 class="card-title fw-bold">
                                        {{ $items[$cartItem['item_id']]->name }}
                                        <span class="float-end text-primary">&#8358; {{ number_format($items[$cartItem['item_id']]->price * $cartItem['quantity'], 2) }}</span>
                                    </h3>
                                    <p class="card-text text-muted">
                                        Unit Price: &#8358; {{ number_format($items[$cartItem['item_id']]->price, 2) }}<br>
                                        Quantity: {{ $cartItem['quantity'] }}<br>
                                        Instructions: {{ $cartItem['instructions'] ?: 'None' }}
                                    </p>
                                    <form action="{{ route('restaurant.cart.update', $table) }}" method="POST" class="mb-2">
                                        @csrf
                                        <input type="hidden" name="index" value="{{ $index }}">
                                        <div class="input-group">
                                            <input type="number" name="quantity" value="{{ $cartItem['quantity'] }}" min="1" class="form-control" required>
                                            <button type="submit" class="btn btn-outline-secondary">Update</button>
                                        </div>
                                    </form>
                                    <form action="{{ route('restaurant.cart.remove', $table) }}" method="POST">
                                        @csrf
                                        <input type="hidden" name="index" value="{{ $index }}">
                                        <button type="submit" class="btn btn-outline-danger w-100">Remove</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>

            <div class="text-center mt-4">
                <h4>Total: &#8358; {{ number_format(
                    array_sum(array_map(function($cartItem) use ($items) {
                        return isset($items[$cartItem['item_id']]) ? $items[$cartItem['item_id']]->price * $cartItem['quantity'] : 0;
                    }, $cart))
                , 2) }}</h4>
                <form action="{{ route('restaurant.order.submit', $table) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-lg">Place Order</button>
                </form>
                <a href="{{ route('restaurant.menu', $table) }}" class="btn btn-outline-primary btn-lg mt-2">Continue Shopping</a>
            </div>
        @endif

        <style>
            .card {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
            }
            .card-title {
                font-size: 1.5rem;
            }
            .btn-primary {
                background-color: #d9534f;
                border-color: #d9534f;
            }
            .btn-primary:hover {
                background-color: #c9302c;
                border-color: #c9302c;
            }
        </style>
    </div>
@endsection