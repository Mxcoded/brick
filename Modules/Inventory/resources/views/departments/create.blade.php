@extends('layouts.master')

@section('title', 'Add New Department')

@section('page-content')
    <div class="container-fluid p-4">
        <h1 class="display-5 text-dark mb-4">Add New Department</h1>
        <p class="lead text-muted">Create a new department and assign it to a store.</p>

        <div class="card shadow border-0 mb-4">
            <div class="card-body p-4">
                <form action="{{ route('inventory.departments.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Department Name</label>
                        <input type="text" class="form-control" id="name" name="name" value="{{ old('name') }}" required>
                        @error('name')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="store_id" class="form-label">Store</label>
                        <select class="form-select" id="store_id" name="store_id" required>
                            <option value="">Select a store</option>
                            @foreach ($stores as $store)
                                <option value="{{ $store->id }}" @selected(old('store_id') == $store->id)>{{ $store->name }}</option>
                            @endforeach
                        </select>
                        @error('store_id')
                            <div class="text-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="submit" class="btn btn-success me-2 shadow-sm">
                            <i class="fas fa-save me-2"></i>Save Department
                        </button>
                        <a href="{{ route('inventory.departments.index') }}" class="btn btn-secondary shadow-sm">
                            <i class="fas fa-times me-2"></i>Cancel
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection