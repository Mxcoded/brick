@extends('restaurant::layouts.master')
@section('title', 'Order Confirmation')
@section('content')
    <div class="container-fluid content py-4">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold text-dark">Order #{{ $order->id }} Confirmed</h1>
            <p class="lead text-muted">
    Thank you for your order
    @switch($type)
        @case('table')
            – Table {{ $sourceModel->number ?? 'N/A' }}
            @break

        @case('room')
            – Room {{ $sourceModel->name ?? 'N/A' }}
            @break

        @case('online')
            , {{ $order->customer_name ?? 'Customer' }}! It will be delivered to {{ $order->delivery_address ?? 'your address' }}.
            @break

        @default
            – Type: {{ ucfirst($type) }}
    @endswitch
    It has been sent to our staff for processing.
</p>
        </div>

        @if ($order->orderItems->isEmpty())
            <div class="alert alert-warning text-center rounded-3 shadow-sm">
                <i class="bi bi-exclamation-triangle me-2"></i>No items found in this order.
            </div>
        @else
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="shadow-lg border-0 rounded-3">
                        <div class="card-body">
                            <h3 class="card-title fw-bold">Order Summary</h3>
                            <p><strong>Tracking Status:</strong> {{ ucfirst($order->tracking_status ?? 'Pending') }}</p>
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Image</th>
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
                                            <td>
                                                <img src="{{ $item->menuItem && $item->menuItem->image && file_exists(public_path('storage/' . $item->menuItem->image)) ? asset('storage/' . $item->menuItem->image) : asset('storage/images/menudefaultimage.png') }}"
                                                    alt="{{ $item->menuItem ? $item->menuItem->name : 'No Image' }}"
                                                    class="img-fluid rounded"
                                                    style="max-width: 60px; max-height: 60px; object-fit: cover;">
                                            </td>
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
                                        <td colspan="5" class="text-end fw-bold">Total:</td>
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
@endsection