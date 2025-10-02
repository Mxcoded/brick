@extends('restaurant::layouts.master')
@section('title', 'Menu')

@section('content')
    <div class="container-fluid py-5" x-data="{
        search: '',
        sortBy: 'name',
        sortOptions: [
            { value: 'name', label: 'Name A-Z' },
            { value: 'price_asc', label: 'Price Low-High' },
            { value: 'price_desc', label: 'Price High-Low' }
        ],
        categories: {{ json_encode($categories) }} || [],
        instructions: {},
        activeTab: {{ $categories->first()->id ?? 'null' }}, // Set the first category as active
        
        getFilteredAndSortedItems(category) {
            if (!category || !Array.isArray(category.menu_items)) return [];
            let items = category.menu_items.filter(item =>
                !this.search ||
                (item.name && item.name.toLowerCase().includes(this.search.toLowerCase())) ||
                (item.description && item.description.toLowerCase().includes(this.search.toLowerCase()))
            );
            if (this.sortBy === 'name') items.sort((a, b) => (a.name || '').localeCompare(b.name || ''));
            else if (this.sortBy === 'price_asc') items.sort((a, b) => (a.price || 0) - (b.price || 0));
            else if (this.sortBy === 'price_desc') items.sort((a, b) => (b.price || 0) - (a.price || 0));
            return items;
        },
        handleAddToCart(item) {
            const itemInstructions = this.instructions[item.id] || '';
            $store.cart.addToCart(item.id, item.name, item.price, 1, itemInstructions);
        }
    }">
        <div class="hero-section mb-5 text-center text-white">
            <h1 class="display-4 fw-bold">
                @if ($type === 'table')
                    Menu - Table {{ $sourceModel->number ?? 'N/A' }}
                @elseif ($type === 'room')
                    Menu - Room {{ $sourceModel->name ?? 'N/A' }}
                @else
                    Online Menu
                @endif
            </h1>
            <p class="lead">Discover extraordinary flavors crafted with passion and precision.</p>
        </div>

        <div class="container-fluid" style="max-width: 1400px;">
            <div class="menu-tabs-container mb-4">
                <ul class="nav nav-tabs">
                    <template x-for="category in categories" :key="category.id">
                        <li class="nav-item">
                            <a class="nav-link" href="#" :class="{ 'active': activeTab === category.id }" @click.prevent="activeTab = category.id" x-text="category.name"></a>
                        </li>
                    </template>
                </ul>
            </div>

            <div class="search-filter-section p-3 rounded-3 shadow-sm mb-5">
                <div class="row g-3 align-items-center">
                    <div class="col-lg-9">
                        <input type="text" x-model.debounce.300ms="search" placeholder="Search within the selected category..." class="form-control" aria-label="Search menu">
                    </div>
                    <div class="col-lg-3">
                        <select x-model="sortBy" class="form-select" aria-label="Sort by">
                            <template x-for="option in sortOptions" :key="option.value">
                                <option :value="option.value" x-text="option.label"></option>
                            </template>
                        </select>
                    </div>
                </div>
            </div>

            <div class="tab-content">
                <template x-for="category in categories" :key="category.id">
                    <div x-show="activeTab === category.id" class="fade-in">
                        <div class="row g-4">
                            <template x-for="item in getFilteredAndSortedItems(category)" :key="item.id">
                                 <div class="col-md-6 col-lg-4 d-flex">
                                     <div class="card menu-item-card w-100 shadow-sm border-0">
                                         <img :src="item.image ? `{{ asset('storage') }}/${item.image}` : '{{ asset('storage/images/menudefaultimage.png') }}'" class="card-img-top" :alt="item.name">
                                         <div class="card-body d-flex flex-column">
                                             <h5 class="card-title fw-bold" x-text="item.name"></h5>
                                             <p class="card-text text-muted small flex-grow-1" x-text="item.description ? item.description.substring(0, 80) + '...' : 'No description available.'"></p>
                                             <h6 class="fw-bold text-primary">₦<span x-text="Number(item.price).toLocaleString()"></span></h6>
                                             <textarea x-model="instructions[item.id]" placeholder="Special instructions..." class="form-control form-control-sm my-2" rows="2"></textarea>
                                             <div class="mt-auto d-grid">
                                                 <button class="btn btn-primary-custom" @click="handleAddToCart(item)" :disabled="$store.cart.isSubmitting">
                                                     <span x-show="!$store.cart.isSubmitting"><i class="fas fa-cart-plus me-2"></i>Add to Cart</span>
                                                     <span x-show="$store.cart.isSubmitting"><i class="fas fa-spinner fa-spin me-2"></i>Adding...</span>
                                                 </button>
                                             </div>
                                         </div>
                                     </div>
                                 </div>
                            </template>
                        </div>

                        <template x-for="subcategory in (category.children_recursive || [])" :key="subcategory.id">
                            <div class="mt-5">
                                <h4 class="category-title mb-4 ms-3 border-start border-primary border-3 ps-2" x-text="subcategory.name"></h4>
                                <div class="row g-4">
                                    <template x-for="subItem in getFilteredAndSortedItems(subcategory)" :key="subItem.id">
                                        <div class="col-md-6 col-lg-4 d-flex">
                                            <div class="card menu-item-card w-100 shadow-sm border-0">
                                                <img :src="subItem.image ? `{{ asset('storage') }}/${subItem.image}` : '{{ asset('storage/images/menudefaultimage.png') }}'" class="card-img-top" :alt="subItem.name">
                                                <div class="card-body d-flex flex-column">
                                                    <h5 class="card-title fw-bold" x-text="subItem.name"></h5>
                                                    <p class="card-text text-muted small flex-grow-1" x-text="subItem.description ? subItem.description.substring(0, 80) + '...' : 'No description available.'"></p>
                                                    <h6 class="fw-bold text-primary">₦<span x-text="Number(subItem.price).toLocaleString()"></span></h6>
                                                    <textarea x-model="instructions[subItem.id]" placeholder="Special instructions..." class="form-control form-control-sm my-2" rows="2"></textarea>
                                                    <div class="mt-auto d-grid">
                                                        <button class="btn btn-primary-custom" @click="handleAddToCart(subItem)" :disabled="$store.cart.isSubmitting">
                                                            <span x-show="!$store.cart.isSubmitting"><i class="fas fa-cart-plus me-2"></i>Add to Cart</span>
                                                            <span x-show="$store.cart.isSubmitting"><i class="fas fa-spinner fa-spin me-2"></i>Adding...</span>
                                                        </button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </template>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
        </div>
        
        <style>
             .hero-section {
                background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1517248135467-4c7edcad34c4') center/cover no-repeat;
                padding: 4rem 1rem;
                border-radius: var(--border-radius);
            }
            .search-filter-section {
                background: rgba(255, 255, 255, 0.9);
                backdrop-filter: blur(10px);
            }
            .menu-tabs-container .nav-tabs {
                border-bottom: 2px solid #dee2e6;
            }
            .menu-tabs-container .nav-tabs .nav-link {
                color: rgb(165, 165, 165);
                font-weight: 600;
                padding: 0.75rem 1.5rem;
                border: none;
                border-bottom: 2px solid transparent;
                transition: var(--transition);
                margin-bottom: -2px;
            }
            body.dark-mode .menu-tabs-container .nav-tabs .nav-link {
                color: #adb5bd;
            }
            .menu-tabs-container .nav-tabs .nav-link:hover {
                color: var(--primary-color);
            }
            .menu-tabs-container .nav-tabs .nav-link.active {
                color: var(--primary-color);
                border-bottom: 2px solid var(--primary-color);
                background-color: transparent;
            }
            .menu-item-card {
                transition: var(--transition);
            }
            .menu-item-card:hover {
                transform: translateY(-8px);
                box-shadow: var(--box-shadow) !important;
            }
            .menu-item-card .card-img-top {
                height: 200px;
                object-fit: cover;
            }
        </style>
    </div>
@endsection