@extends('restaurant::layouts.master')
@section('title', 'Edit Category')

@section('content')
<div class="container my-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-lg border-0 rounded-4">
                <div class="card-header button text-white py-3">
                    <h2 class="card-title fw-bold mb-0">Edit Category: {{ $category->name }}</h2>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('restaurant.admin.update-category', $category->id) }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="name" class="form-label fw-medium">Category Name</label>
                            <input type="text" name="name" id="name" class="form-control form-control-lg" value="{{ old('name', $category->name) }}" required>
                        </div>
                        <div class="mb-4">
                            <label for="parent_id" class="form-label fw-medium">Parent Category</label>
                            <select name="parent_id" id="parent_id" class="form-select form-select-lg">
                                <option value="">None (Is a Parent)</option>
                                @foreach ($parent_categories as $parent_category)
                                    <option value="{{ $parent_category->id }}" {{ old('parent_id', $category->parent_id) == $parent_category->id ? 'selected' : '' }}>
                                        {{ $parent_category->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="d-flex justify-content-end">
                             <a href="{{ route('dashboard') }}" class="btn btn-secondary btn-lg me-2">Cancel</a>
                             <button type="submit" class="btn button btn-lg fw-bold">Update Category</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection