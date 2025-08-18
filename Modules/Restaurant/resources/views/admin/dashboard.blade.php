@extends('restaurant::layouts.master')
@section('title', 'Admin Dashboard')
@section('content')
    <div class="container-fluid">
        <div class="text-center mb-5 mt-4">
            <h1 class="display-4 fw-bold text-dark">Admin Dashboard</h1>
            <p class="lead text-muted">Manage menu categories, items, and orders</p>
        </div>

        <!-- Responsive Side-by-Side Form Section -->
        <div class="row mb-5 g-4">
            <!-- Add Category Form -->
            <div class="col-lg-6 col-12">
                <div class="card shadow-lg border-0 rounded-4 h-100">
                    <div class="card-header bg-light py-3">
                        <h3 class="card-title fw-bold mb-0">Add New Category</h3>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('restaurant.admin.add-category') }}" method="POST">
                            @csrf
                            <div class="mb-4">
                                <label for="name" class="form-label fw-medium">Category Name</label>
                                <input type="text" name="name" id="name"
                                    class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                    placeholder="Enter category name" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6 mb-4">
                                    <label for="parent_category" class="form-label fw-medium">Parent Category</label>
                                    <select name="parent_category" id="parent_category" class="form-select form-select-lg">
                                        <option value="">None</option>
                                        @foreach ($parent_categories as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label for="sub_category" class="form-label fw-medium">Sub Category</label>
                                    <select name="sub_category" id="sub_category" class="form-select form-select-lg" disabled>
                                        <option value="">Select parent first</option>
                                    </select>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 py-2 fw-bold">
                                <i class="bi bi-plus-circle me-2"></i>Add Category
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Add Menu Item Form -->
            <div class="col-lg-6 col-12">
                <div class="card shadow-lg border-0 rounded-4 h-100">
                    <div class="card-header bg-light py-3">
                        <h3 class="card-title fw-bold mb-0">Add New Menu Item</h3>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('restaurant.admin.add-item') }}" method="POST"
                            enctype="multipart/form-data">
                            @csrf
                            <div class="mb-4">
                                <label for="restaurant_menu_categories_id" class="form-label fw-medium">Category</label>
                                <select name="restaurant_menu_categories_id" id="restaurant_menu_categories_id"
                                    class="form-select form-select-lg @error('restaurant_menu_categories_id') is-invalid @enderror"
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

                            <div class="mb-4">
                                <label for="name" class="form-label fw-medium">Item Name</label>
                                <input type="text" name="name" id="name"
                                    class="form-control form-control-lg @error('name') is-invalid @enderror" 
                                    placeholder="Enter item name" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <label for="description" class="form-label fw-medium">Description</label>
                                <textarea name="description" id="description" 
                                    class="form-control form-control-lg @error('description') is-invalid @enderror"
                                    rows="3" placeholder="Enter item description"></textarea>
                                @error('description')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6 mb-4">
                                    <label for="price" class="form-label fw-medium">Price (₦)</label>
                                    <input type="number" name="price" id="price" step="0.01"
                                        class="form-control form-control-lg @error('price') is-invalid @enderror" 
                                        placeholder="0.00" required min="0">
                                    @error('price')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>

                                <div class="col-md-6 mb-4">
                                    <label for="image" class="form-label fw-medium">Image</label>
                                    <input type="file" name="image" id="image"
                                        class="form-control form-control-lg @error('image') is-invalid @enderror">
                                    @error('image')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-lg w-100 py-2 fw-bold">
                                <i class="bi bi-plus-circle me-2"></i>Add Item
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Orders Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold mb-0">Manage Orders</h2>
                    <div class="d-flex">
                        <button class="btn btn-outline-primary me-2">
                            <i class="bi bi-filter me-1"></i>Filter
                        </button>
                        <button class="btn btn-primary">
                            <i class="bi bi-arrow-clockwise me-1"></i>Refresh
                        </button>
                    </div>
                </div>
                
                @if ($orders->isEmpty())
                    <div class="alert alert-info text-center rounded-3 shadow-sm py-4">
                        <i class="bi bi-info-circle-fill me-2"></i>No orders available
                    </div>
                @else
                    <div class="row g-4">
                        @foreach ($orders as $order)
                            <div class="col-md-6 col-lg-4 col-xl-3">
                                <div class="card h-100 shadow-sm border-0 rounded-3">
                                    <div class="card-header bg-light py-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h3 class="h5 fw-bold mb-0">
                                                #{{ $order->id }}
                                            </h3>
                                            <span class="badge rounded-pill 
                                                @if($order->status === 'pending') bg-warning text-dark
                                                @elseif($order->status === 'accepted') bg-info
                                                @else bg-success @endif">
                                                {{ ucfirst($order->status) }}
                                            </span>
                                        </div>
                                        <small class="text-muted d-block mt-1">
                                            <i class="bi bi-clock me-1"></i>{{ $order->created_at->format('d M Y, H:i') }}
                                        </small>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <p class="mb-1"><i class="bi bi-tag me-2"></i><strong>Type:</strong> 
                                                <span class="text-capitalize">{{ $order->type }}</span>
                                            </p>
                                            @if ($order->type === 'table')
                                                <p class="mb-1"><i class="bi bi-table me-2"></i><strong>Table:</strong> 
                                                    {{ $order->table->number ?? 'N/A' }}
                                                </p>
                                            @elseif ($order->type === 'room')
                                                <p class="mb-1"><i class="bi bi-door-closed me-2"></i><strong>Room:</strong> 
                                                    {{ $order->room->name ?? 'N/A' }}
                                                </p>
                                            @elseif ($order->type === 'online')
                                                <p class="mb-1"><i class="bi bi-person me-2"></i><strong>Customer:</strong> 
                                                    {{ $order->customer_name }}
                                                </p>
                                                <p class="mb-1"><i class="bi bi-telephone me-2"></i><strong>Phone:</strong> 
                                                    {{ $order->customer_phone }}
                                                </p>
                                                <p class="mb-1"><i class="bi bi-geo-alt me-2"></i><strong>Address:</strong> 
                                                    {{ $order->delivery_address }}
                                                </p>
                                            @endif
                                            <p class="mb-0"><i class="bi bi-truck me-2"></i><strong>Tracking:</strong> 
                                                <span class="badge bg-secondary text-capitalize">
                                                    {{ $order->tracking_status ?? 'pending' }}
                                                </span>
                                            </p>
                                        </div>

                                        <div class="d-grid mb-3">
                                            <button class="btn btn-outline-secondary d-flex align-items-center justify-content-center" 
                                                    type="button" 
                                                    data-bs-toggle="collapse" 
                                                    data-bs-target="#orderDetails{{ $order->id }}">
                                                <span>View Order Details</span>
                                                <i class="bi bi-chevron-down ms-2"></i>
                                            </button>
                                        </div>
                                        
                                        <div class="collapse" id="orderDetails{{ $order->id }}">
                                            <div class="table-responsive">
                                                <table class="table table-sm table-borderless">
                                                    <thead class="bg-light">
                                                        <tr>
                                                            <th>Item</th>
                                                            <th class="text-end">Qty</th>
                                                            <th class="text-end">Price</th>
                                                            <th class="text-end">Total</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($order->orderItems as $item)
                                                            <tr>
                                                                <td class="align-middle">
                                                                    <div class="d-flex align-items-center">
                                                                        <img src="{{ $item->menuItem && $item->menuItem->image && file_exists(public_path('storage/' . $item->menuItem->image)) ? asset('storage/' . $item->menuItem->image) : asset('storage/images/menudefaultimage.png') }}"
                                                                            alt="{{ $item->menuItem ? $item->menuItem->name : 'No Image' }}"
                                                                            class="img-fluid rounded me-2"
                                                                            style="width: 40px; height: 40px; object-fit: cover;">
                                                                        <div>
                                                                            <div class="fw-medium">{{ $item->menuItem ? $item->menuItem->name : 'Item not found' }}</div>
                                                                            @if ($item->instructions)
                                                                                <small class="text-muted">{{ $item->instructions }}</small>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                                <td class="align-middle text-end">{{ $item->quantity }}</td>
                                                                <td class="align-middle text-end">₦{{ $item->menuItem ? number_format($item->menuItem->price, 2) : '0.00' }}</td>
                                                                <td class="align-middle text-end fw-medium">₦{{ $item->menuItem ? number_format($item->menuItem->price * $item->quantity, 2) : '0.00' }}</td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                    <tfoot class="border-top">
                                                        <tr>
                                                            <td colspan="3" class="text-end fw-bold">Total:</td>
                                                            <td class="text-end fw-bold">₦{{ number_format($order->orderItems->sum(function($item) {
                                                                return $item->menuItem ? $item->menuItem->price * $item->quantity : 0;
                                                            }), 2) }}</td>
                                                        </tr>
                                                    </tfoot>
                                                </table>
                                            </div>
                                        </div>
                                        
                                        <form action="{{ route('restaurant.admin.order.update', $order->id) }}" method="POST">
                                            @csrf
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <label for="status" class="form-label small fw-medium">Order Status</label>
                                                    <select name="status" id="status" class="form-select" required>
                                                        <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                        <option value="accepted" {{ $order->status === 'accepted' ? 'selected' : '' }}>Accepted</option>
                                                        <option value="completed" {{ $order->status === 'completed' ? 'selected' : '' }}>Completed</option>
                                                    </select>
                                                </div>
                                                @if ($order->type === 'online' || $order->type === 'room')
                                                    <div class="col-md-6">
                                                        <label for="tracking_status" class="form-label small fw-medium">Tracking</label>
                                                        <select name="tracking_status" id="tracking_status" class="form-select">
                                                            <option value="pending" {{ $order->tracking_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                            <option value="preparing" {{ $order->tracking_status === 'preparing' ? 'selected' : '' }}>Preparing</option>
                                                            <option value="delivered" {{ $order->tracking_status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                                        </select>
                                                    </div>
                                                @endif
                                            </div>
                                            <button type="submit" class="btn btn-primary w-100 mt-3">
                                                <i class="bi bi-check-circle me-1"></i>Update Order
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <!-- Categories Section -->
        <div class="row mb-5">
            <div class="col-12">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2 class="fw-bold mb-0">Menu Categories</h2>
                    <span class="badge bg-primary rounded-pill">{{ count($categories) }} categories</span>
                </div>
                
                @if ($categories->isEmpty())
                    <div class="alert alert-info text-center rounded-3 shadow-sm py-4">
                        <i class="bi bi-info-circle-fill me-2"></i>No categories available
                    </div>
                @else
                    <div class="row g-4">
                        @foreach ($categories as $category)
                            <div class="col-md-4 col-lg-3">
                                <div class="card h-100 shadow-sm border-0 rounded-3 overflow-hidden">
                                    <div class="card-header bg-light py-3">
                                        <div class="d-flex justify-content-between align-items-center">
                                            <h5 class="card-title fw-bold mb-0">{{ $category->name }}</h5>
                                            <span class="badge bg-primary rounded-pill">
                                                {{ $category->items_count ?? 0 }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="card-body py-3">
                                        <p class="text-muted mb-0">
                                            @if ($category->parent_id)
                                                <i class="bi bi-diagram-2 me-1"></i>Subcategory of 
                                                <span class="fw-medium">{{ $categories->find($category->parent_id)->name ?? 'Unknown' }}</span>
                                            @else
                                                <i class="bi bi-collection me-1"></i>Parent Category
                                            @endif
                                        </p>
                                    </div>
                                    <div class="card-footer bg-transparent border-0 pt-0">
                                        <div class="d-flex justify-content-end">
                                            <button class="btn btn-sm btn-outline-primary me-2">
                                                <i class="bi bi-pencil-square"></i>
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <style>
            /* Enhanced Card Design */
            .card {
                transition: transform 0.3s ease, box-shadow 0.3s ease;
                border: 1px solid rgba(0,0,0,0.05);
            }
            
            .card:hover {
                transform: translateY(-5px);
                box-shadow: 0 10px 20px rgba(0,0,0,0.1);
            }
            
            /* Improved Form Styling */
            .form-control-lg, .form-select-lg {
                padding: 0.75rem 1rem;
                border-radius: 0.75rem;
            }
            
            /* Badge Enhancements */
            .badge {
                font-weight: 500;
                letter-spacing: 0.5px;
            }
            
            /* Button Styles */
            .btn {
                border-radius: 0.75rem;
                font-weight: 500;
                transition: all 0.2s ease;
            }
            
            .btn-lg {
                padding: 0.75rem;
            }
            
            /* Table Improvements */
            .table-sm th, .table-sm td {
                padding: 0.5rem;
            }
            
            /* Header Spacing */
            .card-header {
                padding: 1rem 1.5rem;
            }
            
            /* Hover Effects */
            .btn-outline-primary:hover {
                background-color: #0d6efd;
                color: white;
            }
            
            /* Focus States */
            .form-control:focus, .form-select:focus {
                border-color: #86b7fe;
                box-shadow: 0 0 0 0.25rem rgba(13, 110, 253, 0.15);
            }
        </style>

        <!-- Include Bootstrap Icons -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css" rel="stylesheet">
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            // Subcategory dynamic loading
            document.getElementById('parent_category').addEventListener('change', function () {
                const parentId = this.value;
                const subSelect = document.getElementById('sub_category');
                
                if (parentId) {
                    subSelect.disabled = false;
                    subSelect.innerHTML = '<option value="">Loading...</option>';
                    
                    // AJAX call to fetch subcategories
                    fetch(`/restaurant-admin/get-subcategories/${parentId}`)
                        .then(response => response.json())
                        .then(data => {
                            subSelect.innerHTML = '<option value="">Select Subcategory</option>';
                            data.forEach(category => {
                                const option = document.createElement('option');
                                option.value = category.id;
                                option.textContent = category.name;
                                subSelect.appendChild(option);
                            });
                        });
                } else {
                    subSelect.disabled = true;
                    subSelect.innerHTML = '<option value="">Select parent first</option>';
                }
            });
            
            // Initialize tooltips
            $('[data-bs-toggle="tooltip"]').tooltip();
        });
    </script>
@endsection