@extends('restaurant::layouts.master')
@section('title', 'Menu')

@section('content')
    <div class="container-fluid content py-4" x-data="{ activeTab: 'all', categories: {{ json_encode($categories) }}, categoryNames: {{ json_encode($category_names) }} }">
        @if (session('error'))
            <div class="alert alert-danger text-center rounded-3 shadow-sm">
                <i class="bi bi-exclamation-circle me-2"></i>{{ session('error') }}
            </div>
        @endif
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold text-dark">
                @if ($type === 'table')
                    Menu - Table {{ $sourceModel->number }}
                @elseif ($type === 'room')
                    Menu - Room {{ $sourceModel->name }}
                @else
                    Online Menu
                @endif
            </h1>
            <p class="lead text-muted">
                @if ($type === 'online')
                    Explore our delicious offerings and order for delivery.
                @else
                    Explore our delicious offerings and add your favorites to the cart.
                @endif
            </p>
        </div>

        <!-- Navigation Tabs -->
        <nav class="flex mt-3 mb-3 nav-pills flex-column flex-sm-row menu-tab">
            <ul class="justify-content-center nav">
                <li role="button" class="nav-item">
                    <a class="nav-link" @click="activeTab = 'all'"
                        :class="{ 'active': activeTab === 'all', 'inactive-class': activeTab !== 'all' }">All</a>
                </li>
                <template x-for="category in categoryNames" :key="category">
                    <li role="button" class="nav-item">
                        <a class="nav-link" @click="activeTab = category.toLowerCase()"
                            :class="{
                                'active': activeTab === category.toLowerCase(),
                                'inactive-class': activeTab !== category
                                    .toLowerCase()
                            }"
                            x-text="category"></a>
                    </li>
                </template>
            </ul>
        </nav>

        <!-- Menu Items -->
        <div class="row g-4">
            <template x-if="categories.length === 0">
                <div class="col-12 text-center">
                    <div class="alert alert-info rounded-3 shadow-sm">
                        <i class="bi bi-info-circle me-2"></i>No menu items available at the moment.
                    </div>
                </div>
            </template>
            <template x-else x-for="category in categories" :key="category.name">
                <template x-for="item in category.menu_items" :key="item.id">
                    <div class="col-md-6 offset-md-3 mb-3 mt-3"
                        x-show="activeTab === 'all' || activeTab === category.name.toLowerCase()">
                        <div class="h-100 shadow-lg border-0 rounded-3 p-3">
                            <div class="d-flex justify-content-between">
                                <div class="d-flex">
                                    <div class="card-img-top text-center p-3" style="height: 200px; overflow: hidden;">
                                        <div class="image-wrapper" x-data="{ loaded: false }">
                                            <div class="shimmer" x-show="!loaded"></div>
                                            <img :src="item.image ?
                                                '{{ asset('storage') }}/' + item.image :
                                                '{{ asset('storage/images/menudefaultimage.png') }}'"
                                                :alt="item.name" class="img-fluid rounded fade-in"
                                                style="max-height: 100%; object-fit: cover;" loading="lazy"
                                                @load="loaded = true">
                                        </div>
                                    </div>
                                    <div>
                                        <h5 class="card-title fw-bold" x-text="item.name"></h5>
                                        <p class="text-muted" x-text="item.description || 'No description available'"></p>
                                        <p class="fw-bold text-primary"
                                            x-text="`₦${Number(item.price).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`">
                                        </p>
                                    </div>
                                </div>
                                <div>
                                    <form
                                        @submit.prevent="$store.cart.add(item.id, instructions[item.id] || '', item.price, item.name)"
                                        method="POST" x-data="{ item_id: item.id, instructions: {}, price: item.price, name: item.name }">
                                        @csrf
                                        <input type="hidden" name="item_id" x-model="item_id">
                                        <input type="hidden" name="price" x-model="price">
                                        <input type="hidden" name="name" x-model="name">
                                        <div class="mb-3">
                                            <label :for="'instructions-' + item.id" class="form-label">Special
                                                Instructions</label>
                                            <textarea name="instructions" x-model="instructions[item.id]" :id="'instructions-' + item.id" class="form-control"
                                                rows="2"></textarea>
                                        </div>
                                        <button type="submit" class="btn w-100"
                                            style="background-color: #e4716e82; color: red">Add +</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </template>
            </template>
        </div>

        <!-- Cart Modal -->
        <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
            <form class="modal-dialog" @submit.prevent="$store.cart.sendData()" method="POST"
                action="{{ route($type === 'online' ? 'restaurant.online.cart.add' : 'restaurant.cart.add', $type === 'online' ? [] : [$type, $sourceModel->id]) }}">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h1 class="modal-title fs-5" id="cartModalLabel">Cart Order</h1>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <template x-if="$store.cart.items.length === 0">
                            <div class="alert alert-info text-center">Your cart is empty.</div>
                        </template>
                        <template x-for="(item, index) in $store.cart.items" :key="item.item_id">
                            <div class="card p-3 mb-2 position-relative" :id="'cart-' + item.item_id" style="width: 100%;">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <h5 x-text="item.name"></h5>
                                        <p
                                            x-text="`Price: ₦${Number(item.price).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`">
                                        </p>
                                        <p x-text="`Instructions: ${item.instructions || 'None'}`"></p>
                                        <p
                                            x-text="`Total: ₦${Number(item.total).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`">
                                        </p>
                                    </div>
                                    <div class="d-flex align-items-center" style="height: 40px">
                                        <button type="button" @click="$store.cart.minusQuantity(item.item_id)"
                                            class="btn" style="background-color: #e4716e82; color: red">-</button>
                                        <span x-text="item.quantity"
                                            style="width: 40px; text-align: center; margin: 0 10px; padding: 5px"></span>
                                        <button type="button" @click="$store.cart.addQuantity(item.item_id)"
                                            class="btn" style="background-color: #e4716e82; color: red">+</button>
                                    </div>
                                </div>
                                <button type="button" @click="$store.cart.remove(item.item_id)"
                                    class="btn-close position-absolute" style="top: 10px; right: 10px"></button>
                            </div>
                        </template>
                        <input type="hidden" name="order" x-model="JSON.stringify($store.cart.items)">
                        <p>Total Items: <span x-text="$store.cart.items.length"></span></p>
                        <p>
                            Total Price:<span
                                x-text="`₦${$store.cart.items.reduce((total, item) => total + Number(item.total), 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`"></span>
                        </p>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Proceed</button>
                    </div>
                </div>
            </form>
        </div>

        <!-- Cart Number Indicator -->
        <div id="cart-number" class="position-fixed bottom-0 end-0 m-3 p-2 bg-danger text-white rounded-circle"
            style="display: none; width: 30px; height: 30px; text-align: center;" x-text="$store.cart.items.length"></div>

        <!-- Styles -->
        <style>
            body {
                font-family: 'Futura', 'Arial', sans-serif;
            }

            .card {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                border-radius: 0.75rem;
            }

            .card:hover {
                transform: translateY(-5px);
                box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
            }

            .card-img-top {
                background-color: #f8f9fa;
                border-radius: 0.75rem;
            }

            .btn-primary {
                background-color: #d9534f;
                border-color: #d9534f;
            }

            .btn-primary:hover {
                background-color: #c9302c;
                border-color: #c9302c;
            }

            .form-control,
            .form-select,
            textarea {
                border-radius: 0.5rem;
            }

            .alert {
                padding: 1.25rem;
                border-radius: 0.75rem;
            }

            .menu-tab .nav-link.active {
                background-color: #e4716e82 !important;
                color: red !important;
            }

            .nav-link {
                color: #696969 !important;
            }

            .image-wrapper {
                position: relative;
                width: 100%;
                height: 100%;
            }

            .shimmer {
                position: absolute;
                top: 0;
                left: 0;
                width: 100%;
                height: 100%;
                background: linear-gradient(90deg, #f0f0f0 25%, #e0e0e0 50%, #f0f0f0 75%);
                background-size: 200% 100%;
                animation: shimmer 1.5s infinite;
                border-radius: 0.75rem;
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
                opacity: 0;
                transition: opacity 0.5s ease-in-out;
            }

            .fade-in[x-cloak],
            .fade-in[style*="display: none"] {
                opacity: 0 !important;
            }

            .fade-in:not([style*="display: none"]) {
                opacity: 1 !important;
            }
        </style>

        <!-- Bootstrap Icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('alpine:init', () => {
            Alpine.store('cart', {
                items: [],
                add(item_id, instructions, price, name) {
                    if (this.items.some(item => item.item_id === item_id && item.instructions ===
                            instructions)) {
                        alert('Item already exists in the cart with the same instructions.');
                        return;
                    }
                    this.items.push({
                        item_id,
                        instructions: instructions || '',
                        price: Number(price),
                        total: Number(price),
                        name,
                        quantity: 1,
                    });
                    const cartNumber = document.getElementById('cart-number');
                    cartNumber.style.display = 'block';
                    cartNumber.innerText = this.items.length;
                },
                addQuantity(item_id) {
                    const item = this.items.find(item => item.item_id === item_id);
                    if (item) {
                        item.quantity++;
                        item.total = Number(item.price) * item.quantity;
                    }
                    this.updateCartNumber();
                },
                minusQuantity(item_id) {
                    const item = this.items.find(item => item.item_id === item_id);
                    if (item && item.quantity > 1) {
                        item.quantity--;
                        item.total = Number(item.price) * item.quantity;
                    } else if (item && item.quantity === 1) {
                        this.remove(item_id);
                    }
                    this.updateCartNumber();
                },
                remove(item_id) {
                    this.items = this.items.filter(item => item.item_id !== item_id);
                    const childElement = document.getElementById(`cart-${item_id}`);
                    if (childElement && childElement.parentNode) {
                        childElement.parentNode.removeChild(childElement);
                    }
                    this.updateCartNumber();
                },
                updateCartNumber() {
                    const cartNumber = document.getElementById('cart-number');
                    cartNumber.innerText = this.items.length;
                    cartNumber.style.display = this.items.length > 0 ? 'block' : 'none';
                },
                sendData() {
                    fetch("{{ route($type === 'online' ? 'restaurant.online.order.add' : 'restaurant.order.add', $type === 'online' ? [] : [$type, $sourceModel->id]) }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}'
                            },
                            body: JSON.stringify({
                                order: this.items
                            })
                        })
                        .then(response => {
                            if (!response.ok) throw new Error('Network response was not ok');
                            return response.json();
                        })
                        .then(data => {
                            window.location.href =
                                "{{ route($type === 'online' ? 'restaurant.online.cart' : 'restaurant.cart', $type === 'online' ? [] : [$type, $sourceModel->id]) }}";
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('An error occurred while submitting the order. Please try again.');
                        });
                }
            });
        });
    </script>
@endpush
