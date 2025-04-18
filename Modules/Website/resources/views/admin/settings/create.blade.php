@extends('website::layouts.admin')

@section('title', 'Create Setting')

@section('content')
    <div class="card">
        <div class="card-header">
            <h1 class="h3 mb-0">Create Setting</h1>
        </div>
        <div class="card-body">
            <form action="{{ route('website.admin.settings.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="key" class="form-label">Key</label>
                    <input type="text" class="form-control @error('key') is-invalid @enderror" id="key" name="key" value="{{ old('key') }}" required>
                    @error('key')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="type" class="form-label">Type</label>
                    <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                        <option value="string" {{ old('type') === 'string' ? 'selected' : '' }}>String</option>
                        <option value="image" {{ old('type') === 'image' ? 'selected' : '' }}>Image</option>
                        <option value="video" {{ old('type') === 'video' ? 'selected' : '' }}>Video</option>
                        <option value="json" {{ old('type') === 'json' ? 'selected' : '' }}>JSON</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3" id="value-field" style="display: none;">
                    <label for="value" class="form-label">Value</label>
                    <textarea class="form-control @error('value') is-invalid @enderror" id="value" name="value">{{ old('value') }}</textarea>
                    @error('value')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3" id="image-field" style="display: none;">
                    <label for="image" class="form-label">Image</label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3" id="video-field" style="display: none;">
                    <label for="video" class="form-label">Video</label>
                    <input type="file" class="form-control @error('video') is-invalid @enderror" id="video" name="video" accept="video/*">
                    @error('video')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Create Setting</button>
            </form>
        </div>
    </div>
@endsection