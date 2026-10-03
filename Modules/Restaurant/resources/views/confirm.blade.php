@extends('restaurant::layouts.master')
@section('title', 'Order Confirmation')

@section('content')
    @php
        // The status is shown in plain words rather than a database value, so a
        // customer knows whether to sit tight or expect the food shortly.
        $status = strtolower($order->tracking_status ?? 'pending');
        $statusLabels = [
            'pending' => 'Waiting for the kitchen',
            'accepted' => 'Being prepared',
            'preparing' => 'Being prepared',
            'ready' => 'Ready',
            'served' => 'Served',
            'completed' => 'Completed',
            'paid' => 'Paid',
        ];
        $statusLabel = $statusLabels[$status] ?? ucfirst($status);
        $deliveryAddress = $type === 'online' ? ($order->delivery_address ?? null) : null;
        // Online orders are settled before the kitchen ever sees them, so the
        // confirmation says so instead of asking for a payment that is done.
        $paid = $type === 'online' && (float) $order->grand_total > 0 && $order->is_fully_paid;
    @endphp

    <div class="order-page container" style="max-width: 760px;">
        <header class="order-head text-center">
            <div class="confirm-check" aria-hidden="true">
                <i class="fas fa-check"></i>
            </div>
            <h1 class="order-title">
                @if ($type === 'online' && ! empty($order->customer_name))
                    Thank you, {{ $order->customer_name }}!
                @else
                    Thank you!
                @endif
            </h1>
            <p class="order-sub">
                <span class="order-count">Order #{{ $order->id }} &middot; {{ $statusLabel }}</span>
                @if ($type === 'online')
                    We have your order and will bring it to {{ $deliveryAddress ?: 'your address' }}.
                @elseif ($type === 'table')
                    Your order has been sent to the kitchen for Table {{ $sourceModel->number ?? '' }}.
                @elseif ($type === 'room')
                    Your order has been sent to the kitchen for Room {{ $sourceModel->name ?? '' }}.
                @else
                    Your order has been sent to our staff.
                @endif
            </p>
        </header>

        @if ($order->orderItems->isEmpty())
            <div class="order-notice order-notice-bad" role="alert">
                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                <span>We could not find any items on this order. Please speak to a member of staff.</span>
            </div>
        @else
            <ul class="order-lines" aria-label="Items in this order">
                @foreach ($order->orderItems as $item)
                    @php $unitPrice = $item->resolvedUnitPrice(); @endphp
                    <li class="order-line">
                        <img class="order-line-img"
                            src="{{ $item->menuItem && $item->menuItem->image && file_exists(public_path('storage/' . $item->menuItem->image)) ? asset('storage/' . $item->menuItem->image) : asset('storage/images/menudefaultimage.png') }}"
                            alt="" loading="lazy">
                        <div class="order-line-body">
                            <p class="order-line-name">{{ $item->menuItem->name ?? 'Item' }}</p>
                            @if (! empty($item->instructions))
                                <p class="order-line-note">Your note: {{ $item->instructions }}</p>
                            @endif
                            <p class="order-line-price">
                                {{ $item->quantity }} &times; {{ '₦' . number_format($unitPrice, 2) }}
                            </p>
                        </div>
                        <span class="order-line-total">{{ '₦' . number_format($unitPrice * $item->quantity, 2) }}</span>
                    </li>
                @endforeach
            </ul>

            @if ((float) $order->grand_total > 0)
                <div class="order-totals">
                    <div class="order-total-row">
                        <span>Food subtotal</span>
                        <span>{{ '₦' . number_format((float) $order->subtotal, 2) }}</span>
                    </div>
                    @if ((float) $order->vat > 0)
                        <div class="order-total-row">
                            <span>VAT ({{ rtrim(rtrim(number_format((float) $order->vat_rate, 2), '0'), '.') }}%)</span>
                            <span>{{ '₦' . number_format((float) $order->vat, 2) }}</span>
                        </div>
                    @endif
                    @if ((float) $order->service_charge > 0)
                        <div class="order-total-row">
                            <span>Service charge ({{ rtrim(rtrim(number_format((float) $order->service_charge_rate, 2), '0'), '.') }}%)</span>
                            <span>{{ '₦' . number_format((float) $order->service_charge, 2) }}</span>
                        </div>
                    @endif
                    <div class="order-total-row order-total-grand">
                        <span>{{ $paid ? 'Paid' : 'Total to pay' }}</span>
                        <span>{{ '₦' . number_format((float) $order->grand_total, 2) }}</span>
                    </div>
                </div>
            @endif

            @if ($paid)
                <div class="order-notice order-notice-good" role="status">
                    <i class="fas fa-circle-check" aria-hidden="true"></i>
                    <span>Payment received. Your order is already paid &mdash; nothing is due on delivery.</span>
                </div>
            @endif
        @endif

        @if ($deliveryAddress)
            <div class="checkout">
                <h2 class="checkout-title">Delivering to</h2>
                <p class="order-line-note">{{ $deliveryAddress }}</p>
                @if (! empty($order->customer_phone))
                    <p class="order-line-note">We will call {{ $order->customer_phone }} if we need to check anything.</p>
                @endif
            </div>
        @endif

        <p class="order-back">
            <a href="{{ route($type === 'online' ? 'restaurant.online.menu' : 'restaurant.menu', $type === 'online' ? [] : [$type, $sourceModel->id]) }}">
                <i class="fas fa-arrow-left me-1" aria-hidden="true"></i>Order something else
            </a>
        </p>
    </div>
@endsection
