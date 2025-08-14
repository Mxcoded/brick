:@extends('restaurant::layouts.master')
@section('title', 'Menu')

@section('content')
    <div class="container-fluid content py-4">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold text-dark">Menu - Table {{ $table }}</h1>
            <p class="lead text-muted">Explore our delicious offerings and add your favorites to the cart.</p>
        </div>

        <div x-data="{ activeTab: 'all' }">
            <nav class="flex mt-3 mb-3 nav-pills flex-column flex-sm-row menu-tab" x-data="{ categories: {{ json_encode($category_names) }} } ">
                <ul class="justify-content-center nav " style="">

                <li role="button" class="nav-item">
                    <a class="nav-link" @click="activeTab = 'all'" :class="{ 'active': activeTab === 'all', 'inactive-class': activeTab !== 'all' }">All</a>
                </li>

                <template x-for="category in categories">
                    <li role="button" class="nav-item">
                        <a class="nav-link" @click="activeTab = category.toLowerCase()" :class="{ 'active': activeTab === category.toLowerCase(), 'inactive-class': activeTab !== category.toLowerCase() }" x-text="category"></a>
                    </li>

                </template>
                </ul>
            </nav>
            


            <div x-show="activeTab === 'all'" class="justify-content-center">
                @if ($categories->isEmpty())
                    <div class="alert alert-info text-center rounded-3 shadow-sm">
                        <i class="bi bi-info-circle me-2"></i>No menu items available at the moment.
                    </div>
                @else
                    <div x-data="{ categories: {{ json_encode($categories) }} }" class="row ">
                        <template x-for="category in categories">
                                <template x-for="item in category.menu_items">
                                    <div class="col-md-6 offset-md-3 mb-3 mt-3">
                                        <div class="h-100 shadow-lg border-0 rounded-3 p-3">
                                            <div class="d-flex justify-content-between">
                                                <div class="d-flex">
                                                        <div class="card-img-top text-center p-3" style="height: 200px; overflow: hidden;">
                                                        <img src="https://via.placeholder.com/150x150?text=No+Image" alt="No Image" class="img-fluid rounded" style="max-height: 100%; object-fit: cover;">
                                                    </div>
                                                    <div>
                                                        <h5 class="card-title fw-bold" x-text="item.name"></h5>
                                                        <p class="text-muted" x-text="item.description"></p>
                                                        <p class="fw-bold text-primary" x-text="item.price"></p>
                                                    </div>
                                                </div>
                                                <div>
                                                    <form @submit.prevent="$store.cart.add(item_id, instructions, price, name)" method="POST" x-data="{ item_id: item.id, instructions: {}, price: item.price, name: item.name }">
                                                        @csrf
                                                        <input type="hidden" name="item_id" x-model="item_id">
                                                        <input type="hidden" name="price" x-model="price">
                                                        <input type="hidden" name="name" x-model="name">

                                                        <div class="mb-3">
                                                            <label :for="'instructions-' + item.id" class="form-label">Special Instructions</label>
                                                            <textarea name="instructions" x-model=instructions[item.id] :id=instructions[item.id] class="form-control" rows="2"></textarea>
                                                        </div>
                                                        <input type="hidden" name="instructions" :value="instructions[item_id]">
                                                <button type="submit" class="btn w-100" style="background-color: #e4716e82; color: red">Add +</button>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                        </template>
                    </div>
                @endif
                
            </div>
            <div x-data="{ categories: {{ json_encode($categories) }} }" class="justify-content-center">
                <template x-for="category in categories">
                    <div x-show="activeTab === category.name.toLowerCase()" class="category-item align-items-center">
                        <template x-for="item in category.menu_items">
                                    <div class="col-md-6 offset-md-3 mb-3 mt-3">
                                        <div class="h-100 shadow-lg border-0 rounded-3 p-3">
                                    <div class="d-flex justify-content-between">
                                        <div class="d-flex">
                                                <div class="card-img-top text-center p-3" style="height: 200px; overflow: hidden;">
                                                <img src="https://via.placeholder.com/150x150?text=No+Image" alt="No Image" class="img-fluid rounded" style="max-height: 100%; object-fit: cover;">
                                            </div>
                                            <div>
                                                <h5 class="card-title fw-bold" x-text="item.name"></h5>
                                                <p class="text-muted" x-text="item.description"></p>
                                                <p class="fw-bold text-primary" x-text="item.price"></p>
                                            </div>
                                        </div>
                                        <div>
                                            <form @submit.prevent="$store.cart.add(item_id, instructions, price, name)" method="POST" x-data="{ item_id: item.id, instructions: {}, price: item.price, name: item.name }">
                                                @csrf
                                                <input type="hidden" name="item_id" x-model="item_id">
                                                <input type="hidden" name="price" x-model="price">
                                                <input type="hidden" name="name" x-model="name">

                                                <div class="mb-3">
                                                    <label :for="'instructions-' + item.id" class="form-label text-gray-600">Special Instructions</label>
                                                    <textarea name="instructions" x-model=instructions[item.id] :id=instructions[item.id] class="form-control" rows="2"></textarea>
                                                </div>
                                                <input type="hidden" name="instructions" :value="instructions[item_id]">
                                                <button type="submit"  class="btn w-100" style="background-color: #e4716e82; color: red">Add +</button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </template>
            </div>
            <div class="container-fluid">
                <div class="modal fade" id="cartModal" tabindex="-1" aria-labelledby="cartModalLabel" aria-hidden="true">
                    <form class="modal-dialog" x-on:submit.prevent="$store.cart.sendData()" method="POST" action="{{ route('restaurant.cart.order', $table) }}">
                    @csrf
                        <div class="modal-content">
                        <div class="modal-header">
                            <h1 class="modal-title fs-5" id="cartModalLabel">Cart Order</h1>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body" x-data="{order: JSON.stringify($store.cart.items) }">
                            <div  class="mb-3">
                                <template x-for="(item, index) in $store.cart.items">
                                    <div class="card p-3 mb-2 position-relative" :id="'cart-' + item.item_id" style="width: 100%;">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <div>
                                                <h5 x-text="item.name"></h5>
                                                <p x-text="'Price: ' + item.price"></p>
                                                <p x-text="'Instructions: ' + item.instructions[item.item_id]"></p>
                                                <p x-text="'Price: ' + item.price"></p>
                                            </div>
                                            <div class="d-flex" style="height: 40px" x-data="{quantity: item.quantity}">
                                                <button type="button" @click="$store.cart.minusQuantity(item.item_id)" class="btn w-100" style="background-color: #e4716e82; color: red">-</button>
                                                <span x-text="$store.cart.items[index].quantity" style="width: 40px; text-align: center; margin-right: 10px; margin-left: 10px; padding: 5px" ></span>
                                                <button type="button" @click="$store.cart.addQuantity(item.item_id)" class="btn w-100" style="background-color: #e4716e82; color: red">+</button>
                                            </div>
                                        </div>
                                        <button type="button" @click="$store.cart.remove(item.item_id)" class="btn-close position-absolute start-60" style="width: 20px; right: 10px"></button>
                                    </div>
                                </template>
                            </div>
                            <input type="hidden" name="order" id="order" x-model="order">
                            <p>Total Items: <span x-text="$store.cart.items.length"></span></p>
                            <p>Total Price: <span id="total" x-text="$store.cart.items.reduce((total, item) => total + parseFloat(item.price), 0).toFixed(2)"></span></p>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary" >Proceed</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

     
        <style>
            body {
                font-family: 'Figtree', sans-serif;
                color: red;
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
                border-top-left-radius: 0.75rem;
                border-top-right-radius: 0.75rem;
            }
            .btn-primary {
                background-color: #d9534f;
                border-color: #d9534f;
            }
            .btn-primary:hover {
                background-color: #c9302c;
                border-color: #c9302c;
            }
            .form-control, .form-select, textarea {
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
        </style>

        <!-- Include Bootstrap Icons for alerts -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    </div>
@endsection

@push('scripts')
    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.store('cart', {
            items: [],
            add(item_id, instruction, price, name) {
                if(this.items.some(item => item.item_id === item_id )) {
                    alert('Item already exists in the cart with the same instructions.');
                    return;
                }
                this.items.push({
                    item_id: item_id,
                    instructions: instruction,
                    price: price,
                    total: price,
                    name: name,
                    quantity: 1,
                });
                document.getElementById('cart-number').style.display = 'block'; 
                document.getElementById('cart-number').innerText = this.items.length;
                
            },
            addQuantity(item_id) {
                this.items.some(item => {
                    if(item.item_id === item_id){
                        item.quantity++;
                        item.total = parseFloat(item.total) + parseFloat(item.price);
                    }
                    
                });
                const total = this.items.reduce((total, item) => total + parseFloat(item.total), 0).toFixed(2);
                document.getElementById('total').innerText = total;
            },
            minusQuantity(item_id) {
                this.items.some(item => {
                    if(item.item_id === item_id){
                        item.quantity--;
                        item.total = parseFloat(item.total) - parseFloat(item.price);
                    }
                });
                const total = this.items.reduce((total, item) => total + parseFloat(item.total), 0).toFixed(2);
                document.getElementById('total').innerText = total;
            },
            remove(item_id){
                const newArray = this.items.filter(item => item.item_id != item_id);
                this.items = newArray;
                const total = this.items.reduce((total, item) => total + parseFloat(item.total), 0).toFixed(2);
                const childElement = document.getElementById(`cart-` + item_id);
                if (childElement && childElement.parentNode) {
                    childElement.parentNode.removeChild(childElement);
                }
                console.log(newArray);
                console.log(this.items);
                document.getElementById('total').innerText = total;
                document.getElementById('cart-number').innerText = this.items.length;
                document.getElementById('cart-number').style.display = this.items.length > 0 ? 'block' : 'none';
                
            },
            sendData() {
                fetch("{{ route('restaurant.cart.order', $table) }}", {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}' // Include CSRF token for security
                        },
                        body: JSON.stringify({order: this.items}) // Send the store data as JSON
                    })
                    .then(response => response.json())
                    .then(data => {
                        console.log('Success:', data);
                        var myLaravelRouteUrl = "{{ route('restaurant.cart', $table) }}";
                        window.location.href = myLaravelRouteUrl;
                        // Handle success response from Laravel
                    })
                    .catch((error) => {
                        console.log('Error:', error);
                        // Handle error
                });
            }
        });
    });
</script>
@endpush


