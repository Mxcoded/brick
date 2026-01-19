@extends('layouts.master')

@section('title', 'Add Dining Option')

@section('page-content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Add New Dining Option</h5>
                </div>
                <div class="card-body p-4">
                    {{-- Added Error Display --}}
                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('website.admin.dining.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required value="{{ old('name') }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Opening Hours</label>
                            <input type="text" name="opening_hours" class="form-control" placeholder="e.g. 7:00 AM - 10:00 PM" value="{{ old('opening_hours') }}">
                        </div>

                        {{-- ✅ ADDED: Menu Link --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Menu Link (Optional)</label>
                            <input type="url" name="menu_link" class="form-control" placeholder="https://..." value="{{ old('menu_link') }}">
                            <div class="form-text">Link to a PDF menu or external page.</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="4" required>{{ old('description') }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <div class="form-text">Recommended size: 800x600px</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('website.admin.dining.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">Create Dining Option</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection