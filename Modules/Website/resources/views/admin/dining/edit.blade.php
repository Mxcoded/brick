@extends('layouts.master')

@section('title', 'Edit Dining Option')

@section('page-content')
<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold">Edit Dining Option</h5>
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

                    <form action="{{ route('website.admin.dining.update', $dining->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold">Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" required value="{{ old('name', $dining->name) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Opening Hours</label>
                            <input type="text" name="opening_hours" class="form-control" value="{{ old('opening_hours', $dining->opening_hours) }}">
                        </div>

                        {{-- ✅ ADDED: Menu Link --}}
                        <div class="mb-3">
                            <label class="form-label fw-bold">Menu Link (Optional)</label>
                            <input type="url" name="menu_link" class="form-control" placeholder="https://..." value="{{ old('menu_link', $dining->menu_link) }}">
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="4" required>{{ old('description', $dining->description) }}</textarea>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Current Image</label>
                            @if($dining->image_url)
                                <div class="mb-2">
                                    <img src="{{ $dining->image_url }}" class="rounded img-thumbnail" style="height: 150px;">
                                </div>
                            @endif
                            <input type="file" name="image" class="form-control" accept="image/*">
                            <div class="form-text">Upload to replace the current image.</div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('website.admin.dining.index') }}" class="btn btn-light">Cancel</a>
                            <button type="submit" class="btn btn-primary px-4">Update Changes</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection