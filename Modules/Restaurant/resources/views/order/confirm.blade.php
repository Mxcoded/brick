@extends('restaurant::layouts.master')
@section('title', 'Order Confirmation')
@section('content')
    <div class="container-fluid content">
        <div class="text-center mb-4">
            <h1 class="display-4 fw-bold">Order #{{ $order->id }} Confirmed</h1>
            <p class="lead text-muted">Thank you for your order, Table {{ $order->table->number }}! It has been sent to our staff for processing.</p>
        </div>

        @if ($order->orderItems->isEmpty())
            <div class="alert alert-warning text-center">
                No items found in this order.
            </div>
        @else
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="card shadow-sm border-0">
                        <div class="card-body">
                            <h3 class="card-title fw-bold">Order Summary</h3>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Item</th>
                                        <th>Quantity</th>
                                        <th>Price</th>
                                        <th>Instructions</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($order->orderItems as $item)
                                        <tr>
                                            <td>{{ $item->menuItem ? $item->menuItem->name : 'Item not found' }}</td>
                                            <td>{{ $item->quantity }}</td>
                                            <td>{{ $item->menuItem ? '₦' . number_format($item->menuItem->price, 2) : 'N/A' }}</td>
                                            <td>{{ $item->instructions ?: 'None' }}</td>
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
        @endif

        <div class="text-center mt-4">
            <a href="{{ route('restaurant.menu', $table) }}" class="btn btn-outline-primary btn-lg">Back to Menu</a>
        </div>

        <style>
            .card {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
            }
            .card:hover {
                transform: translateY(-5px);
                box-shadow: 0 8px 16px rgba(0, 0, 0, 0.2);
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