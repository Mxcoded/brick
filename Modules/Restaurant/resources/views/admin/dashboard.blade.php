@extends('restaurant::layouts.master')
@section('title', 'Admin Dashboard')
@section('content')
    <div class="container-fluid">
        <div class="text-center mb-5 mt-4">
            <h1 class="display-4 fw-bold text-dark">Admin Dashboard</h1>
            <p class="lead text-muted">Manage your restaurant's menu, items, and orders seamlessly.</p>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row mb-5 g-4">
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-header bg-light py-3"><h3 class="card-title fw-bold mb-0">Add New Category</h3></div>
                    <div class="card-body p-4">
                        <form action="{{ route('restaurant.admin.add-category') }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="name" class="form-label fw-medium">Category Name</label>
                                <input type="text" name="name" id="name" class="form-control form-control-lg" placeholder="e.g., Appetizers" required>
                            </div>
                            <div class="mb-4">
                                <label for="parent_category" class="form-label fw-medium">Parent Category (Optional)</label>
                                <select name="parent_category" id="parent_category" class="form-select form-select-lg">
                                    <option value="">None (It's a Parent Category)</option>
                                    @foreach ($parent_categories as $p_category)
                                        <option value="{{ $p_category->id }}">{{ $p_category->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <button type="submit" class="btn button btn-lg w-100 py-2 fw-bold">Add Category</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                 <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-header bg-light py-3"><h3 class="card-title fw-bold mb-0">Add New Menu Item</h3></div>
                    <div class="card-body p-4">
                        <form action="{{ route('restaurant.admin.add-item') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                             <div class="row g-3">
                                <div class="col-md-6 mb-3">
                                    <label for="item_name" class="form-label fw-medium">Item Name</label>
                                    <input type="text" name="name" id="item_name" class="form-control form-control-lg" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="item_category" class="form-label fw-medium">Category</label>
                                    <select name="restaurant_menu_categories_id" id="item_category" class="form-select form-select-lg" required>
                                        <option value="">Select Category</option>
                                        @foreach ($categories->sortBy('name') as $category)
                                            <option value="{{ $category->id }}">{{ $category->name }} @if($category->parent_id) (Sub) @endif</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="description" class="form-label fw-medium">Description</label>
                                <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="row g-3">
                                <div class="col-md-6 mb-3">
                                    <label for="price" class="form-label fw-medium">Price (₦)</label>
                                    <input type="number" name="price" id="price" step="0.01" class="form-control form-control-lg" required min="0">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="image" class="form-label fw-medium">Image</label>
                                    <input type="file" name="image" id="image" class="form-control">
                                </div>
                            </div>
                            <button type="submit" class="btn button btn-lg w-100 py-2 fw-bold mt-3">Add Item</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="row mb-5">
            <div class="col-12">
                <h2 class="fw-bold mb-4">Manage Menu Items</h2>
                <div class="row g-4">
                    @forelse ($menuItems as $item)
                        <div class="col-md-6 col-lg-4 col-xl-3">
                            <div class="card h-100 shadow-sm border-0 rounded-3">
                                <img src="{{ $item->image ? asset('storage/' . $item->image) : asset('storage/images/menudefaultimage.png') }}" class="card-img-top" alt="{{ $item->name }}" style="height: 200px; object-fit: cover;">
                                <div class="card-body">
                                    <h5 class="card-title fw-bold">{{ $item->name }}</h5>
                                    <p class="card-text text-muted small">{{ Str::limit($item->description, 50) }}</p>
                                    <p class="fw-bold h5 text-primary mb-2">₦{{ number_format($item->price, 2) }}</p>
                                    <span class="badge bg-secondary"><i class="bi bi-tag me-1"></i>{{ $item->category->name ?? 'Uncategorized' }}</span>
                                </div>
                                <div class="card-footer bg-white border-0 p-3">
                                    <div class="d-flex justify-content-end">
                                        <a href="{{ route('restaurant.admin.edit-item', $item->id) }}" class="btn btn-sm btn-outline-primary me-2"><i class="bi bi-pencil-square"></i> Edit</a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{ $item->id }}" data-name="{{ $item->name }}" data-type="item">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12"><div class="alert alert-info">No menu items found.</div></div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="row mb-5">
             <div class="col-12">
                <h2 class="fw-bold mb-4">Manage Menu Categories</h2>
                <div class="row g-4">
                    @forelse ($categories->sortBy('name') as $category)
                        <div class="col-md-4 col-lg-3">
                            <div class="card h-100 shadow-sm border-0 rounded-3">
                                <div class="card-body">
                                    <h5 class="card-title fw-bold">{{ $category->name }}</h5>
                                    <p class="text-muted mb-2">
                                        @if ($category->parent_id)
                                            <i class="bi bi-diagram-2 me-1"></i>Subcategory of <strong>{{ $categories->find($category->parent_id)->name ?? 'Unknown' }}</strong>
                                        @else
                                            <i class="bi bi-collection me-1"></i><strong>Parent Category</strong>
                                        @endif
                                    </p>
                                    <span class="badge bg-primary rounded-pill">{{ $category->menu_items_count }} items</span>
                                </div>
                                <div class="card-footer bg-white border-0 p-3">
                                     <div class="d-flex justify-content-end">
                                        <a href="{{ route('restaurant.admin.edit-category', $category->id) }}" class="btn btn-sm btn-outline-primary me-2"><i class="bi bi-pencil-square"></i> Edit</a>
                                        <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#deleteModal" data-id="{{ $category->id }}" data-name="{{ $category->name }}" data-type="category">
                                            <i class="bi bi-trash"></i> Delete
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12"><div class="alert alert-info">No categories found.</div></div>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="row mb-5">
           </div>
    </div>

    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete <strong id="deleteModalItemName"></strong>? This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <form id="deleteForm" method="POST">
                        @csrf
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const deleteModal = document.getElementById('deleteModal');
    if (deleteModal) {
        deleteModal.addEventListener('show.bs.modal', function (event) {
            const button = event.relatedTarget;
            const id = button.getAttribute('data-id');
            const name = button.getAttribute('data-name');
            const type = button.getAttribute('data-type');

            const modalForm = deleteModal.querySelector('#deleteForm');
            const modalItemName = deleteModal.querySelector('#deleteModalItemName');

            let actionUrl = '';
            if (type === 'category') {
                actionUrl = `/restaurant-admin/dashboard/category/${id}/delete`;
            } else if (type === 'item') {
                actionUrl = `/restaurant-admin/dashboard/item/${id}/delete`;
            }
            
            modalForm.setAttribute('action', actionUrl);
            modalItemName.textContent = name;
        });
    }
});
</script>
@endsection