@extends('restaurant::layouts.adminMaster')
@section('title', 'Edit Menu Item')
@section('admin-content')
<div class="container-fluid py-3">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('restaurant.admin.dashboard') }}" class="text-decoration-none">Manage Menu</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Item</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-7">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-light py-3 d-flex align-items-center">
                    <i class="bi bi-menu-button-wide me-2 fs-5"></i>
                    <h5 class="fw-bold mb-0">Edit Item: <span class="text-primary">{{ $item->name }}</span></h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('restaurant.admin.update-item', $item->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label fw-medium">Item Name</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light"><i class="bi bi-type"></i></span>
                                    <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $item->name) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <label for="restaurant_menu_categories_id" class="form-label fw-medium">Category</label>
                                <select name="restaurant_menu_categories_id" id="restaurant_menu_categories_id" class="form-select form-select-lg" required>
                                    @foreach ($categories as $category)
                                        <option value="{{ $category->id }}" {{ old('restaurant_menu_categories_id', $item->restaurant_menu_categories_id) == $category->id ? 'selected' : '' }}>
                                            {{ $category->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label for="description" class="form-label fw-medium">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3" placeholder="Brief description of the item">{{ old('description', $item->description) }}</textarea>
                        </div>
                        <div class="row g-3 mb-3">
                            <div class="col-md-5">
                                <label for="price" class="form-label fw-medium">Price (₦)</label>
                                <div class="input-group input-group-lg">
                                    <span class="input-group-text bg-light"><i class="bi bi-currency-dollar"></i></span>
                                    <input type="number" name="price" id="price" step="0.01" class="form-control" value="{{ old('price', $item->price) }}" min="0" required>
                                </div>
                            </div>
                            <div class="col-md-7">
                                <label for="image" class="form-label fw-medium">Image</label>
                                <input type="file" name="image" id="image" class="form-control form-control-lg" accept="image/*">
                                <small class="text-muted">Leave empty to keep current image. Recommended: 800×600px</small>
                            </div>
                        </div>
                        @if($item->image)
                        <div class="mb-4 p-3 bg-light rounded-3">
                            <label class="form-label fw-medium small mb-2">Current Image</label>
                            <div class="d-flex align-items-center gap-3">
                                <img src="{{ asset('storage/' . $item->image) }}" alt="{{ $item->name }}"
                                     class="rounded-3 border"
                                     style="width: 100px; height: 100px; object-fit: cover;"
                                     onerror="this.style.display='none'">
                                <div class="small text-muted">
                                    <i class="bi bi-check-circle text-success me-1"></i>{{ basename($item->image) }}
                                    <br><span class="text-muted">Upload a new file to replace it.</span>
                                </div>
                            </div>
                        </div>
                        @endif
                        <div class="mb-4">
                            <div class="form-check form-switch">
                                <input type="hidden" name="is_available" value="0">
                                <input type="checkbox" name="is_available" id="is_available" class="form-check-input" role="switch" value="1" {{ old('is_available', $item->is_available) ? 'checked' : '' }}>
                                <label for="is_available" class="form-check-label fw-medium">Available for online ordering</label>
                                <div class="form-text">Uncheck to hide this item from the online menu while keeping it active for POS.</div>
                            </div>
                        </div>
                        <div class="d-flex gap-2 justify-content-end mt-4">
                            <a href="{{ route('restaurant.admin.dashboard') }}" class="btn btn-outline-secondary btn-lg rounded-pill px-4">
                                <i class="bi bi-x me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn button btn-lg px-4 fw-bold">
                                <i class="bi bi-check-lg me-1"></i> Update Item
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
