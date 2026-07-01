@extends('restaurant::layouts.adminMaster')
@section('title', 'Manage Menu')
@section('admin-content')
<div class="container-fluid py-3">
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

    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-primary bg-opacity-10">
                <div class="fs-4 fw-bold text-primary">{{ $menuItems->count() }}</div>
                <div class="small text-muted">Menu Items</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-success bg-opacity-10">
                <div class="fs-4 fw-bold text-success">{{ $categories->count() }}</div>
                <div class="small text-muted">Categories</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-warning bg-opacity-10">
                <div class="fs-4 fw-bold text-warning">{{ $orders->where('status', 'pending')->count() }}</div>
                <div class="small text-muted">Pending Orders</div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm rounded-4 text-center p-3 bg-info bg-opacity-10">
                <div class="fs-4 fw-bold text-info">{{ $trashedItems->count() + $trashedCategories->count() }}</div>
                <div class="small text-muted">Trashed</div>
            </div>
        </div>
    </div>

    <div class="row g-4 mb-4">
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-light py-3 d-flex align-items-center">
                    <i class="bi bi-folder-plus me-2 fs-5"></i>
                    <h5 class="fw-bold mb-0">Add Category</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('restaurant.admin.add-category') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label for="name" class="form-label fw-medium">Category Name</label>
                            <input type="text" name="name" id="name" class="form-control form-control-lg" placeholder="e.g. Appetizers, Main Course" required>
                        </div>
                        <div class="mb-3">
                            <label for="parent_category" class="form-label fw-medium">Parent Category <span class="text-muted fw-normal">(optional)</span></label>
                            <select name="parent_category" id="parent_category" class="form-select form-select-lg">
                                <option value="">None — top-level category</option>
                                @foreach ($parent_categories as $p_category)
                                    <option value="{{ $p_category->id }}">{{ $p_category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn button btn-lg w-100 fw-bold">
                            <i class="bi bi-plus-circle me-1"></i> Add Category
                        </button>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-light py-3 d-flex align-items-center">
                    <i class="bi bi-plus-square me-2 fs-5"></i>
                    <h5 class="fw-bold mb-0">Add Menu Item</h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('restaurant.admin.add-item') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="item_name" class="form-label fw-medium">Item Name</label>
                                <input type="text" name="name" id="item_name" class="form-control form-control-lg" placeholder="e.g. Grilled Chicken" required>
                            </div>
                            <div class="col-md-6">
                                <label for="item_category" class="form-label fw-medium">Category</label>
                                <select name="restaurant_menu_categories_id" id="item_category" class="form-select form-select-lg" required>
                                    <option value="">Select category</option>
                                    @foreach ($categories->sortBy('name') as $category)
                                        <option value="{{ $category->id }}">{{ $category->name }} @if($category->parent_id) <span class="text-muted">(Sub)</span> @endif</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label fw-medium">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="2" placeholder="Brief description of the item"></textarea>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="price" class="form-label fw-medium">Price (₦)</label>
                                <input type="number" name="price" id="price" step="0.01" class="form-control form-control-lg" min="0" placeholder="0.00" required>
                            </div>
                            <div class="col-md-6">
                                <label for="image" class="form-label fw-medium">Image</label>
                                <input type="file" name="image" id="image" class="form-control form-control-lg" accept="image/*">
                                <small class="text-muted">Optional. Recommended: 800×600px</small>
                            </div>
                        </div>
                        <button type="submit" class="btn button btn-lg w-100 fw-bold">
                            <i class="bi bi-plus-circle me-1"></i> Add Item
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-light py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center">
                <i class="bi bi-menu-button-wide me-2 fs-5"></i>
                <h5 class="fw-bold mb-0">Menu Items</h5>
                <span class="badge bg-primary rounded-pill ms-2">{{ $menuItems->count() }}</span>
            </div>
            <div class="d-flex gap-2">
                <input type="text" id="itemSearch" class="form-control form-control-sm" placeholder="Search items..." style="width:200px">
                <select id="categoryFilter" class="form-select form-select-sm" style="width:auto">
                    <option value="">All Categories</option>
                    @foreach ($categories->sortBy('name') as $cat)
                        <option value="{{ $cat->name }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-body p-4">
            @if($menuItems->isEmpty())
                <div class="text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1"></i>
                    <p class="mt-2">No menu items yet. Add one above!</p>
                </div>
            @else
            <div class="row g-4" id="menuItemsGrid">
                @foreach ($menuItems as $item)
                    <div class="col-md-6 col-lg-4 col-xl-3 menu-item-card" data-name="{{ strtolower($item->name) }}" data-category="{{ $item->category?->name ?? '' }}">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden item-card">
                            <div class="position-relative overflow-hidden" style="height: 180px; background: #f8f9fa;">
                                <img src="{{ $item->image ? asset('storage/' . $item->image) : asset('storage/images/menudefaultimage.png') }}"
                                     alt="{{ $item->name }}"
                                     class="w-100 h-100"
                                     style="object-fit: cover;"
                                     loading="lazy"
                                     onerror="this.parentElement.innerHTML='<div class=\'d-flex align-items-center justify-content-center h-100 text-muted\'><i class=\'bi bi-image fs-1\'></i></div>'">
                            </div>
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <h6 class="fw-bold mb-0 text-truncate">{{ $item->name }}</h6>
                                    <span class="badge bg-dark rounded-pill ms-1 flex-shrink-0">{{ $item->category?->name ?? 'Uncategorized' }}</span>
                                </div>
                                @if($item->description)
                                    <p class="text-muted small mb-2 text-truncate">{{ $item->description }}</p>
                                @endif
                                <div class="fw-bold h5 text-primary mb-0">₦{{ number_format($item->price, 2) }}</div>
                                <span class="badge mt-2 {{ $item->is_available ? 'bg-success' : 'bg-secondary' }}">{{ $item->is_available ? 'Available' : 'Unavailable' }}</span>
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 px-3 pb-3 d-flex gap-2">
                                <a href="{{ route('restaurant.admin.edit-item', $item->id) }}" class="btn btn-outline-primary btn-sm rounded-pill flex-grow-1">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </a>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill flex-grow-1"
                                    data-bs-toggle="modal" data-bs-target="#deleteModal"
                                    data-id="{{ $item->id }}" data-name="{{ $item->name }}" data-type="item">
                                    <i class="bi bi-trash me-1"></i>Delete
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-light py-3 d-flex align-items-center">
            <i class="bi bi-collection me-2 fs-5"></i>
            <h5 class="fw-bold mb-0">Categories</h5>
            <span class="badge bg-primary rounded-pill ms-2">{{ $categories->count() }}</span>
        </div>
        <div class="card-body p-4">
            @if($categories->isEmpty())
                <div class="text-center text-muted py-4">
                    <i class="bi bi-folder fs-1"></i>
                    <p class="mt-2">No categories yet.</p>
                </div>
            @else
            <div class="row g-3">
                @foreach ($categories->sortBy('name') as $category)
                    <div class="col-md-4 col-lg-3">
                        <div class="card h-100 border-0 shadow-sm rounded-4">
                            <div class="card-body">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="fw-bold mb-0">{{ $category->name }}</h6>
                                    <span class="badge bg-primary rounded-pill">{{ $category->menu_items_count }}</span>
                                </div>
                                @if ($category->parent_id)
                                    <div class="small text-muted">
                                        <i class="bi bi-arrow-return-right me-1"></i>Sub of <strong>{{ $categories->find($category->parent_id)->name ?? '?' }}</strong>
                                    </div>
                                @else
                                    <div class="small text-muted"><i class="bi bi-diagram-2 me-1"></i>Top-level</div>
                                @endif
                            </div>
                            <div class="card-footer bg-white border-0 pt-0 px-3 pb-3 d-flex gap-2">
                                <a href="{{ route('restaurant.admin.edit-category', $category->id) }}" class="btn btn-outline-primary btn-sm rounded-pill flex-grow-1">
                                    <i class="bi bi-pencil me-1"></i>Edit
                                </a>
                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill flex-grow-1"
                                    data-bs-toggle="modal" data-bs-target="#deleteModal"
                                    data-id="{{ $category->id }}" data-name="{{ $category->name }}" data-type="category">
                                    <i class="bi bi-trash me-1"></i>Delete
                                </button>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif
        </div>
    </div>

    @if($trashedItems->count() || $trashedCategories->count())
    <div class="card border-0 shadow-sm rounded-4 mb-4 border-danger border-opacity-25">
        <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center cursor-pointer" data-bs-toggle="collapse" data-bs-target="#trashCollapse" role="button" style="cursor:pointer">
            <div class="d-flex align-items-center">
                <i class="bi bi-trash3 text-danger me-2 fs-5"></i>
                <h5 class="fw-bold mb-0 text-danger">Trash</h5>
                <span class="badge bg-danger rounded-pill ms-2">{{ $trashedItems->count() + $trashedCategories->count() }}</span>
            </div>
            <i class="bi bi-chevron-down" id="trashToggle"></i>
        </div>
        <div class="collapse" id="trashCollapse">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Type</th>
                                <th>Name</th>
                                <th>Deleted</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($trashedCategories as $cat)
                            <tr>
                                <td><span class="badge bg-secondary">Category</span></td>
                                <td>{{ $cat->name }}</td>
                                <td class="small text-muted">{{ $cat->deleted_at->diffForHumans() }}</td>
                                <td class="text-end">
                                    <form action="{{ route('restaurant.admin.restore-category', $cat->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success rounded-pill"><i class="bi bi-arrow-counterclockwise me-1"></i>Restore</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                            @foreach($trashedItems as $item)
                            <tr>
                                <td><span class="badge bg-dark">Item</span></td>
                                <td>{{ $item->name }} <span class="text-muted small">({{ $item->category?->name ?? 'Uncategorized' }})</span></td>
                                <td class="small text-muted">{{ $item->deleted_at->diffForHumans() }}</td>
                                <td class="text-end">
                                    <form action="{{ route('restaurant.admin.restore-item', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button class="btn btn-sm btn-outline-success rounded-pill"><i class="bi bi-arrow-counterclockwise me-1"></i>Restore</button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-light py-3 d-flex align-items-center">
            <i class="bi bi-receipt me-2 fs-5"></i>
            <h5 class="fw-bold mb-0">Recent Orders</h5>
            <span class="badge bg-primary rounded-pill ms-2">{{ $orders->count() }}</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>#</th>
                            <th>Source</th>
                            <th>Items</th>
                            <th class="text-end">Total</th>
                            <th>Status</th>
                            <th>Tracking</th>
                            <th>Date</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                        <tr>
                            <td class="fw-bold">{{ $order->id }}</td>
                            <td>{{ $order->type === 'walk_in' ? ($order->customer_name ?: 'Walk-in') : ($order->type === 'table' ? 'Table ' . $order->source_id : 'Room ' . $order->source_id) }}</td>
                            <td>{{ $order->orderItems->count() }} item(s)</td>
                            <td class="fw-bold text-end">₦{{ number_format($order->grand_total) }}</td>
                            <td>
                                @php
                                    $statusBadge = match($order->status) {
                                        'pending' => 'bg-warning text-dark',
                                        'accepted' => 'bg-info',
                                        'completed' => 'bg-success',
                                        'rejected' => 'bg-danger',
                                        'void' => 'bg-dark',
                                        default => 'bg-light text-dark',
                                    };
                                @endphp
                                <span class="badge {{ $statusBadge }} rounded-pill">{{ ucfirst($order->status) }}</span>
                            </td>
                            <td>
                                @if($order->tracking_status)
                                    <span class="badge bg-dark rounded-pill">{{ ucfirst($order->tracking_status) }}</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                            <td class="text-end">
                                <a href="{{ route('restaurant.admin.order.show', $order->id) }}" class="btn btn-sm btn-outline-primary rounded-pill">
                                    <i class="bi bi-eye"></i> View
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4">
            <div class="modal-body text-center p-4">
                <div class="text-danger mb-3"><i class="bi bi-exclamation-triangle fs-1"></i></div>
                <h5 class="fw-bold mb-2">Confirm Deletion</h5>
                <p class="text-muted small mb-0">Are you sure you want to delete <strong id="deleteModalItemName" class="text-danger"></strong>?</p>
                <p class="text-muted small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer justify-content-center border-0 pt-0 pb-4">
                <form id="deleteForm" method="POST" class="d-flex gap-2">
                    @csrf
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold">Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('admin-scripts')
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
            if (type === 'category') actionUrl = `/restaurant-admin/dashboard/category/${id}/delete`;
            else if (type === 'item') actionUrl = `/restaurant-admin/dashboard/item/${id}/delete`;
            modalForm.setAttribute('action', actionUrl);
            modalItemName.textContent = name;
        });
    }

    const searchInput = document.getElementById('itemSearch');
    const categoryFilter = document.getElementById('categoryFilter');
    const cards = document.querySelectorAll('.menu-item-card');

    function filterItems() {
        const query = searchInput.value.toLowerCase().trim();
        const cat = categoryFilter.value.toLowerCase();
        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            const category = card.getAttribute('data-category').toLowerCase();
            const matchName = !query || name.includes(query);
            const matchCat = !cat || category.includes(cat);
            card.style.display = matchName && matchCat ? '' : 'none';
        });
    }

    searchInput.addEventListener('input', filterItems);
    categoryFilter.addEventListener('change', filterItems);
});
</script>
@endsection
