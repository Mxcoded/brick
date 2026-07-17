@extends('restaurant::layouts.adminMaster')
@section('title', 'Edit Category')
@section('admin-content')
<div class="container-fluid py-3">
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="{{ route('restaurant.admin.dashboard') }}" class="text-decoration-none">Manage Menu</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Category</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-8 col-xl-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-light py-3 d-flex align-items-center">
                    <i class="bi bi-folder me-2 fs-5"></i>
                    <h5 class="fw-bold mb-0">Edit Category: <span class="text-primary">{{ $category->name }}</span></h5>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('restaurant.admin.update-category', $category->id) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="name" class="form-label fw-medium">Category Name</label>
                            <div class="input-group input-group-lg">
                                <span class="input-group-text bg-light"><i class="bi bi-tag"></i></span>
                                <input type="text" name="name" id="name" class="form-control" value="{{ old('name', $category->name) }}" placeholder="e.g. Appetizers" required>
                            </div>
                        </div>
                        <div class="mb-4">
                            <label for="parent_id" class="form-label fw-medium">Parent Category <span class="text-muted fw-normal">(optional)</span></label>
                            <select name="parent_id" id="parent_id" class="form-select form-select-lg">
                                <option value="">None — top-level category</option>
                                @foreach ($parent_categories as $parent_category)
                                    <option value="{{ $parent_category->id }}" {{ old('parent_id', $category->parent_id) == $parent_category->id ? 'selected' : '' }}>
                                        {{ $parent_category->name }}
                                    </option>
                                @endforeach
                            </select>
                            @if($category->parent_id)
                                <small class="text-muted">Currently a subcategory of <strong>{{ $parent_categories->find($category->parent_id)?->name ?? '?' }}</strong></small>
                            @endif
                        </div>
                        <div class="d-flex gap-2 justify-content-end">
                            <a href="{{ route('restaurant.admin.dashboard') }}" class="btn btn-outline-secondary btn-lg rounded-pill px-4">
                                <i class="bi bi-x me-1"></i> Cancel
                            </a>
                            <button type="submit" class="btn button btn-lg px-4 fw-bold">
                                <i class="bi bi-check-lg me-1"></i> Update Category
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
