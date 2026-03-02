@extends('layouts.master')

@section('title', 'Add New Room')

@section('page-content')
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card">
                <div class="card-body">
                    <h4 class="card-title">Add New Room Type</h4>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('website.admin.rooms.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Room Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name') }}"
                                        placeholder="e.g. Deluxe Suite" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Price per Night (₦) <span class="text-danger">*</span></label>
                                    <input type="number" name="price" class="form-control" value="{{ old('price') }}"
                                        required min="0">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Capacity (Guests)</label>
                                    <input type="number" name="capacity" class="form-control"
                                        value="{{ old('capacity', 2) }}" min="1">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Room Size</label>
                                    <input type="text" name="size" class="form-control" value="{{ old('size') }}"
                                        placeholder="e.g. 45 sqm">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>Bed Type</label>
                                    <select name="bed_type" class="form-control">
                                        <option value="King Size">King Size</option>
                                        <option value="Queen Size">Queen Size</option>
                                        <option value="Twin Beds">Twin Beds</option>
                                        <option value="Double Bed">Double Bed</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label>Description <span class="text-danger">*</span></label>
                            <textarea name="description" class="form-control" rows="5" required>{{ old('description') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>Status</label>
                                    <select name="status" class="form-control">
                                        <option value="available">Available (Online)</option>
                                        <option value="maintenance">Maintenance (Hidden)</option>
                                        <option value="booked">Fully Booked</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>YouTube Video URL (Optional)</label>
                                    <input type="url" name="video_url" class="form-control"
                                        value="{{ old('video_url') }}" placeholder="https://youtube.com/watch?v=...">
                                </div>
                            </div>
                        </div>

                        <div class="form-group mb-4">
                            <label class="d-block mb-2 font-weight-bold">Amenities</label>
                            <div class="row">
                                @forelse($amenities as $amenity)
                                    <div class="col-md-3 mb-2">
                                        <div class="form-check">
                                            <label class="form-check-label">
                                                <input type="checkbox" class="form-check-input" name="amenities[]"
                                                    value="{{ $amenity->id }}">
                                                {{ $amenity->name }}
                                                @if ($amenity->icon)
                                                    <i class="{{ $amenity->icon }} ml-1"></i>
                                                @endif
                                            </label>
                                        </div>
                                    </div>
                                @empty
                                    <div class="col-12 text-muted">
                                        No amenities found. <a href="{{ route('website.admin.amenities.create') }}">Create
                                            some here</a>.
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- Primary Image Upload with Preview --}}
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Primary Image <span class="text-danger">*</span></label>
                                    <div class="upload-zone" id="primaryUploadZone">
                                        <input type="file" name="image" id="primary_image" class="form-control" accept="image/*" required>
                                        <div id="primary_preview" class="upload-preview mt-2"></div>
                                    </div>
                                    <small class="text-muted">Max 20MB. Images will be automatically compressed.</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Gallery Images (Optional)</label>
                                    <input type="file" name="gallery_images[]" id="gallery_images" class="form-control" accept="image/*" multiple>
                                    <div id="gallery_preview" class="upload-preview mt-2 d-flex flex-wrap gap-2"></div>
                                    <small class="text-muted">Select multiple images for the gallery.</small>
                                </div>
                            </div>
                        </div>

                        {{-- Upload Progress Overlay (shown during form submission) --}}
                        <div id="uploadOverlay" class="upload-overlay d-none">
                            <div class="upload-modal">
                                <div class="upload-modal-content">
                                    <div class="spinner-border text-primary mb-3" role="status">
                                        <span class="visually-hidden">Uploading...</span>
                                    </div>
                                    <h5 class="mb-2">Uploading & Compressing Images</h5>
                                    <p class="text-muted mb-3" id="uploadStatusText">Please wait while we optimize your images...</p>
                                    <div class="progress" style="height: 25px; width: 100%;">
                                        <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                             role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">
                                            <span id="uploadProgressText">0%</span>
                                        </div>
                                    </div>
                                    <p class="mt-2 small text-muted" id="uploadFileInfo"></p>
                                </div>
                            </div>
                        </div>

                        <div class="card bg-light border-0 p-3 mb-4">
                            <div class="form-check form-switch">
                                <input type="checkbox" name="is_featured" value="1" class="form-check-input" 
                                       id="is_featured" {{ old('is_featured') ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold" for="is_featured">
                                    <i class="fas fa-star text-warning me-1"></i> Featured Room
                                </label>
                                <small class="d-block text-muted mt-1">Featured rooms appear on the homepage</small>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary me-2" id="submitBtn">
                            <i class="fas fa-plus-circle me-1"></i> Create Room
                        </button>
                        <a href="{{ route('website.admin.rooms.index') }}" class="btn btn-light">Cancel</a>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
<style>
    .upload-preview-item {
        position: relative;
        display: inline-block;
    }
    .upload-preview-item img {
        width: 120px;
        height: 80px;
        object-fit: cover;
        border-radius: 8px;
        border: 2px solid #e9ecef;
    }
    .upload-preview-item .file-info {
        font-size: 0.7rem;
        color: #6c757d;
        text-align: center;
        margin-top: 4px;
    }
    .upload-preview-item .file-size {
        background: rgba(0,0,0,0.6);
        color: #fff;
        font-size: 0.65rem;
        padding: 2px 6px;
        border-radius: 4px;
        position: absolute;
        bottom: 28px;
        right: 4px;
    }
    .upload-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.7);
        z-index: 9999;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .upload-modal {
        background: #fff;
        border-radius: 16px;
        padding: 40px;
        max-width: 450px;
        width: 90%;
        text-align: center;
        box-shadow: 0 20px 60px rgba(0,0,0,0.3);
    }
    .upload-modal .progress {
        border-radius: 15px;
        overflow: hidden;
    }
    .upload-modal .progress-bar {
        font-weight: 600;
        font-size: 0.85rem;
    }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const submitBtn = document.getElementById('submitBtn');
    const overlay = document.getElementById('uploadOverlay');
    const progressBar = document.getElementById('uploadProgressBar');
    const progressText = document.getElementById('uploadProgressText');
    const statusText = document.getElementById('uploadStatusText');
    const fileInfo = document.getElementById('uploadFileInfo');

    // Format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    // Primary Image Preview
    document.getElementById('primary_image').addEventListener('change', function(e) {
        const file = e.target.files[0];
        const preview = document.getElementById('primary_preview');
        preview.innerHTML = '';
        
        if (file) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const div = document.createElement('div');
                div.className = 'upload-preview-item';
                div.innerHTML = `
                    <img src="${e.target.result}" alt="Preview">
                    <span class="file-size">${formatFileSize(file.size)}</span>
                    <div class="file-info">${file.name.substring(0, 20)}${file.name.length > 20 ? '...' : ''}</div>
                `;
                preview.appendChild(div);
            }
            reader.readAsDataURL(file);
        }
    });

    // Gallery Images Preview
    document.getElementById('gallery_images').addEventListener('change', function(e) {
        const files = e.target.files;
        const preview = document.getElementById('gallery_preview');
        preview.innerHTML = '';
        
        let totalSize = 0;
        Array.from(files).forEach(file => {
            totalSize += file.size;
            const reader = new FileReader();
            reader.onload = function(ev) {
                const div = document.createElement('div');
                div.className = 'upload-preview-item';
                div.innerHTML = `
                    <img src="${ev.target.result}" alt="Preview">
                    <span class="file-size">${formatFileSize(file.size)}</span>
                    <div class="file-info">${file.name.substring(0, 15)}...</div>
                `;
                preview.appendChild(div);
            }
            reader.readAsDataURL(file);
        });

        if (files.length > 0) {
            const totalInfo = document.createElement('div');
            totalInfo.className = 'w-100 mt-2 small text-muted';
            totalInfo.innerHTML = `<i class="fas fa-images me-1"></i> ${files.length} images selected (${formatFileSize(totalSize)} total)`;
            preview.appendChild(totalInfo);
        }
    });

    // Form Submit with Progress
    form.addEventListener('submit', function(e) {
        const primaryImage = document.getElementById('primary_image').files[0];
        const galleryImages = document.getElementById('gallery_images').files;
        
        // Only show overlay if images are being uploaded
        if (primaryImage || galleryImages.length > 0) {
            e.preventDefault();
            
            // Show overlay
            overlay.classList.remove('d-none');
            submitBtn.disabled = true;
            
            // Calculate total size
            let totalSize = primaryImage ? primaryImage.size : 0;
            Array.from(galleryImages).forEach(f => totalSize += f.size);
            const totalImages = (primaryImage ? 1 : 0) + galleryImages.length;
            
            fileInfo.innerHTML = `<i class="fas fa-file-image me-1"></i> Uploading ${totalImages} image(s) (${formatFileSize(totalSize)})`;
            
            // Create FormData
            const formData = new FormData(form);
            
            // Create XMLHttpRequest for progress tracking
            const xhr = new XMLHttpRequest();
            
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    progressBar.style.width = percent + '%';
                    progressBar.setAttribute('aria-valuenow', percent);
                    progressText.textContent = percent + '%';
                    
                    if (percent < 50) {
                        statusText.textContent = 'Uploading images...';
                    } else if (percent < 90) {
                        statusText.textContent = 'Compressing & optimizing...';
                    } else {
                        statusText.textContent = 'Almost done, saving room...';
                    }
                }
            });
            
            xhr.addEventListener('load', function() {
                if (xhr.status >= 200 && xhr.status < 300) {
                    progressBar.classList.remove('progress-bar-animated');
                    progressBar.classList.add('bg-success');
                    statusText.textContent = 'Upload complete! Redirecting...';
                    progressText.textContent = '100%';
                    
                    // Redirect to success page (parse redirect from response)
                    setTimeout(() => {
                        window.location.href = '{{ route("website.admin.rooms.index") }}';
                    }, 500);
                } else {
                    // Handle error
                    overlay.classList.add('d-none');
                    submitBtn.disabled = false;
                    alert('Upload failed. Please try again.');
                }
            });
            
            xhr.addEventListener('error', function() {
                overlay.classList.add('d-none');
                submitBtn.disabled = false;
                alert('Network error. Please check your connection and try again.');
            });
            
            xhr.open('POST', form.action);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.send(formData);
        }
    });
});
</script>
@endpush
