@extends('restaurant::layouts.master')
@section('title', 'Menu')

@section('content')
    <div class="container-fluid py-5" x-data="{
        activeTab: 'all',
        search: '',
        selectedCategory: '',
        sortBy: 'name',
        sortOptions: [
            { value: 'name', label: 'Name A-Z' },
            { value: 'price_asc', label: 'Price Low-High' },
            { value: 'price_desc', label: 'Price High-Low' }
        ],
        categories: {{ json_encode($categories) }},
        categoryNames: {{ json_encode($category_names) }},
        instructions: {},
        quickViewItem: null,
        sidebarOpen: false,
        getFilteredAndSortedItems(category) {
            let items = category.menu_items.filter(item =>
                !this.search ||
                item.name.toLowerCase().includes(this.search.toLowerCase()) ||
                (item.description && item.description.toLowerCase().includes(this.search.toLowerCase()))
            );
            if (this.sortBy === 'name') {
                items.sort((a, b) => a.name.localeCompare(b.name));
            } else if (this.sortBy === 'price_asc') {
                items.sort((a, b) => a.price - b.price);
            } else if (this.sortBy === 'price_desc') {
                items.sort((a, b) => b.price - a.price);
            }
            return items;
        },
        toggleSidebar() {
            this.sidebarOpen = !this.sidebarOpen;
        }
    }">
        <!-- Hero Section -->
        <div class="hero-section mb-5">
            <div class="hero-content text-center">
                <h1 class="hero-title display-4 fw-bold text-dark animate__animated animate__fadeInDown">
                    @if ($type === 'table')
                        Menu - Table {{ $sourceModel->number }}
                    @elseif ($type === 'room')
                        Menu - Room {{ $sourceModel->name }}
                    @else
                        Online Menu
                    @endif
                </h1>
                <p class="hero-subtitle lead text-muted animate__animated animate__fadeInUp">
                    @if ($type === 'online')
                        Discover extraordinary flavors crafted with passion and precision
                    @else
                        Browse and add your favorites to your cart
                    @endif
                </p>
                <button class="btn btn-custom btn-primary-custom btn-lg pulse"
                    @click="document.querySelector('.search-filter-section').scrollIntoView({ behavior: 'smooth' })">
                    <i class="fas fa-arrow-down me-2"></i>Explore Menu
                </button>
            </div>
        </div>

        <!-- Success/Error Alerts -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show text-center glass-morphism rounded-3 shadow-sm"
                role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show text-center glass-morphism rounded-3 shadow-sm"
                role="alert">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Search and Filter Section -->
        <div class="search-filter-section mx-auto mb-5" style="max-width: 1200px;">
            <div class="row g-4 align-items-center">
                <div class="col-lg-6">
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" x-model="search" placeholder="Search delicious dishes..." class="form-control"
                            aria-label="Search menu items">
                    </div>
                </div>
                <div class="col-lg-3">
                    <select x-model="selectedCategory" class="form-select" aria-label="Filter by category">
                        <option value="">All Categories</option>
                        <template x-for="category in categories" :key="category.id">
                            <option :value="category.id" x-text="category.name"></option>
                        </template>
                        <template x-for="category in categories" :key="category.id">
                            <template x-for="sub in category.children_recursive" :key="sub.id">
                                <option :value="sub.id" x-text="`${category.name} > ${sub.name}`"></option>
                            </template>
                        </template>
                    </select>
                </div>
                <div class="col-lg-3">
                    <select x-model="sortBy" class="form-select" aria-label="Sort by">
                        <template x-for="option in sortOptions" :key="option.value">
                            <option :value="option.value" x-text="option.label"></option>
                        </template>
                    </select>
                </div>
                <div class="col-12 d-lg-none text-center">
                    <button class="btn btn-outline-primary w-100" @click="toggleSidebar">
                        <i class="fas fa-list me-2"></i>Toggle Categories
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Content Layout -->
        <div class="row" style="max-width: 1400px; margin: 0 auto;">
            <!-- Category Sidebar -->
            <div class="col-lg-3">
                <div class="category-sidebar" :class="{ 'show': sidebarOpen }">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h5 class="fw-bold text-center m-0">
                            <i class="fas fa-list-ul me-2 text-primary"></i>Categories
                        </h5>
                        <button class="btn btn-outline-secondary d-lg-none" @click="toggleSidebar">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                    <ul class="category-nav">
                        <li class="nav-item">
                            <a class="nav-link" :class="{ 'active': activeTab === 'all' }"
                                @click="activeTab = 'all'; selectedCategory = ''" aria-label="All categories">
                                <i class="fas fa-th-large me-2"></i>All Items
                            </a>
                        </li>
                        <template x-for="category in categories" :key="category.id">
                            <li class="nav-item">
                                <a class="nav-link" :class="{ 'active': activeTab === category.name.toLowerCase() }"
                                    @click="activeTab = category.name.toLowerCase(); selectedCategory = category.id"
                                    x-text="category.name" :aria-label="`Category: ${category.name}`"></a>
                                <template x-if="category.children_recursive.length > 0">
                                    <ul class="nav flex-column ms-3">
                                        <template x-for="sub in category.children_recursive" :key="sub.id">
                                            <li class="nav-item">
                                                <a class="nav-link" :class="{ 'active': selectedCategory === sub.id }"
                                                    @click="selectedCategory = sub.id" x-text="sub.name"
                                                    :aria-label="`Sub-category: ${sub.name}`"></a>
                                            </li>
                                        </template>
                                    </ul>
                                </template>
                            </li>
                        </template>
                    </ul>
                </div>
            </div>

            <!-- Menu Grid -->
            <div class="col-lg-9">
                <!-- Empty State -->
                <template x-if="categories.length === 0">
                    <div class="alert alert-info text-center rounded-3 shadow-sm">
                        <i class="bi bi-info-circle me-2"></i>No menu items available.
                    </div>
                </template>

                <!-- Featured Items Section -->
                <template x-if="activeTab === 'all' && !selectedCategory">
                    <div class="mb-5 fade-in">
                        <div class="d-flex align-items-center mb-4">
                            <h2 class="fw-bold text-gradient me-3">
                                <i class="fas fa-star text-warning me-2"></i>Featured Dishes
                            </h2>
                            <div class="flex-grow-1"
                                style="height: 2px; background: linear-gradient(90deg, var(--primary-color), transparent);">
                            </div>
                        </div>
                        <div class="row g-4">
                            <template x-for="category in categories" :key="category.id">
                                <template x-for="item in getFilteredAndSortedItems(category).slice(0, 4)"
                                    :key="item.id">
                                    <div class="col-xl-4 col-lg-6 col-md-6">
                                        <div class="menu-item-card h-100">
                                            <div class="menu-image-container">
                                                <img :src="item.image && item.image !== '' ? '{{ asset('storage') }}/' +
                                                    item.image : '{{ asset('storage/images/menudefaultimage.png') }}'"
                                                    :alt="item.name" class="menu-image">
                                                <div class="image-overlay"></div>
                                                <span class="price-badge"
                                                    x-text="`₦${Number(item.price).toLocaleString(undefined, { minimumFractionDigits: 2 })}`"></span>
                                            </div>
                                            <div class="p-3">
                                                <h5 class="fw-bold mb-2" x-text="item.name"></h5>
                                                <p class="text-muted mb-3"
                                                    x-text="item.description || 'No description available'"></p>
                                                <form
                                                    @submit.prevent="window.addToCart(item.id, item.name, item.price, $event.target.quantity.value, $event.target.instructions.value, '{{ route($type === 'online' ? 'restaurant.online.cart.add' : 'restaurant.cart.add', $type === 'online' ? [] : [$type, $sourceModel->id]) }}')">
                                                    @csrf
                                                    <input type="hidden" name="item_id" :value="item.id">
                                                    <div class="mb-3">
                                                        <label for="quantity-${item.id}"
                                                            class="form-label small">Quantity</label>
                                                        <input type="number" name="quantity" :id="`quantity-${item.id}`"
                                                            class="form-control form-control-sm" value="1"
                                                            min="1" required>
                                                    </div>
                                                    <div class="mb-3">
                                                        <label for="instructions-${item.id}"
                                                            class="form-label small">Special Instructions</label>
                                                        <textarea name="instructions" :id="`instructions-${item.id}`" class="form-control form-control-sm" rows="2"
                                                            maxlength="255" placeholder="E.g., No onions"></textarea>
                                                    </div>
                                                    <button type="submit" class="btn btn-custom btn-primary-custom w-100"
                                                        :disabled="$store.cart.isAdding">
                                                        <i class="fas"
                                                            :class="$store.cart.isAdding ? 'fa-spinner fa-spin' : 'fa-plus'"></i>
                                                        <span x-show="!$store.cart.isAdding">Add to Cart</span>
                                                        <span x-show="$store.cart.isAdding">Adding...</span>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </template>
                        </div>
                    </div>
                </template>

                <!-- Category Items -->
                <template x-for="category in categories" :key="category.id">
                    <template
                        x-if="activeTab === category.name.toLowerCase() || selectedCategory === category.id || selectedCategory === ''">
                        <div class="mb-5 fade-in">
                            <div class="d-flex align-items-center mb-4">
                                <h2 class="fw-bold text-gradient me-3" x-text="category.name"></h2>
                                <div class="flex-grow-1"
                                    style="height: 2px; background: linear-gradient(90deg, var(--primary-color), transparent);">
                                </div>
                            </div>
                            <div class="row g-4">
                                <template x-for="(item, index) in $store.cart.items" :key="item.item_id">
                                    <div class="card mb-3 border-0 shadow-sm glass-morphism">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-3">
                                                <div class="flex-grow-1">
                                                    <h6 class="fw-bold mb-1" x-text="item.name || 'Unknown Item'"></h6>
                                                    <p class="text-muted small mb-2" x-show="item.instructions"
                                                        x-text="'Instructions: ' + (item.instructions || 'None')"></p>
                                                    <p class="fw-semibold text-primary mb-0"
                                                        x-text="item.price ? `₦${Number(item.price).toLocaleString(undefined, { minimumFractionDigits: 2 })} each` : 'Price not available'">
                                                    </p>
                                                </div>
                                                <form
                                                    action="{{ route(
                                                        isset($type) && $type === 'online' ? 'restaurant.online.cart.remove' : 'restaurant.cart.remove',
                                                        isset($type) && $type !== 'online' ? [$type, $sourceModel->id ?? null] : [],
                                                    ) }}"
                                                    method="POST">
                                                    @csrf
                                                    <input type="hidden" name="index" :value="index">
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-danger rounded-circle"
                                                        style="width: 35px; height: 35px;" aria-label="Remove item">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center">
                                                <form
                                                    action="{{ route(
                                                        isset($type) && $type === 'online' ? 'restaurant.online.cart.update' : 'restaurant.cart.update',
                                                        isset($type) && $type !== 'online' ? [$type, $sourceModel->id ?? null] : [],
                                                    ) }}"
                                                    method="POST"
                                                    @submit.prevent="updateCart($event, item.item_id, index)">
                                                    @csrf
                                                    <input type="hidden" name="index" :value="index">
                                                    <div class="quantity-controls">
                                                        <button type="button" class="quantity-btn"
                                                            @click="if (item.quantity > 1) { item.quantity--; updateCart($event, item.item_id, index); }"
                                                            aria-label="Decrease quantity">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                        <input type="number" name="quantity"
                                                            x-model.number="item.quantity"
                                                            class="form-control form-control-sm w-75 d-inline text-center"
                                                            min="1" required>
                                                        <button type="button" class="quantity-btn"
                                                            @click="item.quantity++; updateCart($event, item.item_id, index);"
                                                            aria-label="Increase quantity">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                </form>
                                                <div class="text-end">
                                                    <p class="fw-bold mb-0 h5 text-success"
                                                        x-text="item.price && item.quantity ? `₦${Number(item.price * item.quantity).toLocaleString(undefined, { minimumFractionDigits: 2 })}` : 'Total not available'">
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </template>
                    <!-- Subcategories -->
                    <template x-for="sub in category.children_recursive" :key="sub.id">
                        <template x-if="selectedCategory === sub.id">
                            <div class="mb-5 fade-in">
                                <div class="d-flex align-items-center mb-4">
                                    <h2 class="fw-bold text-gradient me-3" x-text="sub.name"></h2>
                                    <div class="flex-grow-1"
                                        style="height: 2px; background: linear-gradient(90deg, var(--primary-color), transparent);">
                                    </div>
                                </div>
                                <div class="row g-4">
                                    <template x-for="item in getFilteredAndSortedItems(sub)" :key="item.id">
                                        <div class="col-xl-4 col-lg-6 col-md-6">
                                            <div class="menu-item-card h-100">
                                                <div class="menu-image-container">
                                                    <img :src="item.image && item.image !== '' ?
                                                        '{{ asset('storage') }}/' + item.image :
                                                        '{{ asset('storage/images/menudefaultimage.png') }}'"
                                                        :alt="item.name" class="menu-image">
                                                    <div class="image-overlay"></div>
                                                    <span class="price-badge"
                                                        x-text="`₦${Number(item.price).toLocaleString(undefined, { minimumFractionDigits: 2 })}`"></span>
                                                </div>
                                                <div class="p-3">
                                                    <h5 class="fw-bold mb-2" x-text="item.name"></h5>
                                                    <p class="text-muted mb-3"
                                                        x-text="item.description || 'No description available'"></p>
                                                    <form
                                                        action="{{ route($type === 'online' ? 'restaurant.online.cart.add' : 'restaurant.cart.add', $type === 'online' ? [] : [$type, $sourceModel->id]) }}"
                                                        method="POST"
                                                        @submit.prevent="window.addToCart(item.id, item.name, item.price, $event.target.quantity.value, $event.target.instructions.value, $event.target.action); showSuccessMessage(`Added ${item.name} to cart`);">
                                                        @csrf
                                                        <input type="hidden" name="item_id" :value="item.id">
                                                        <div class="mb-3">
                                                            <label :for="`quantity-${item.id}`"
                                                                class="form-label small">Quantity</label>
                                                            <input type="number" name="quantity"
                                                                :id="`quantity-${item.id}`"
                                                                class="form-control form-control-sm" value="1"
                                                                min="1" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label :for="`instructions-${item.id}`"
                                                                class="form-label small">Special Instructions</label>
                                                            <textarea name="instructions" :id="`instructions-${item.id}`" class="form-control form-control-sm" rows="2"
                                                                maxlength="255" placeholder="E.g., No onions"></textarea>
                                                        </div>
                                                        <button type="submit"
                                                            class="btn btn-custom btn-primary-custom w-100"
                                                            :disabled="$store.cart.isAdding || $store.cart.isRefreshing">
                                                            <i class="fas"
                                                                :class="$store.cart.isAdding || $store.cart.isRefreshing ?
                                                                    'fa-spinner fa-spin' : 'fa-plus'"></i>
                                                            <span
                                                                x-show="!($store.cart.isAdding || $store.cart.isRefreshing)">Add
                                                                to Cart</span>
                                                            <span
                                                                x-show="$store.cart.isAdding || $store.cart.isRefreshing">Adding...</span>
                                                        </button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </template>
                </template>
            </div>
        </div>
        <!-- Quick View Modal -->
        <div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content glass-morphism">
                    <div class="modal-header">
                        <h5 class="modal-title" id="quickViewModalLabel"
                            x-text="quickViewItem ? quickViewItem.name : 'Item Details'"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <template x-if="quickViewItem">
                            <div class="row">
                                <div class="col-md-6">
                                    <img :src="quickViewItem.image && quickViewItem.image.includes('storage') ? quickViewItem
                                        .image : '/storage/images/menudefaultimage.png'"
                                        :alt="quickViewItem.name" class="img-fluid rounded"
                                        style="max-height: 300px; object-fit: cover;">
                                </div>
                                <div class="col-md-6">
                                    <h4 x-text="quickViewItem.name"></h4>
                                    <p class="text-muted"
                                        x-text="quickViewItem.description || 'No description available'"></p>
                                    <p class="fw-bold">Price: ₦<span x-text="quickViewItem.price.toFixed(2)"></span></p>
                                    <div class="mb-3">
                                        <label for="quantity" class="form-label">Quantity</label>
                                        <input type="number"
                                            x-model.number="instructions[quickViewItem.id] ? instructions[quickViewItem.id].quantity : 1"
                                            min="1" class="form-control form-control-sm w-50"
                                            placeholder="Quantity">
                                    </div>
                                    <div class="mb-3">
                                        <label for="instructions" class="form-label">Special Instructions</label>
                                        <textarea x-model="instructions[quickViewItem.id] ? instructions[quickViewItem.id].notes : ''" class="form-control"
                                            rows="3" placeholder="Any special requests?"></textarea>
                                    </div>
                                    <button class="btn btn-primary-custom w-100"
                                        @click="window.addToCart(
                                quickViewItem.id,
                                quickViewItem.name,
                                quickViewItem.price,
                                instructions[quickViewItem.id] ? parseInt(instructions[quickViewItem.id].quantity) : 1,
                                instructions[quickViewItem.id] ? instructions[quickViewItem.id].notes : '',
                                '{{ route($type === 'online' ? 'restaurant.online.cart.add' : 'restaurant.cart.add', $type === 'online' ? [] : [$type, $sourceModel->id]) }}'
                            )">
                                        <i class="fas fa-cart-plus me-2"></i>Add to Cart
                                    </button>
                                </div>
                            </div>
                        </template>
                        <template x-if="!quickViewItem">
                            <p class="text-center">No item selected.</p>
                        </template>
                    </div>
                </div>
            </div>
        </div>
        <style>
            .hero-section {
                background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4');
                background-size: cover;
                background-position: center;
                padding: 5rem 1rem;
                border-radius: var(--border-radius);
                margin-bottom: 2rem;
            }

            .hero-content {
                max-width: 800px;
                margin: 0 auto;
            }

            .hero-title {
                font-size: 3rem;
                font-weight: 700;
                margin-bottom: 1rem;
            }

            .hero-subtitle {
                font-size: 1.25rem;
                margin-bottom: 2rem;
            }

            .alert-dismissible {
                position: relative;
                z-index: 1000;
                max-width: 800px;
                margin: 0 auto 1rem;
            }

            .search-filter-section {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(20px);
                padding: 2rem;
                border-radius: var(--border-radius);
                box-shadow: var(--box-shadow);
                margin-bottom: 2rem;
                position: relative;
                z-index: 10;
            }

            .form-control,
            .form-select {
                border: 2px solid rgba(0, 0, 0, 0.1);
                border-radius: 50px;
                padding: 0.8rem 1.5rem;
                font-weight: 500;
                transition: var(--transition);
                background: rgba(255, 255, 255, 0.9);
            }

            .form-control:focus,
            .form-select:focus {
                border-color: var(--primary-color);
                box-shadow: 0 0 0 3px rgba(217, 83, 79, 0.1);
                background: white;
            }

            .input-group-text {
                background: var(--primary-color);
                color: white;
                border: none;
                border-radius: 50px 0 0 50px;
            }

            .category-sidebar {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(20px);
                border-radius: var(--border-radius);
                padding: 1.5rem;
                position: sticky;
                top: 120px;
                height: fit-content;
                max-height: calc(100vh - 140px);
                overflow-y: auto;
                box-shadow: var(--box-shadow);
                transition: var(--transition);
            }

            .category-sidebar.show {
                left: 0;
            }

            .category-nav {
                list-style: none;
                padding: 0;
            }

            .category-nav .nav-item {
                margin-bottom: 0.5rem;
            }

            .category-nav .nav-link {
                padding: 0.8rem 1rem;
                border-radius: 10px;
                color: #666;
                text-decoration: none;
                transition: var(--transition);
                position: relative;
                overflow: hidden;
            }

            .category-nav .nav-link::before {
                content: '';
                position: absolute;
                top: 0;
                left: -100%;
                width: 100%;
                height: 100%;
                background: linear-gradient(135deg, var(--primary-color), #ff6b6b);
                transition: var(--transition);
                z-index: -1;
            }

            .category-nav .nav-link:hover::before,
            .category-nav .nav-link.active::before {
                left: 0;
            }

            .category-nav .nav-link:hover,
            .category-nav .nav-link.active {
                color: white;
                transform: translateX(5px);
            }

            .menu-item-card {
                background: rgba(255, 255, 255, 0.95);
                backdrop-filter: blur(20px);
                border-radius: var(--border-radius);
                padding: 1.5rem;
                transition: var(--transition);
                border: 1px solid rgba(255, 255, 255, 0.2);
                position: relative;
                overflow: hidden;
            }

            .menu-item-card:hover {
                transform: translateY(-10px) scale(1.02);
                box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
            }

            .menu-image-container {
                position: relative;
                height: 200px;
                border-radius: 10px;
                overflow: hidden;
                margin-bottom: 1rem;
            }

            .menu-image {
                width: 100%;
                height: 100%;
                object-fit: cover;
                transition: var(--transition);
            }

            .menu-item-card:hover .menu-image {
                transform: scale(1.1);
            }

            .image-overlay {
                position: absolute;
                top: 0;
                left: 0;
                right: 0;
                bottom: 0;
                background: linear-gradient(45deg, rgba(0, 0, 0, 0.3), transparent);
                opacity: 0;
                transition: var(--transition);
            }

            .menu-item-card:hover .image-overlay {
                opacity: 1;
            }

            .price-badge {
                position: absolute;
                top: 10px;
                right: 10px;
                background: var(--primary-color);
                color: white;
                padding: 0.5rem 1rem;
                border-radius: 20px;
                font-weight: 600;
                font-size: 0.9rem;
            }

            .btn-custom {
                border: none;
                border-radius: 50px;
                padding: 0.8rem 2rem;
                font-weight: 600;
                transition: var(--transition);
                position: relative;
                overflow: hidden;
            }

            .btn-custom::before {
                content: '';
                position: absolute;
                top: 50%;
                left: 50%;
                width: 0;
                height: 0;
                border-radius: 50%;
                background: rgba(255, 255, 255, 0.3);
                transition: var(--transition);
                transform: translate(-50%, -50%);
            }

            .btn-custom:hover::before {
                width: 300px;
                height: 300px;
            }

            .btn-primary-custom {
                background: linear-gradient(135deg, var(--primary-color), #ff6b6b);
                color: white;
            }

            .btn-primary-custom:hover {
                transform: translateY(-2px);
                box-shadow: 0 10px 30px rgba(217, 83, 79, 0.3);
            }

            .form-control-sm,
            .form-select {
                border-radius: 0.5rem;
            }

            @media (max-width: 768px) {
                .hero-title {
                    font-size: 2.5rem;
                }

                .search-filter-section {
                    padding: 1.5rem;
                }

                .category-sidebar {
                    position: fixed;
                    top: 0;
                    left: -300px;
                    width: 300px;
                    height: 100vh;
                    z-index: 1050;
                    transition: var(--transition);
                }

                .category-sidebar.show {
                    left: 0;
                }

                .menu-item-card {
                    padding: 1rem;
                }

                .menu-image-container {
                    height: 150px;
                }
            }
        </style>
    </div>

    @push('scripts')
        <script>
            // Smooth scroll to menu section
            document.querySelectorAll('.btn-custom.pulse').forEach(btn => {
                btn.addEventListener('click', () => {
                    document.querySelector('.search-filter-section').scrollIntoView({
                        behavior: 'smooth'
                    });
                });
            });

            // // Initialize Alpine.js store for cart indicator (optional enhancement)
            // document.addEventListener('alpine:init', () => {
            //     Alpine.store('cart', {
            //         items: [],
            //         totalPrice: 0,
            //         add(item) {
            //             this.items.push(item);
            //             this.updateTotal();
            //         },
            //         updateTotal() {
            //             this.totalPrice = this.items.reduce((sum, item) => sum + (item.price * item.quantity),
            //                 0);
            //         }
            //     });
            // });
        </script>
    @endpush
@endsection
