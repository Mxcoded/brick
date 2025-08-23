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

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">

    <script src="https://unpkg.com/alpinejs@3.12.0/dist/cdn.min.js" defer></script>
    <script src="https://unpkg.com/@alpinejs/persist@3.12.0/dist/cdn.min.js" defer></script>

    <style>
        :root {
            --primary-color: #d4af37;
            /* Elegant gold */
            --primary-hover: #bfa133;
            --secondary-color: #7a8a99;
            --success-color: #3ca67c;
            --warning-color: #f4a261;
            --info-color: #5ac8fa;
            --light-color: #f4f5f7;
            --dark-color: #1c1f26;
            --border-radius: 14px;
            --box-shadow: 0 6px 24px rgba(0, 0, 0, 0.06);
            --transition: all 0.25s ease-in-out;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }


        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #fdfcfb 0%, #e8d9c7 50%, #d4af37 100%);
            min-height: 100vh;
            color: #333;
            overflow-x: hidden;
            transition: var(--transition);
        }

        body.dark-mode {
            background: linear-gradient(135deg, rgb(35, 35, 35) 0%, rgb(139, 69, 19) 50%, rgb(218, 165, 32) 100%);
            color: #e9ecef;
        }

        body.dark-mode .glass-morphism,
        body.dark-mode .navbar,
        body.dark-mode .card,
        body.dark-mode .modal-content {
            background: rgba(30, 30, 30, 0.95);
            color: #e0e0e0;
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: var(--box-shadow);
            border-radius: var(--border-radius);
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
            background-color: rgba(255, 255, 255, 0.85);
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 24px;
            transition: var(--transition);
        }

        .button {
            background-color: var(--primary-color);
            color: #fff;
            padding: 12px 24px;
            border: none;
            border-radius: var(--border-radius);
            font-size: 16px;
            font-weight: 500;
            box-shadow: var(--box-shadow);
            transition: var(--transition);
        }

        .button:hover {
            background-color: var(--primary-hover);
            cursor: pointer;
        }

        .navbar {
            background-color: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(10px);
            box-shadow: var(--box-shadow);
            padding: 16px 32px;
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
            0% {
                transform: translateY(100vh) rotate(0deg);
            }

            100% {
                transform: translateY(-100px) rotate(360deg);
            }
        }

        @keyframes pulse {
            0% {
                transform: scale(1);
            }

            50% {
                transform: scale(1.1);
            }

            100% {
                transform: scale(1);
            }
        }

        .loading-shimmer {
            background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: shimmer 1.5s infinite;
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        .fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
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
        
        .quantity-input {
            width: 50px !important;
            text-align: center;
            border: none;
            background: transparent;
            -moz-appearance: textfield;
        }
        .quantity-input::-webkit-outer-spin-button,
        .quantity-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
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
    @php
        $type = $type ?? 'online';
        $sourceId = $sourceModel->id ?? null;
        $cartKey = $type === 'online' ? 'online_cart' : $type . '_cart';
        $routeParams = $type !== 'online' ? ['type' => $type, 'source' => $sourceId] : [];
    @endphp

    @if (View::getSection('title') !== 'Welcome')
        <nav class="navbar navbar-expand-lg">
            <div class="container-fluid">
                <a class="navbar-brand" href="{{ route('restaurant.landing') }}">Taste Restaurant</a>
                <button class="navbar-toggler" type="button" @click="showMobileMenu = !showMobileMenu"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" x-bind:class="{ 'show': showMobileMenu }">
                    <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('restaurant.landing') }}">Home</a>
                        </li>
                        <li class="nav-item">
                             <a class="nav-link" href="{{ route($type === 'online' ? 'restaurant.online.menu' : 'restaurant.menu', $routeParams) }}">Menu</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('restaurant.online.orders') }}">Order History</a>
                        </li>
                        <li class="nav-item">
                            <button class="btn btn-outline-primary btn-sm rounded-pill"
                                @click="$store.persist.isDarkMode = !$store.persist.isDarkMode"
                                aria-label="Toggle dark mode">
                                <i class="fas" :class="$store.persist.isDarkMode ? 'fa-sun' : 'fa-moon'"></i>
                                <span x-text="$store.persist.isDarkMode ? 'Light Mode' : 'Dark Mode'"></span>
                            </button>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
    @endif
    <div class="floating-elements">
        <div class="floating-element"
            style="left: 10%; top: 20%; width: 50px; height: 50px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-utensils" style="color: #fff; font-size: 20px;"></i>
        </div>
        <div class="floating-element"
            style="left: 80%; top: 60%; width: 70px; height: 70px; background: var(--secondary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-coffee" style="color: #fff; font-size: 24px;"></i>
        </div>
        <div class="floating-element"
            style="left: 30%; top: 80%; width: 40px; height: 40px; background: var(--success-color); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-concierge-bell" style="color: #fff; font-size: 16px;"></i>
        </div>
        <div class="floating-element"
            style="left: 60%; top: 30%; width: 60px; height: 60px; background: var(--warning-color); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-wine-glass" style="color: #fff; font-size: 20px;"></i>
        </div>
        <div class="floating-element"
            style="left: 20%; top: 50%; width: 80px; height: 80px; background: var(--info-color); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-ice-cream" style="color: #fff; font-size: 28px;"></i>
        </div>
        <div class="floating-element"
            style="left: 50%; top: 50%; width: 100px; height: 100px; background: var(--dark-color); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-drumstick-bite" style="color: #fff; font-size: 32px;"></i>
        </div>
        <div class="floating-element"
            style="left: 70%; top: 20%; width: 90px; height: 90px; background: var(--primary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-bread-slice" style="color: #fff; font-size: 30px;"></i>
        </div>
        <div class="floating-element"
            style="left: 40%; top: 10%; width: 50px; height: 50px; background: var(--secondary-color); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
            <i class="fas fa-cookie" style="color: #fff; font-size: 20px;"></i>
        </div>
    </div>

    @yield('content')
    @if (View::getSection('title') !== 'Welcome')
        <button class="cart-indicator" data-bs-toggle="modal" data-bs-target="#cartModal">
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-count" x-show="$store.cart.items.length > 0" x-text="$store.cart.items.length"></span>
        </button>

        <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true"
            @show.bs.modal="$store.cart.refreshCart()">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form action="{{ route($type === 'online' ? 'restaurant.online.order.submit' : 'restaurant.order.submit', $routeParams) }}" method="POST">
                    @csrf
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="cartModalLabel">
                                <i class="fas fa-shopping-cart me-2"></i> Your Cart
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div id="cart-alerts"></div>
                            <template x-if="$store.cart.isLoading">
                                <div class="modal-loading">
                                    <i class="fas fa-spinner fa-spin fa-3x text-muted"></i>
                                </div>
                            </template>
                            <template x-if="!$store.cart.isLoading">
                                <div>
                                    <template x-if="$store.cart.items.length === 0">
                                        <div class="text-center py-5">
                                            <i class="fas fa-shopping-cart text-muted mb-3" style="font-size: 4rem; opacity: 0.3;"></i>
                                            <h5 class="text-muted">Your cart is empty</h5>
                                            <p class="text-muted">Add some delicious items to get started!</p>
                                        </div>
                                    </template>
                                    <template x-if="$store.cart.items.length > 0">
                                        <div class="cart-items">
                                            <template x-for="(item, index) in $store.cart.items" :key="index">
                                                <div class="card mb-3 border-0 shadow-sm glass-morphism">
                                                    <div class="card-body">
                                                        <div class="d-flex justify-content-between align-items-start mb-3">
                                                            <div class="flex-grow-1">
                                                                <h6 class="fw-bold mb-1" x-text="item.name"></h6>
                                                                <p class="text-muted small mb-2" x-show="item.instructions" x-text="'Instructions: ' + item.instructions"></p>
                                                                <p class="fw-semibold text-primary mb-0" x-text="`₦${Number(item.price).toLocaleString()}`"></p>
                                                            </div>
                                                            <button type="button" @click="$store.cart.removeItem(index)" class="btn btn-sm btn-outline-danger rounded-circle" style="width: 35px; height: 35px;" aria-label="Remove item">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </div>
                                                        <div class="d-flex justify-content-between align-items-center">
                                                            <div class="quantity-controls">
                                                                <button type="button" class="quantity-btn" @click="$store.cart.updateQuantity(index, item.quantity - 1)" :disabled="item.quantity <= 1">
                                                                    <i class="fas fa-minus"></i>
                                                                </button>
                                                                <input type="number" x-model.number.debounce.500ms="item.quantity" @change="$store.cart.updateQuantity(index, item.quantity)" class="form-control form-control-sm quantity-input" min="1" required>
                                                                <button type="button" class="quantity-btn" @click="$store.cart.updateQuantity(index, item.quantity + 1)">
                                                                    <i class="fas fa-plus"></i>
                                                                </button>
                                                            </div>
                                                            <p class="fw-bold mb-0 h5 text-success" x-text="`₦${(item.price * item.quantity).toLocaleString()}`"></p>
                                                        </div>
                                                    </div>
                                                </div>
                                            </template>
                                            <div class="cart-summary mt-4 p-3 glass-morphism rounded">
                                                 <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <span class="h5 mb-0">Total:</span>
                                                    <span class="h4 fw-bold text-success mb-0" x-text="`₦${$store.cart.totalPrice.toLocaleString()}`"></span>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </template>
                        </div>
                        <div class="modal-footer" x-show="$store.cart.items.length > 0 && !$store.cart.isLoading">
                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Continue Shopping</button>
                                @if ($type === 'online')
                                    <button type="button" class="btn btn-primary-custom btn-lg" @click="$store.cart.redirectTo()"  :disabled="$store.cart.isSubmitting">
                                        <span x-show="!$store.cart.isSubmitting"><i class="fas fa-credit-card me-2"></i>Proceed to Checkout</span>
                                    </button>
                                @else
                                    <button type="submit" class="btn btn-primary-custom btn-lg" :disabled="$store.cart.isSubmitting">
                                        <span x-show="!$store.cart.isSubmitting"><i class="fas fa-credit-card me-2"></i>Proceed to Checkout</span>
                                        <span x-show="$store.cart.isSubmitting"><i class="fas fa-spinner fa-spin me-2"></i>Processing...</span>
                                    </button>
                                @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.store('persist', {
                    isDarkMode: false
                });

                Alpine.store('cart', {
                    items: [],
                    isLoading: true,
                    isSubmitting: false,
                    routes: {
                        add:    '{{ route($type === 'online' ? 'restaurant.online.cart.add' : 'restaurant.cart.add', $routeParams) }}',
                        update: '{{ route($type === 'online' ? 'restaurant.online.cart.update' : 'restaurant.cart.update', $routeParams) }}',
                        remove: '{{ route($type === 'online' ? 'restaurant.online.cart.remove' : 'restaurant.cart.remove', $routeParams) }}',
                        get:    '{{ route($type === 'online' ? 'restaurant.online.cart.get' : 'restaurant.cart.get', $routeParams) }}',
                        cart:   "{{ route($type === 'online' ? 'restaurant.online.cart' : 'restaurant.cart', $routeParams) }}"
                    },
                    
                    init() {
                        
                        this.refreshCart();
                    },
                    
                    get totalPrice() {
                        return this.items.reduce((sum, item) => sum + (item.price * item.quantity), 0);
                    },
                    redirectTo() {
                        window.location.href = this.routes.cart;
                    },
                    async fetchApi(route, method, body) {
                        try {
                            const response = await fetch(route, {
                                method: method,
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                                },
                                body: JSON.stringify(body)
                            });
                            if (!response.ok) throw new Error('Network response was not ok');
                            return await response.json();
                        } catch (error) {
                            console.error('API Error:', error);
                            this.showAlert('danger', 'An error occurred. Please try again.');
                            return null;
                        }
                    },
                    
                    async addToCart(itemId, name, price, quantity, instructions) {
                        this.isSubmitting = true;
                        if (this.items.some(item => item.item_id === itemId)) {
                            this.showAlert('info', `${name} is already in your cart.`);
                            new bootstrap.Modal(document.getElementById('cartModal')).show();
                            this.isSubmitting = false;
                            return;
                        }
                        const data = await this.fetchApi(this.routes.add, 'POST', { item_id: itemId, quantity, instructions });
                        if (data && data.success) {
                            this.items = data.cart;
                            this.showAlert('success', `${name} has been added to your cart.`);
                            new bootstrap.Modal(document.getElementById('cartModal')).show();
                        } else {
                            this.showAlert('danger', data?.message || 'Failed to add item.');
                        }
                        this.isSubmitting = false;
                    },

                    async updateQuantity(index, quantity) {
                        const originalQuantity = this.items[index].quantity;
                        if (quantity < 1) return;

                        this.items[index].quantity = quantity;
                        this.isSubmitting = true;

                        const data = await this.fetchApi(this.routes.update, 'POST', { index, quantity });
                        if (data && data.success) {
                            this.items = data.cart;
                        } else {
                            this.items[index].quantity = originalQuantity; // Revert on failure
                            this.showAlert('danger', data?.message || 'Failed to update cart.');
                        }
                        this.isSubmitting = false;
                    },

                    async removeItem(index) {
                        const originalItems = [...this.items];
                        this.items.splice(index, 1);
                        this.isSubmitting = true;
                        
                        const data = await this.fetchApi(this.routes.remove, 'POST', { index });
                        if (data && data.success) {
                            this.items = data.cart;
                            this.showAlert('info', 'Item removed from cart.');
                        } else {
                            this.items = originalItems; // Revert on failure
                            this.showAlert('danger', data?.message || 'Failed to remove item.');
                        }
                        this.isSubmitting = false;
                    },

                    async refreshCart() {
                        this.isLoading = true;
                        try {
                            const response = await fetch(this.routes.get, { headers: { 'Accept': 'application/json' }});
                            if (!response.ok) throw new Error('Network error');
                            const data = await response.json();
                            if (data.success) {
                                this.items = data.cart;
                            }
                        } catch(error) {
                            console.log('Cart refreshed:', error);
                             console.error('Error refreshing cart:', error);
                        } finally {
                            this.isLoading = false;
                        }
                    },
                    
                    showAlert(type, message) {
                        const alertContainer = document.getElementById('cart-alerts');
                        if (!alertContainer) return;
                        
                        const alert = document.createElement('div');
                        alert.className = `alert alert-${type} alert-dismissible fade show`;
                        alert.role = 'alert';
                        alert.innerHTML = `
                            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
                            ${message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        `;
                        alertContainer.appendChild(alert);
                        setTimeout(() => alert.remove(), 4000);
                    }
                });

                Alpine.data('appData', () => ({
                    showMobileMenu: false
                }));
            });
        </script>
    @endif
    @stack('scripts')
</body>

</html>