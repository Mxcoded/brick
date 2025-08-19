<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">

    <title>@yield('title', 'Taste Restaurant') - {{ config('app.name', 'Laravel') }}</title>

    <meta name="description" content="@yield('description', 'Discover delicious meals at Taste Restaurant')">
    <meta name="keywords" content="@yield('keywords', 'restaurant, food, menu, dining')">
    <meta name="author" content="@yield('author', 'Taste Restaurant')">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Bootstrap & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <!-- Alpine.js -->
    <script src="https://unpkg.com/alpinejs@3.12.0/dist/cdn.min.js" defer></script>
    <script src="https://unpkg.com/@alpinejs/persist@3.12.0/dist/cdn.min.js" defer></script>

    <style>
        :root {
            --primary-color: #d9534f;
            --primary-hover: #c9302c;
            --secondary-color: #6c757d;
            --success-color: #198754;
            --warning-color: #fd7e14;
            --info-color: #0dcaf0;
            --light-color: #f8f9fa;
            --dark-color: #212529;
            --border-radius: 12px;
            --box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, rgb(254, 254, 254) 0%, rgb(254, 145, 55) 100%);
            min-height: 100vh;
            color: #333;
            overflow-x: hidden;
        }

        body.dark-mode {
            background: linear-gradient(135deg, #1e3a8a, #3b0764);
            color: #e9ecef;
        }

        body.dark-mode .glass-morphism,
        body.dark-mode .navbar,
        body.dark-mode .card,
        body.dark-mode .modal-content {
            background: rgba(30, 30, 30, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        body.dark-mode .text-muted {
            color: #adb5bd !important;
        }
 .landing-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4');
            background-size: cover;
            background-position: center;
            color: white;
        }

        .card {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 2rem;
            max-width: 500px;
            width: 100%;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
        }
        .navbar {
            background: rgba(255, 255, 255, 0.95) !important;
            backdrop-filter: blur(20px);
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
            transition: var(--transition);
            position: sticky;
            top: 0;
            z-index: 1030;
        }

        .navbar-brand {
            font-weight: 700;
            font-size: 1.8rem;
            background: linear-gradient(135deg, var(--primary-color), #ff6b6b);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .nav-link {
            color: #333 !important;
            transition: var(--transition);
        }

        .nav-link:hover,
        .nav-link.active {
            color: var(--primary-color) !important;
        }

        .cart-indicator {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-color), #ff6b6b);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 1.5rem;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: var(--transition);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .cart-indicator:hover {
            transform: scale(1.1);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.3);
        }

        .cart-indicator.loading {
            animation: pulse 1s infinite;
        }

        .cart-count {
            position: absolute;
            top: -5px;
            right: -5px;
            background: var(--warning-color);
            color: white;
            border-radius: 50%;
            width: 25px;
            height: 25px;
            font-size: 0.8rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .floating-elements {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: -1;
        }

        .floating-element {
            position: absolute;
            opacity: 0.1;
            animation: float 20s infinite linear;
        }

        @keyframes float {
            0% { transform: translateY(100vh) rotate(0deg); }
            100% { transform: translateY(-100px) rotate(360deg); }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.2); }
            100% { transform: scale(1); }
        }

        .loading-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% { background-position: -200% 0; }
            100% { background-position: 200% 0; }
        }

        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .modal-content {
            border: none;
            border-radius: var(--border-radius);
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-color), #ff6b6b);
            color: white;
            border-bottom: none;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }

        .btn-primary-custom {
            background: linear-gradient(135deg, var(--primary-color), #ff6b6b);
            color: white;
            border: none;
            border-radius: 50px;
            padding: 0.8rem 2rem;
            font-weight: 600;
            transition: var(--transition);
        }

        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 30px rgba(217, 83, 79, 0.3);
        }

        .quantity-controls {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            background: rgba(0, 0, 0, 0.05);
            border-radius: 25px;
            padding: 0.3rem;
        }

        .quantity-btn {
            width: 35px;
            height: 35px;
            border: none;
            border-radius: 50%;
            background: var(--primary-color);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
        }

        .quantity-btn:hover {
            background: var(--primary-hover);
            transform: scale(1.1);
        }

        .modal-loading {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 200px;
        }

        @media (max-width: 768px) {
            .modal-dialog {
                margin: 0.5rem;
            }
            .navbar-brand {
                font-size: 1.5rem;
            }
        }
    </style>

    @yield('head')
</head>
<body x-data="appData" x-bind:class="{ 'dark-mode': $store.persist.isDarkMode }">
    <!-- Dynamic Cart Key -->
    @php
        $cartKey = isset($type) ? ($type === 'online' ? 'online_cart' : $type . '_cart') : 'online_cart';
    @endphp
@if (View::getSection('title') !== 'Welcome')
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('restaurant.landing') }}">Taste Restaurant</a>
            <button class="navbar-toggler" type="button" @click="showMobileMenu = !showMobileMenu" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" x-bind:class="{ 'show': showMobileMenu }">
                <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('restaurant.landing') }}">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route(
                            isset($type) && $type === 'online' ? 'restaurant.online.menu' : (isset($type) ? 'restaurant.menu' : 'restaurant.online.menu'),
                            isset($type) && $type !== 'online' ? [$type, $sourceModel->id ?? null] : []
                        ) }}">Menu</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('restaurant.online.orders') }}">Order History</a>
                    </li>
                    <li class="nav-item">
                        <button class="btn btn-outline-primary btn-sm rounded-pill" @click="$store.persist.isDarkMode = !$store.persist.isDarkMode" aria-label="Toggle dark mode">
                            <i class="fas" :class="$store.persist.isDarkMode ? 'fa-sun' : 'fa-moon'"></i>
                            <span x-text="$store.persist.isDarkMode ? 'Light Mode' : 'Dark Mode'"></span>
                        </button>
                    </li>
                </ul>
            </div>
        </div>
    </nav>
@endif
    <!-- Floating Elements -->
    <div class="floating-elements">
        <div class="floating-element" style="left: 10%; top: 20%; width: 50px; height: 50px; background: var(--primary-color); border-radius: 50%;"></div>
        <div class="floating-element" style="left: 80%; top: 60%; width: 70px; height: 70px; background: var(--secondary-color); border-radius: 50%;"></div>
        <div class="floating-element" style="left: 30%; top: 80%; width: 40px; height: 40px; background: var(--success-color); border-radius: 50%;"></div>
    </div>

    <!-- Content -->
    @yield('content')

    <!-- Cart Indicator -->
    <button class="cart-indicator" data-bs-toggle="modal" data-bs-target="#cartModal" x-data="{ count: $store.cart.items.length }" :class="{ 'loading': $store.cart.isAdding || $store.cart.isRefreshing }" @click="$store.cart.refreshCart()">
        <i class="fas" :class="$store.cart.isAdding || $store.cart.isRefreshing ? 'fa-spinner fa-spin' : 'fa-shopping-cart'"></i>
        <span class="cart-count" x-text="count"></span>
    </button>
@if (View::getSection('title') !== 'Welcome')
    <!-- Cart Modal -->
    <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true" x-data="{ isLoading: false }" @show.bs.modal="$store.cart.refreshCart()">
        <div class="modal-dialog modal-dialog-centered modal-lg">
           <form action="{{ route(
                isset($type) && $type === 'online' ? 'restaurant.online.order.submit' : 'restaurant.order.submit',
                isset($type) && $type !== 'online' ? [$type, $sourceModel->id ?? null] : []
            ) }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="cartModalLabel">Your Cart</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <template x-if="$store.cart.isRefreshing || isLoading">
                            <div class="modal-loading">
                                <i class="fas fa-spinner fa-spin fa-3x text-muted"></i>
                            </div>
                        </template>
                        <template x-if="!$store.cart.isRefreshing && !isLoading">
                            <div>
                               <template x-if="!Array.isArray($store.cart.items) || $store.cart.items.length === 0">
                                    <div class="text-center py-5">
                                        <i class="fas fa-shopping-cart text-muted mb-3" style="font-size: 4rem; opacity: 0.3;"></i>
                                        <h5 class="text-muted">Your cart is empty</h5>
                                        <p class="text-muted">Add some delicious items to get started!</p>
                                    </div>
                                </template>
                                <template x-else>
                                    <div class="cart-items">
                                        <!-- Error/Success Alerts -->
                                        @if (session('error'))
                                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        @endif
                                        @if (session('success'))
                                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                                            </div>
                                        @endif
                                        <template x-for="(item, index) in $store.cart.items" :key="item.item_id">
                                            <div class="card mb-3 border-0 shadow-sm glass-morphism">
                                                <div class="card-body">
                                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                                        <div class="flex-grow-1">
                                                            <h6 class="fw-bold mb-1" x-text="item.name || 'Unknown Item'"></h6>
                                                            <p class="text-muted small mb-2" x-show="item.instructions" x-text="'Instructions: ' + (item.instructions || 'None')"></p>
                                                            <p class="fw-semibold text-primary mb-0" x-text="item.price ? `₦${Number(item.price).toLocaleString(undefined, { minimumFractionDigits: 2 })} each` : 'Price not available'"></p>
                                                        </div>
                                                        <form action="{{ route(
                                                            isset($type) && $type === 'online' ? 'restaurant.online.cart.remove' : 'restaurant.cart.remove',
                                                            isset($type) && $type !== 'online' ? [$type, $sourceModel->id ?? null] : []
                                                        ) }}" method="POST">
                                                            @csrf
                                                            <input type="hidden" name="index" :value="index">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" style="width: 35px; height: 35px;" aria-label="Remove item">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                    <div class="d-flex justify-content-between align-items-center">
                                                     <form action="{{ route(
                                                            isset($type) && $type === 'online' ? 'restaurant.online.cart.update' : 'restaurant.cart.update',
                                                            isset($type) && $type !== 'online' ? [$type, $sourceModel->id ?? null] : []
                                                        ) }}" method="POST" @submit.prevent="updateCart($event, item.item_id, index)">
                                                            @csrf
                                                            <input type="hidden" name="index" :value="index">
                                                            <div class="quantity-controls">
                                                                <button type="button" class="quantity-btn" @click="if (item.quantity > 1) { item.quantity--; updateCart($event, item.item_id, index); }" aria-label="Decrease quantity">
                                                                    <i class="fas fa-minus"></i>
                                                                </button>
                                                                <input type="number" name="quantity" x-model.number="item.quantity" class="form-control form-control-sm w-75 d-inline text-center" min="1" required>
                                                                <button type="button" class="quantity-btn" @click="item.quantity++; updateCart($event, item.item_id, index);" aria-label="Increase quantity">
                                                                    <i class="fas fa-plus"></i>
                                                                </button>
                                                            </div>
                                                        </form>
                                                        <div class="text-end">
                                                            <p class="fw-bold mb-0 h5 text-success" x-text="item.price && item.quantity ? `₦${Number(item.price * item.quantity).toLocaleString(undefined, { minimumFractionDigits: 2 })}` : 'Total not available'"></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </template>
                                        <div class="cart-summary mt-4 p-3 glass-morphism rounded">
                                            <div class="d-flex justify-content-between align-items-center mb-3">
                                                <span class="h5 mb-0">Total:</span>
                                                <span class="h4 fw-bold text-success mb-0" x-text="$store.cart.totalPrice ? `₦${$store.cart.totalPrice.toLocaleString(undefined, { minimumFractionDigits: 2 })}` : 'Total not available'"></span>
                                            </div>
                                            <div class="mb-3">
                                                <div class="d-flex justify-content-between small text-muted mb-1">
                                                    <span>Free delivery progress</span>
                                                    <span x-text="$store.cart.totalPrice ? `₦${Math.max(0, 5000 - $store.cart.totalPrice).toLocaleString(undefined, { minimumFractionDigits: 2 })} more needed` : 'N/A'"></span>
                                                </div>
                                                <div class="progress" style="height: 8px;">
                                                    <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" 
                                                         :style="$store.cart.totalPrice ? `width: ${Math.min(($store.cart.totalPrice / 5000) * 100, 100)}%` : 'width: 0%'" 
                                                         role="progressbar" 
                                                         :aria-valuenow="$store.cart.totalPrice || 0" 
                                                         aria-valuemin="0" 
                                                         aria-valuemax="5000"></div>
                                                </div>
                                                <small class="text-muted" x-show="$store.cart.totalPrice >= 5000">
                                                    <i class="fas fa-check-circle text-success me-1"></i>Congratulations! You qualify for free delivery!
                                                </small>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </template>
                        <input type="hidden" name="order" x-model="JSON.stringify($store.cart.items)">
                    </div>
                   <div class="modal-footer" x-show="$store.cart.items && Array.isArray($store.cart.items) && $store.cart.items.length > 0 && !$store.cart.isRefreshing && !isLoading">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Continue Shopping</button>
                        <button type="submit" class="btn btn-primary-custom btn-lg" :disabled="$store.cart.isSubmitting || $store.cart.isAdding">
                            <i class="fas fa-credit-card me-2"></i>
                            <span x-show="!$store.cart.isSubmitting">Proceed to Checkout</span>
                            <span x-show="$store.cart.isSubmitting">Processing...</span>
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endif
    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('persist', {
                isDarkMode: false
            });

            Alpine.store('cart', {
                items: @json(session($cartKey, [])) || [],
                totalPrice: 0,
                isSubmitting: false,
                isAdding: false,
                isRefreshing: false,
                debug: true, // Enable debug logging
                init() {
                    console.log('Cart store initialized with items:', this.items);
                    this.ensureItemsArray();
                    this.updateTotal();
                    if (!this.items.length) {
                        this.refreshCart();
                    }
                },
                ensureItemsArray() {
                    if (!Array.isArray(this.items)) {
                        console.warn('Items is not an array, resetting to empty array');
                        this.items = [];
                    }
                },
                updateTotal() {
                    this.totalPrice = this.items.reduce((sum, item) => {
                        const price = Number(item.price || 0);
                        const quantity = Number(item.quantity || 0);
                        return sum + (price * quantity);
                    }, 0);
                    console.log('Total price updated:', this.totalPrice);
                },
                addToCart(itemId, name, price, quantity, instructions, route) {
                    console.log('Adding to cart:', { itemId, name, price, quantity, instructions, route });
                    // Check for existing item
                    const existingIndex = this.items.findIndex(
                        item => item.item_id === itemId && item.instructions === (instructions || '')
                    );
                    if (existingIndex !== -1) {
                        console.log('Item exists, incrementing quantity at index:', existingIndex);
                        this.items[existingIndex].quantity += parseInt(quantity) || 1;
                        this.updateCart(null, itemId, existingIndex, route);
                        return;
                    }
                    // Add new item
                    this.isAdding = true;
                    const formData = new FormData();
                    formData.append('item_id', itemId);
                    formData.append('quantity', quantity);
                    formData.append('instructions', instructions || '');
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                    fetch(route, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        console.log('addToCart response status:', response.status);
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        console.log('addToCart response data:', data);
                        if (data.success) {
                            this.items = Array.isArray(data.cart) ? data.cart : [];
                            this.updateTotal();
                            this.showAlert('success', `Added ${name} to cart`);
                        } else {
                            this.showAlert('danger', data.message || 'Failed to add item to cart');
                        }
                    })
                    .catch(error => {
                        console.error('Error adding to cart:', error);
                        this.showAlert('danger', 'An error occurred while adding to cart.');
                    })
                    .finally(() => {
                        this.isAdding = false;
                        console.log('Current cart items after add:', this.items);
                    });
                },
                updateCart(event, itemId, index, route = null) {
                    console.log('Updating cart:', { itemId, index, route });
                    const form = event ? event.target.closest('form') : null;
                    const updateRoute = route || form.action;
                    const quantity = this.items[index].quantity;
                    if (quantity < 1) {
                        this.items[index].quantity = 1;
                        return;
                    }
                    this.isSubmitting = true;
                    const formData = new FormData();
                    formData.append('index', index);
                    formData.append('quantity', quantity);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
                    fetch(updateRoute, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        console.log('updateCart response status:', response.status);
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        console.log('updateCart response data:', data);
                        if (data.success) {
                            this.items = Array.isArray(data.cart) ? data.cart : [];
                            this.updateTotal();
                            this.showAlert('success', 'Cart updated successfully');
                        } else {
                            this.showAlert('danger', data.message || 'Failed to update cart');
                        }
                    })
                    .catch(error => {
                        console.error('Error updating cart:', error);
                        this.showAlert('danger', 'An error occurred while updating the cart.');
                    })
                    .finally(() => {
                        this.isSubmitting = false;
                        console.log('Current cart items after update:', this.items);
                    });
                },
                refreshCart() {
                    console.log('Refreshing cart');
                    this.isRefreshing = true;
                    const route = '{{ route(isset($type) && $type === 'online' ? 'restaurant.online.cart.get' : 'restaurant.cart.get', isset($type) && $type !== 'online' ? [$type, $sourceModel->id ?? null] : []) }}';
                    fetch(route, {
                        method: 'GET',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        console.log('refreshCart response status:', response.status);
                        if (!response.ok) throw new Error('Network response was not ok');
                        return response.json();
                    })
                    .then(data => {
                        console.log('refreshCart response data:', data);
                        if (data.success) {
                            this.items = Array.isArray(data.cart) ? data.cart : [];
                            this.updateTotal();
                            if (!this.items.length) {
                                console.log('Cart is empty after refresh');
                            }
                        } else {
                            this.showAlert('danger', data.message || 'Failed to refresh cart');
                        }
                    })
                    .catch(error => {
                        console.error('Error refreshing cart:', error);
                        this.showAlert('danger', 'An error occurred while refreshing the cart.');
                    })
                    .finally(() => {
                        this.isRefreshing = false;
                        console.log('Current cart items after refresh:', this.items);
                    });
                },
                showAlert(type, message) {
                    console.log('Showing alert:', { type, message });
                    const alert = document.createElement('div');
                    alert.className = `alert alert-${type} alert-dismissible fade show`;
                    alert.role = 'alert';
                    alert.innerHTML = `
                        <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                        ${message}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    `;
                    const modalBody = document.querySelector('#cartModal .modal-body');
                    modalBody.insertBefore(alert, modalBody.firstChild);
                    setTimeout(() => {
                        if (alert.isConnected) {
                            alert.classList.remove('show');
                            setTimeout(() => alert.remove(), 150);
                        }
                    }, 3000);
                }
            });

            Alpine.data('appData', () => ({
                showMobileMenu: false
            }));
        });

        window.addEventListener('load', () => {
            if (!window.Alpine) {
                console.error('Alpine.js not loaded. Check script tags in master.blade.php.');
            }
            window.addToCart = function(itemId, name, price, quantity, instructions, route) {
                console.log('window.addToCart called:', { itemId, name, price, quantity, instructions, route });
                Alpine.store('cart').addToCart(itemId, name, price, quantity, instructions, route);
                const modal = new bootstrap.Modal(document.getElementById('cartModal'));
                modal.show();
            };
        });
    </script>

    @stack('scripts')
</body>
</html>