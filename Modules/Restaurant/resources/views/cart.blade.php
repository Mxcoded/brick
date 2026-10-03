@extends('restaurant::layouts.master')
@section('title', 'Your Order')

@if ($type === 'online')
    @section('head')
        <script src="https://js.paystack.co/v1/inline.js"></script>
    @endsection
@endif

@section('content')
    @php
        // Mirrors the server's pricing so the figure on this page is the figure
        // that gets charged. Nothing here is trusted on submit: the controller
        // re-reads every price and recomputes the total.
        $lineTotal = 0;
        $itemCount = 0;
        $missing = [];
        foreach ($cart as $entry) {
            $menuItem = $items[$entry['item_id']] ?? null;
            if (! $menuItem) {
                $missing[] = $entry['item_id'];
                continue;
            }
            $lineTotal += (float) $menuItem->price * (int) ($entry['quantity'] ?? 0);
            $itemCount += (int) ($entry['quantity'] ?? 0);
        }
        $vatAmount = $lineTotal * $vatRate / 100;
        $serviceChargeAmount = $serviceChargeRate > 0 ? $lineTotal * $serviceChargeRate / 100 : 0;
        $amountDue = $lineTotal + $vatAmount + $serviceChargeAmount;
    @endphp

    <div class="order-page container" style="max-width: 900px;">
        <header class="order-head text-center">
            <h1 class="order-title">
                @if ($type === 'table')
                    Table {{ $sourceModel->number ?? '' }}
                @elseif ($type === 'room')
                    Room {{ $sourceModel->name ?? '' }}
                @else
                    Your Order
                @endif
            </h1>
            <p class="order-sub">
                @if (! empty($cart))
                    <span class="order-count">{{ $itemCount }} {{ $itemCount === 1 ? 'item' : 'items' }} in your basket.</span>
                @endif
                @if ($type === 'online')
                    Check your food, then tell us where to bring it.
                @else
                    Check your food before sending it to the kitchen.
                @endif
            </p>
        </header>

        @if (session('success'))
            <div class="order-notice order-notice-good" role="status">
                <i class="fas fa-check-circle" aria-hidden="true"></i>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        @if (session('error'))
            <div class="order-notice order-notice-bad" role="alert">
                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                <span>{{ session('error') }}</span>
            </div>
        @endif

        @if (! empty($missing))
            <div class="order-notice order-notice-bad" role="alert">
                <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                <span>Some items are no longer on the menu. Please remove them to continue.</span>
            </div>
        @endif

        @if (empty($cart))
            <div class="order-empty text-center">
                <i class="fas fa-basket-shopping" aria-hidden="true"></i>
                <h2>Your basket is empty</h2>
                <p>Pick something you like from the menu to get started.</p>
                <a href="{{ route($type === 'online' ? 'restaurant.online.menu' : 'restaurant.menu', $type === 'online' ? [] : [$type, $sourceModel->id]) }}"
                    class="btn-order-primary">
                    <i class="fas fa-utensils me-2" aria-hidden="true"></i>See the menu
                </a>
            </div>
        @else
            <ul class="order-lines">
                @foreach ($cart as $index => $entry)
                    @php
                        $menuItem = $items[$entry['item_id']] ?? null;
                        $quantity = (int) ($entry['quantity'] ?? 0);
                        $unit = $menuItem ? (float) $menuItem->price : 0.0;
                    @endphp
                    <li class="order-line {{ $menuItem ? '' : 'order-line-gone' }}">
                        <img class="order-line-img"
                            src="{{ $menuItem && $menuItem->image && file_exists(public_path('storage/' . $menuItem->image)) ? asset('storage/' . $menuItem->image) : asset('storage/images/menudefaultimage.png') }}"
                            alt="" loading="lazy">
                        <div class="order-line-body">
                            <p class="order-line-name">{{ $menuItem->name ?? 'This item is no longer available' }}</p>
                            @if (! empty($entry['instructions']))
                                <p class="order-line-note">Your note: {{ $entry['instructions'] }}</p>
                            @endif
                            <p class="order-line-price">
                                {{ '₦' . number_format($unit, 2) }} each
                                @if ($menuItem && $quantity > 1)
                                    <span class="order-line-times">&times; {{ $quantity }}</span>
                                @endif
                            </p>
                        </div>

                        @if ($menuItem)
                            <div class="order-line-side">
                                <span class="order-line-total">{{ '₦' . number_format($unit * $quantity, 2) }}</span>
                                <div class="qty" role="group" aria-label="How many {{ $menuItem->name }}">
                                    <form method="POST"
                                        action="{{ route($type === 'online' ? 'restaurant.online.cart.update' : 'restaurant.cart.update', $type === 'online' ? [] : [$type, $sourceModel->id]) }}">
                                        @csrf
                                        <input type="hidden" name="index" value="{{ $index }}">
                                        <input type="hidden" name="quantity" value="{{ max(1, $quantity - 1) }}">
                                        <button class="qty-btn" type="submit" aria-label="One fewer {{ $menuItem->name }}"
                                            {{ $quantity <= 1 ? 'disabled' : '' }}>&minus;</button>
                                    </form>

                                    <span class="qty-value" aria-live="polite">{{ $quantity }}</span>

                                    <form method="POST"
                                        action="{{ route($type === 'online' ? 'restaurant.online.cart.update' : 'restaurant.cart.update', $type === 'online' ? [] : [$type, $sourceModel->id]) }}">
                                        @csrf
                                        <input type="hidden" name="index" value="{{ $index }}">
                                        <input type="hidden" name="quantity" value="{{ min(999, $quantity + 1) }}">
                                        <button class="qty-btn" type="submit" aria-label="One more {{ $menuItem->name }}">+</button>
                                    </form>
                                </div>

                                <form method="POST"
                                    action="{{ route($type === 'online' ? 'restaurant.online.cart.remove' : 'restaurant.cart.remove', $type === 'online' ? [] : [$type, $sourceModel->id]) }}">
                                    @csrf
                                    <input type="hidden" name="index" value="{{ $index }}">
                                    <button class="order-line-remove" type="submit">Remove</button>
                                </form>
                            </div>
                        @else
                            <form method="POST" class="order-line-side"
                                action="{{ route($type === 'online' ? 'restaurant.online.cart.remove' : 'restaurant.cart.remove', $type === 'online' ? [] : [$type, $sourceModel->id]) }}">
                                @csrf
                                <input type="hidden" name="index" value="{{ $index }}">
                                <button class="order-line-remove" type="submit">Remove</button>
                            </form>
                        @endif
                    </li>
                @endforeach
            </ul>

            <div class="order-totals">
                <div class="order-total-row">
                    <span>Food subtotal</span>
                    <span>{{ '₦' . number_format($lineTotal, 2) }}</span>
                </div>
                @if ($vatRate > 0)
                    <div class="order-total-row">
                        <span>VAT ({{ rtrim(rtrim(number_format($vatRate, 2), '0'), '.') }}%)</span>
                        <span>{{ '₦' . number_format($vatAmount, 2) }}</span>
                    </div>
                @endif
                @if ($serviceChargeRate > 0)
                    <div class="order-total-row">
                        <span>Service charge ({{ rtrim(rtrim(number_format($serviceChargeRate, 2), '0'), '.') }}%)</span>
                        <span>{{ '₦' . number_format($serviceChargeAmount, 2) }}</span>
                    </div>
                @endif
                <div class="order-total-row order-total-grand">
                    <span>Total to pay</span>
                    <span>{{ '₦' . number_format($amountDue, 2) }}</span>
                </div>
            </div>

            @if ($type === 'online')
                <form class="checkout" method="POST" action="{{ route('restaurant.online.order.submit') }}"
                    x-data="deliveryForm" x-on:submit.prevent="onSubmit($event)" novalidate>
                    @csrf
                    <input type="hidden" name="idempotency_key"
                        value="{{ old('idempotency_key', $idempotencyKey) }}">
                    {{-- Filled by the Paystack callback. The server verifies it
                         before it will create the order. --}}
                    <input type="hidden" name="payment_reference" x-ref="reference"
                        value="{{ old('payment_reference') }}">

                    <h2 class="checkout-title">Where should we bring it?</h2>

                    <div class="field">
                        <label for="customer_name">Your name</label>
                        <input type="text" id="customer_name" name="customer_name" autocomplete="name"
                            inputmode="text" required minlength="2" maxlength="255"
                            value="{{ old('customer_name') }}"
                            data-server-error="{{ $errors->first('customer_name') }}"
                            :aria-invalid="errorFor('customer_name') ? 'true' : null"
                            aria-describedby="customer_name_error">
                        <p class="field-error" id="customer_name_error" x-show="errorFor('customer_name')"
                            x-text="errorFor('customer_name')" role="alert"></p>
                    </div>

                    <div class="field">
                        <label for="customer_phone">Phone number</label>
                        <input type="tel" id="customer_phone" name="customer_phone" autocomplete="tel"
                            inputmode="numeric" pattern="[0-9]{10,15}" required
                            value="{{ old('customer_phone') }}"
                            data-server-error="{{ $errors->first('customer_phone') }}"
                            :aria-invalid="errorFor('customer_phone') ? 'true' : null"
                            aria-describedby="customer_phone_error customer_phone_help">
                        <p class="field-help" id="customer_phone_help">Digits only, 10 to 15 numbers. We call this number
                            if anything is unclear.</p>
                        <p class="field-error" id="customer_phone_error" x-show="errorFor('customer_phone')"
                            x-text="errorFor('customer_phone')" role="alert"></p>
                    </div>

                    <div class="field">
                        <label for="delivery_address">Delivery address</label>
                        <textarea id="delivery_address" name="delivery_address" rows="3" required minlength="10"
                            maxlength="1000" autocomplete="street-address"
                            data-server-error="{{ $errors->first('delivery_address') }}"
                            :aria-invalid="errorFor('delivery_address') ? 'true' : null"
                            aria-describedby="delivery_address_error">{{ old('delivery_address') }}</textarea>
                        <p class="field-error" id="delivery_address_error" x-show="errorFor('delivery_address')"
                            x-text="errorFor('delivery_address')" role="alert"></p>
                    </div>

                    @if ($errors->any())
                        <div class="order-notice order-notice-bad" role="alert">
                            <i class="fas fa-exclamation-circle" aria-hidden="true"></i>
                            <span>Please fix the highlighted boxes above, then press the button again.</span>
                        </div>
                    @endif

                    <button type="submit" class="btn-order-primary btn-order-block" :disabled="busy"
                        :aria-busy="busy ? 'true' : 'false'">
                        <span x-show="!busy">
                            <i class="fas fa-lock me-2" aria-hidden="true"></i>
                            Pay &amp; place order &middot; {{ '₦' . number_format($amountDue, 2) }}
                        </span>
                        <span x-show="busy">
                            <i class="fas fa-spinner fa-spin me-2" aria-hidden="true"></i>
                            Waiting for payment&hellip;
                        </span>
                    </button>

                    <p class="checkout-reassure">
                        <i class="fas fa-shield-halved me-1" aria-hidden="true"></i>
                        You pay securely on Paystack. If the payment does not go through, no order is placed and
                        you are not charged by us.
                    </p>
                </form>
            @else
                <form method="POST"
                    action="{{ route('restaurant.order.submit', [$type, $sourceModel->id]) }}" class="checkout">
                    @csrf
                    <input type="hidden" name="idempotency_key" value="{{ $idempotencyKey }}">
                    <button type="submit" class="btn-order-primary btn-order-block">
                        <i class="fas fa-check-circle me-2" aria-hidden="true"></i>
                        Send to the kitchen
                    </button>
                </form>
            @endif

            <p class="order-back">
                <a href="{{ route($type === 'online' ? 'restaurant.online.menu' : 'restaurant.menu', $type === 'online' ? [] : [$type, $sourceModel->id]) }}">
                    <i class="fas fa-arrow-left me-1" aria-hidden="true"></i>Add more food
                </a>
            </p>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            /**
             * Friendly checking for the delivery form.
             *
             * This only explains a problem early and in plain words. It is not a
             * security control: the server re-validates every field, and the
             * messages shown here are the same rules the server applies, so a
             * customer who switches the checks off still cannot get bad data in.
             */
            Alpine.data('deliveryForm', () => ({
                busy: false,
                touched: {},
                reference: '',

                rules: {
                    customer_name: (v) => {
                        if (!v.trim()) return 'Please tell us your name.';
                        if (v.trim().length < 2) return 'That name looks too short.';
                        return '';
                    },
                    customer_phone: (v) => {
                        const digits = v.replace(/\D/g, '');
                        if (!digits) return 'We need a phone number to reach you.';
                        if (digits.length < 10) return 'That number is too short. It needs 10 to 15 digits.';
                        if (digits.length > 15) return 'That number is too long. It needs 10 to 15 digits.';
                        return '';
                    },
                    delivery_address: (v) => {
                        if (!v.trim()) return 'Please tell us where to deliver.';
                        if (v.trim().length < 10) return 'Please write a bit more so the rider can find you.';
                        return '';
                    },
                },

                // Server-side messages win, so a rejected submission always
                // explains itself even if the client rules pass.
                errorFor(field) {
                    const server = document.getElementById(field)?.getAttribute('data-server-error');
                    if (server) return server;
                    if (!this.touched[field]) return '';
                    return this.rules[field](document.getElementById(field)?.value ?? '');
                },

                /**
                 * Mark a field as visited once the customer has actually used
                 * it. The values live in the form inputs rather than on the
                 * component, so this listens to the inputs themselves instead
                 * of watching component properties that do not exist. A field
                 * is only called wrong after someone has typed in it and moved
                 * on, which keeps a blank form from opening covered in red.
                 */
                init() {
                    this.reference = this.$refs.reference?.value ?? '';

                    this.$nextTick(() => {
                        for (const field of Object.keys(this.rules)) {
                            const el = document.getElementById(field);
                            if (!el) continue;
                            // A change re-renders the message, so both events
                            // mark the field as touched.
                            el.addEventListener('blur', () => { this.touched[field] = true; });
                            el.addEventListener('input', () => {
                                if (this.touched[field]) this.touched = { ...this.touched };
                            });
                        }
                    });
                },

                validate() {
                    for (const field of Object.keys(this.rules)) {
                        this.touched[field] = true;
                    }
                    for (const field of Object.keys(this.rules)) {
                        if (this.rules[field](document.getElementById(field)?.value ?? '')) return false;
                    }
                    return true;
                },

                /**
                 * Online checkout pays before it orders, so the submit button
                 * opens the gateway rather than posting straight away. The
                 * reference the gateway returns is the customer's proof of
                 * payment and is what the server verifies; the amount shown is
                 * only a courtesy, the server recomputes the real total.
                 */
                onSubmit(event) {
                    const form = event.target;

                    if (!this.validate()) {
                        this.busy = false;
                        return;
                    }

                    // Already paid on an earlier attempt (for example the server
                    // rejected a typo in the address). Reuse the same reference
                    // instead of charging again.
                    if (this.reference) {
                        this.busy = true;
                        form.submit();
                        return;
                    }

                    const key = @json($paystackPublicKey);
                    const phone = (document.getElementById('customer_phone')?.value ?? '').replace(/\D/g, '');

                    // If the gateway is not configured there is nothing to show.
                    // Let the server answer with a proper error rather than
                    // leaving the customer stuck on a button that does nothing.
                    if (!key || typeof PaystackPop === 'undefined') {
                        this.busy = true;
                        form.submit();
                        return;
                    }

                    this.busy = true;

                    try {
                        PaystackPop.setup({
                            key: key,
                            email: (phone || 'guest') + '@orders.brickspoint.com',
                            amount: Math.round(Number(@json($amountDue)) * 100),
                            ref: 'REST-' + Date.now() + '-' + Math.random().toString(36).slice(2, 8),
                            currency: 'NGN',
                            metadata: {
                                custom_fields: [
                                    { display_name: 'Customer', variable_name: 'customer_name', value: document.getElementById('customer_name')?.value ?? '' },
                                    { display_name: 'Phone', variable_name: 'customer_phone', value: phone },
                                ],
                            },
                            callback: (response) => {
                                this.reference = response.reference;
                                this.$refs.reference.value = response.reference;
                                form.submit();
                            },
                            onClose: () => {
                                this.busy = false;
                            },
                        }).openIframe();
                    } catch (e) {
                        this.busy = false;
                    }
                },
            }));
        });
    </script>
@endpush
