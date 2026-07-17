@extends('restaurant::layouts.master')
@section('title', 'Order History')
@section('head')
@if ($phone && $orders->isNotEmpty())
<meta http-equiv="refresh" content="30">
@endif
<style>
.card { transition: transform 0.3s ease, box-shadow 0.3s ease; border-radius: 0.75rem; }
.card:hover { transform: translateY(-5px); box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15); }
.card-header { background-color: #f8f9fa; padding: 1.25rem; border-radius: 0.75rem 0.75rem 0 0; }
.btn-primary { background-color: #d9534f; border-color: #d9534f; }
.btn-primary:hover { background-color: #c9302c; border-color: #c9302c; }
.form-control { border-radius: 0.5rem; }
.alert { padding: 1.25rem; border-radius: 0.75rem; }
.table img { border-radius: 0.5rem; }
.btn-link { color: #d9534f; }
.btn-link:hover { color: #c9302c; text-decoration: underline; }
.table-sm { font-size: 0.85rem; }
.table-borderless th, .table-borderless td { padding: 0.5rem; }
.badge { font-size: 0.9rem; padding: 0.5em 0.75em; }
.timeline { display: flex; align-items: center; gap: 0; margin: 1rem 0; }
.timeline-step { flex: 1; text-align: center; position: relative; }
.timeline-step .dot { width: 28px; height: 28px; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 0.75rem; background: #e9ecef; color: #6c757d; margin-bottom: 0.25rem; }
.timeline-step.active .dot { background: #d9534f; color: #fff; }
.timeline-step.done .dot { background: #28a745; color: #fff; }
.timeline-step .label { font-size: 0.65rem; color: #6c757d; display: block; white-space: nowrap; }
.timeline-step.active .label { color: #d9534f; font-weight: 600; }
.timeline-step.done .label { color: #28a745; }
.timeline-step + .timeline-step::before { content: ''; position: absolute; top: 14px; left: -50%; width: 100%; height: 2px; background: #e9ecef; z-index: -1; }
.timeline-step.done + .timeline-step::before { background: #28a745; }
</style>
@endsection
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
                        @php
                            $steps = [
                                'pending' => ['label' => 'Placed', 'icon' => 'bi bi-receipt'],
                                'preparing' => ['label' => 'Preparing', 'icon' => 'bi bi-fire'],
                                'ready' => ['label' => 'Ready', 'icon' => 'bi bi-check-circle'],
                                'paid' => ['label' => 'Delivered', 'icon' => 'bi bi-truck'],
                            ];
                            $current = $order->tracking_status ?? 'pending';
                            $reached = false;
                        @endphp
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 shadow-lg border-0 rounded-3">
                                <div class="card-header bg-light border-bottom-0">
                                    <h3 class="card-title fw-bold mb-0">
                                        Order #{{ $order->id }}
                                        <span class="float-end">
                                            <span class="badge bg-{{ $order->status === 'completed' ? 'success' : ($order->status === 'rejected' ? 'danger' : 'warning') }} rounded-pill">{{ ucfirst($order->status) }}</span>
                                        </span>
                                    </h3>
                                    <small class="text-muted">Placed: {{ $order->created_at->format('d M Y H:i') }}</small>
                                </div>
                                <div class="card-body">
                                    <p><strong>Customer:</strong> {{ $order->customer_name }}</p>
                                    <p><strong>Phone:</strong> {{ $order->customer_phone }}</p>
                                    <p><strong>Address:</strong> {{ $order->delivery_address }}</p>

                                    <!-- Tracking Timeline -->
                                    <div class="timeline">
                                        @foreach ($steps as $key => $step)
                                                    @php
                                                if ($key === $current) { $reached = true; $status = 'active'; }
                                                elseif (! $reached) { $status = 'done'; }
                                                else { $status = ''; }
                                            @endphp
                                            <div class="timeline-step {{ $status }}">
                                                <div class="dot"><i class="{{ $step['icon'] }}"></i></div>
                                                <span class="label">{{ $step['label'] }}</span>
                                            </div>
                                        @endforeach
                                    </div>

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
                                            @if ((float) $order->grand_total > 0)
                                            <tfoot>
                                                <tr>
                                                    <td colspan="4" class="text-end fw-bold">Total:</td>
                                                    <td><strong>₦{{ number_format((float) $order->grand_total, 2) }}</strong></td>
                                                </tr>
                                            </tfoot>
                                            @endif
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
    </div>
@endsection