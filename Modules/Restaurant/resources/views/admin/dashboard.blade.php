@extends('restaurant::layouts.master')
@section('title', 'Admin Dashboard')
@section('content')
    <div class="container-fluid">
        <div class="text-center mb-5">
            <h1 class="display-4 fw-bold text-dark">Admin Dashboard</h1>
            <p class="lead text-muted">Manage menu categories, items, and orders.</p>
        </div>

        <!-- Responsive Side-by-Side Form Section -->
        <div class="row mb-5 g-4">
            <!-- Add Category Form -->
            <div class="col-lg-6 col-12">
                <div class="card shadow-lg border-0 rounded-4 h-100">
                    <div class="card-header bg-light border-bottom-0">
                        <h3 class="card-title fw-bold">Add New Category</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('restaurant.admin.add-category') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label">Category Name</label>
                                <input type="text" name="name" id="name"
                                    class="form-control @error('name') is-invalid @enderror" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="parent_category" class="form-label">Parent Category (Optional)</label>
                                <select name="parent_category" id="parent_category" class="form-select">
                                    <option value="">None</option>
                                    @foreach ($parent_categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <label for="sub_category" class="form-label">Sub Category (Optional)</label>
                                <select name="sub_category" id="sub_category" class="form-select">
                                    <option value="">None</option>
                                    @foreach ($categories as $category)
                                        @if ($category->parent_id)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endif
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Add Category</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Add Menu Item Form -->
            <div class="col-lg-6 col-12">
                <div class="card shadow-lg border-0 rounded-4 h-100">
                    <div class="card-header bg-light border-bottom-0">
                        <h3 class="card-title fw-bold">Add New Menu Item</h3>
                    </div>
                    <div class="card-body">
                        <form action="{{ route('restaurant.admin.add-item') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label for="restaurant_menu_categories_id" class="form-label">Category</label>
                                <select name="restaurant_menu_categories_id" id="restaurant_menu_categories_id"
                                    class="form-select @error('restaurant_menu_categories_id') is-invalid @enderror"
                                    required>
                                    <option value="">Select Category</option>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                                    @endforeach
                                </select>
                                @error('restaurant_menu_categories_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="name" class="form-label">Item Name</label>
                                <input type="text" name="name" id="name"
                                    class="form-control @error('name') is-invalid @enderror" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <textarea name="description" id="description" class="form-control @error('description') is-invalid @enderror"
                                    rows="4"></textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="price" class="form-label">Price (₦)</label>
                                <input type="number" name="price" id="price"
                                    class="form-control @error('price') is-invalid @enderror" step="0.01" min="0"
                                    required>
                                @error('price')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="image" class="form-label">Dish Image (Optional)</label>
                                <input type="file" name="image" id="image"
                                    class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                @error('image')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-100">Add Menu Item</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Section -->
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="fw-bold text-dark mb-4">Manage Orders</h2>
                @if ($orders->isEmpty())
                    <div class="alert alert-info text-center rounded-3 shadow-sm">
                        <i class="bi bi-info-circle me-2"></i>No orders available.
                    </div>
                @else
                    <div class="row g-4">
                        @foreach ($orders as $order)
                            <div class="col-md-6 col-lg-4">
                                <div class="card h-100 shadow-lg border-0 rounded-3">
                                    <div class="card-header bg-light border-bottom-0">
                                        <h3 class="card-title fw-bold mb-0">
                                            Order #{{ $order->id }} - {{ ucfirst($order->type) }}
                                            <span class="float-end">
                                                <span
                                                    class="badge bg-success rounded-pill">{{ ucfirst($order->status) }}</span>
                                            </span>
                                        </h3>
                                        <small class="text-muted">Placed:
                                            {{ $order->created_at->format('d M Y H:i') }}</small>
                                    </div>
                                    <div class="card-body">
                                        <p>
                                            <strong>
                                                {{ $order->type === 'table' ? 'Table: ' . ($order->table->number ?? 'N/A') : 'Customer: ' . ($order->customer_name ?? 'N/A') }}
                                            </strong>
                                        </p>
                                        @if ($order->type === 'online')
                                            <p><strong>Phone:</strong> {{ $order->customer_phone ?? 'N/A' }}</p>
                                            <p><strong>Address:</strong> {{ $order->delivery_address ?? 'N/A' }}</p>
                                            <p><strong>Tracking:</strong>
                                                {{ ucfirst($order->tracking_status ?? 'Pending') }}</p>
                                        @endif
                                        <button class="btn btn-link text-decoration-none p-0 mb-2" type="button"
                                            data-bs-toggle="collapse" data-bs-target="#orderDetails{{ $order->id }}"
                                            aria-expanded="false" aria-controls="orderDetails{{ $order->id }}">
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
                                                                @if ($item->menuItem && $item->menuItem->image)
                                                                    <img src="{{ asset('storage/' . $item->menuItem->image) }}"
                                                                        alt="{{ $item->menuItem->name }}"
                                                                        class="img-fluid rounded"
                                                                        style="max-width: 50px; max-height: 50px; object-fit: cover;">
                                                                @else
                                                                    <img src="https://via.placeholder.com/50x50?text=No+Image"
                                                                        alt="No Image" class="img-fluid rounded"
                                                                        style="max-width: 50px; max-height: 50px; object-fit: cover;">
                                                                @endif
                                                            </td>
                                                            <td>
                                                                {{ $item->menuItem ? $item->menuItem->name : 'Item not found' }}
                                                                @if ($item->instructions)
                                                                    <small
                                                                        class="text-muted d-block">{{ $item->instructions }}</small>
                                                                @endif
                                                            </td>
                                                            <td>{{ $item->quantity }}</td>
                                                            <td>{{ $item->menuItem ? '₦' . number_format($item->menuItem->price, 2) : 'N/A' }}
                                                            </td>
                                                            <td>{{ $item->menuItem ? '₦' . number_format($item->menuItem->price * $item->quantity, 2) : 'N/A' }}
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                                <tfoot>
                                                    <tr>
                                                        <td colspan="4" class="text-end fw-bold">Total:</td>
                                                        <td>{{ '₦' .
                                                            number_format(
                                                                $order->orderItems->sum(function ($item) {
                                                                    return $item->menuItem ? $item->menuItem->price * $item->quantity : 0;
                                                                }),
                                                                2,
                                                            ) }}
                                                        </td>
                                                    </tr>
                                                </tfoot>
                                            </table>
                                        </div>
                                        <form action="{{ route('restaurant.admin.order.update', $order->id) }}"
                                            method="POST">
                                            @csrf
                                            <div class="mb-3">
                                                <label for="status-{{ $order->id }}" class="form-label">Status</label>
                                                <select name="status" id="status-{{ $order->id }}"
                                                    class="form-select">
                                                    <option value="pending"
                                                        {{ $order->status === 'pending' ? 'selected' : '' }}>Pending
                                                    </option>
                                                    <option value="accepted"
                                                        {{ $order->status === 'accepted' ? 'selected' : '' }}>Accepted
                                                    </option>
                                                    <option value="completed"
                                                        {{ $order->status === 'completed' ? 'selected' : '' }}>Completed
                                                    </option>
                                                </select>
                                            </div>
                                            @if ($order->type === 'online')
                                                <div class="mb-3">
                                                    <label for="tracking_status-{{ $order->id }}"
                                                        class="form-label">Tracking Status</label>
                                                    <select name="tracking_status"
                                                        id="tracking_status-{{ $order->id }}" class="form-select">
                                                        <option value="pending"
                                                            {{ $order->tracking_status === 'pending' ? 'selected' : '' }}>
                                                            Pending</option>
                                                        <option value="preparing"
                                                            {{ $order->tracking_status === 'preparing' ? 'selected' : '' }}>
                                                            Preparing</option>
                                                        <option value="delivered"
                                                            {{ $order->tracking_status === 'delivered' ? 'selected' : '' }}>
                                                            Delivered</option>
                                                    </select>
                                                </div>
                                            @endif
                                            <button type="submit" class="btn btn-primary w-100">Update Order</button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Display Existing Categories -->
        <div class="row g-4">
            @foreach ($categories as $category)
                <div class="col-md-4">
                    <div class="card h-100 shadow-sm border-0 rounded-3">
                        <div class="card-body">
                            <h5 class="card-title fw-bold">{{ $category->name }}</h5>
                            <p class="text-muted">
                                @if ($category->parent_id)
                                    Subcategory of {{ $categories->find($category->parent_id)->name ?? 'Unknown' }}
                                @else
                                    Parent Category
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <style>
            /* Smooth inputs & card effects */
            .card {
                transition: box-shadow 0.3s ease;
            }

            .card:hover {
                box-shadow: 0 0 25px rgba(0, 0, 0, 0.1);
            }

            .form-control:focus,
            .form-select:focus {
                border-color: #007bff;
                box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
                transition: all 0.2s ease-in-out;
            }

            /* Button styling */
            .btn-primary {
                background-color: #007bff;
                border: none;
                transition: background-color 0.2s ease-in-out;
            }

            .btn-primary:hover {
                background-color: #0056b3;
            }

            /* Typography & Layout polish */
            .card-title {
                font-size: 1.5rem;
                color: #333;
            }

            label {
                font-weight: 500;
                color: #444;
            }

            .card-header {
                background-color: #f8f9fa;
                padding: 1.25rem;
                border-radius: 0.75rem 0.75rem 0 0;
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
            .form-select {
                border-radius: 0.5rem;
            }

            .alert {
                padding: 1.25rem;
                border-radius: 0.75rem;
            }

            .table img {
                border-radius: 0.5rem;
            }

            .btn-link {
                color: #d9534f;
            }

            .btn-link:hover {
                color: #c9302c;
                text-decoration: underline;
            }

            .table-sm {
                font-size: 0.85rem;
            }

            .table-borderless th,
            .table-borderless td {
                padding: 0.5rem;
            }

            .badge {
                font-size: 0.9rem;
                padding: 0.5em 0.75em;
            }
        </style>

        <!-- Include Bootstrap Icons for alerts and chevrons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    </div>
@endsection
