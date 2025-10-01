@extends('website::layouts.admin')

@section('title', 'Edit Setting')

@section('content')
    <div class="card">
        <div class="card-header">
            <h1 class="h3 mb-0">Edit Setting</h1>
        </div>
        <div class="card-body">
            <form action="{{ route('website.admin.settings.update', $setting) }}" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="key" class="form-label">Key</label>
                    <input type="text" class="form-control @error('key') is-invalid @enderror" id="key" name="key" value="{{ old('key', $setting->key) }}" required>
                    @error('key')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="type" class="form-label">Type</label>
                    <select class="form-control @error('type') is-invalid @enderror" id="type" name="type" required>
                        <option value="string" {{ old('type', $setting->type) === 'string' ? 'selected' : '' }}>String</option>
                        <option value="image" {{ old('type', $setting->type) === 'image' ? 'selected' : '' }}>Image</option>
                        <option value="video" {{ old('type', $setting->type) === 'video' ? 'selected' : '' }}>Video</option>
                        <option value="json" {{ old('type', $setting->type) === 'json' ? 'selected' : '' }}>JSON</option>
                    </select>
                    @error('type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3" id="value-field" style="display: none;">
                    <label for="value" class="form-label">Value</label>
                    <textarea class="form-control @error('value') is-invalid @enderror" id="value" name="value">{{ old('value', $setting->value) }}</textarea>
                    @error('value')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3" id="image-field" style="display: none;">
                    <label for="image" class="form-label">Image</label>
                    <input type="file" class="form-control @error('image') is-invalid @enderror" id="image" name="image" accept="image/*">
                    @if ($setting->type === 'image' && $setting->value)
                        <div class="mt-2">
                            <img src="{{ Storage::url($setting->value) }}" alt="{{ $setting->key }}" width="100">
                        </div>
                    @endif
                    @error('image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3" id="video-field" style="display: none;">
                    <label for="video" class="form-label">Video</label>
                    <input type="file" class="form-control @error('video') is-invalid @enderror" id="video" name="video" accept="video/*">
                    @if ($setting->type === 'video' && $setting->value)
                        <div class="mt-2">
                            <a href="{{ Storage::url($setting->value) }}" target="_blank">Current Video</a>
                        </div>
                    @endif
                    @error('video')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Update Setting</button>
            </form>
        </div>
    </div>
@endsection