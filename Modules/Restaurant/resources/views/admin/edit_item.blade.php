@extends('restaurant::layouts.master')
@section('title', 'Edit Menu Item')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-4">
                 <div class="card-header bg-primary text-white py-3">
                    <h2 class="card-title fw-bold mb-0">Edit Item: {{ $item->name }}</h2>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('restaurant.admin.update-item', $item->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label for="name" class="form-label fw-medium">Item Name</label>
                                <input type="text" name="name" id="name" class="form-control form-control-lg" value="{{ old('name', $item->name) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
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
                            <textarea name="description" id="description" class="form-control" rows="3">{{ old('description', $item->description) }}</textarea>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label fw-medium">Price (₦)</label>
                                <input type="number" name="price" id="price" step="0.01" class="form-control form-control-lg" value="{{ old('price', $item->price) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="image" class="form-label fw-medium">Current Image</label>
                                <img src="{{ $item->image ? asset('storage/' . $item->image) : asset('storage/images/menudefaultimage.png') }}" alt="{{ $item->name }}" class="img-fluid rounded mb-2" style="max-height: 100px;">
                                <input type="file" name="image" id="image" class="form-control">
                                <small class="text-muted">Upload a new image to replace the current one.</small>
                            </div>
                        </div>
                        <div class="d-flex justify-content-end mt-4">
                            <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-lg me-2">Cancel</a>
                            <button type="submit" class="btn btn-primary btn-lg fw-bold">Update Item</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection