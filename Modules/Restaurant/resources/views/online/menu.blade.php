@extends('restaurant::layouts.master')
@section('title', 'Online Menu')
@section('content')
    <div class="container-fluid content py-4">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold text-dark">Online Menu</h1>
            <p class="lead text-muted">Explore our delicious offerings and order for delivery.</p>
        </div>

        @if ($categories->isEmpty())
            <div class="alert alert-info text-center rounded-3 shadow-sm">
                <i class="bi bi-info-circle me-2"></i>No menu items available at the moment.
            </div>
        @else
            @foreach ($categories as $category)
                <div class="mb-5">
                    <h2 class="fw-bold text-dark">{{ $category->name }}</h2>
                    <div class="row g-4">
                        @foreach ($category->menuItems as $item)
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 shadow-lg border-0 rounded-3">
                                    <div class="card-img-top text-center p-3" style="height: 200px; overflow: hidden;">
                                        @if ($item->image)
                                            <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}" class="img-fluid rounded" style="max-height: 100%; object-fit: cover;">
                                        @else
                                            <img src="https://via.placeholder.com/150x150?text=No+Image" alt="No Image" class="img-fluid rounded" style="max-height: 100%; object-fit: cover;">
                                        @endif
                                    </div>
                                    <div class="card-body">
                                        <h5 class="card-title fw-bold">{{ $item->name }}</h5>
                                        <p class="text-muted">{{ $item->description ?: 'No description available' }}</p>
                                        <p class="fw-bold text-primary">₦ {{ number_format($item->price, 2) }}</p>
                                        <form action="{{ route('restaurant.online.cart.add') }}" method="POST">
                                            @csrf
                                            <input type="hidden" name="item_id" value="{{ $item->id }}">
                                            <div class="mb-3">
                                                <label for="quantity-{{ $item->id }}" class="form-label">Quantity</label>
                                                <input type="number" name="quantity" id="quantity-{{ $item->id }}" class="form-control" value="1" min="1" required>
                                            </div>
                                            <div class="mb-3">
                                                <label for="instructions-{{ $item->id }}" class="form-label">Special Instructions</label>
                                                <textarea name="instructions" id="instructions-{{ $item->id }}" class="form-control" rows="2"></textarea>
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100">Add to Cart</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        @endif

        <div class="text-center mt-4">
            <a href="{{ route('restaurant.online.cart') }}" class="btn btn-outline-primary btn-lg">View Cart</a>
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
        </style>

        <!-- Include Bootstrap Icons for alerts -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    </div>
@endsection