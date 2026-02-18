
@extends('layouts.master')

@section('title', 'Edit Room') --}}

 @section('page-content')
    <div class="row">
        <div class="col-12 grid-margin">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h4 class="card-title mb-0">Edit Room: <span class="text-primary">{{ $room->name }}</span></h4>
                        <a href="{{ route('website.admin.rooms.index') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-1"></i> Back to List
                        </a>
                    </div>

                    {{-- ✅ NICE UI: SUCCESS MESSAGE --}}
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-check-circle me-2 fs-4 text-success"></i>
                                <div>
                                    <strong>Success!</strong> {{ session('success') }}
                                </div>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    {{-- ⚠️ NICE UI: ERROR MESSAGES --}}
                    @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                            <div class="d-flex align-items-center mb-1">
                                <i class="fas fa-exclamation-circle me-2 fs-4 text-danger"></i>
                                <strong>Please fix the following errors:</strong>
                            </div>
                            <ul class="mb-0 mt-2 ps-4">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    @endif

                    <form action="{{ route('website.admin.rooms.update', $room->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Room Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ old('name', $room->name) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Price per Night (₦) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="price" class="form-control" step="0.01"
                                        value="{{ old('price', $room->price) }}" required>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Capacity (Guests) <span
                                            class="text-danger">*</span></label>
                                    <input type="number" name="capacity" class="form-control"
                                        value="{{ old('capacity', $room->capacity) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Room Size (e.g. 30 sqm)</label>
                                    <input type="text" name="size" class="form-control"
                                        value="{{ old('size', $room->size) }}">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Bed Type</label>
                                    <input type="text" name="bed_type" class="form-control"
                                        value="{{ old('bed_type', $room->bed_type) }}" placeholder="e.g. King Size">
                                </div>
                            </div>

                            <div class="col-12">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                    <textarea name="description" class="form-control" rows="4" required>{{ old('description', $room->description) }}</textarea>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Status <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control" required>
                                        <option value="available" {{ $room->status == 'available' ? 'selected' : '' }}>
                                            Available</option>
                                        <option value="booked" {{ $room->status == 'booked' ? 'selected' : '' }}>Booked
                                        </option>
                                        <option value="maintenance" {{ $room->status == 'maintenance' ? 'selected' : '' }}>
                                            Maintenance</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group mb-3">
                                    <label class="form-label fw-bold">Video URL (Optional)</label>
                                    <input type="url" name="video_url" id="video_url" class="form-control"
                                        value="{{ old('video_url', $room->video_url) }}" placeholder="https://youtube.com/...">
                                    <div id="video_preview_link"></div>
                                </div>
                            </div>

                            <div class="col-12 mb-4">
                                <label class="form-label fw-bold d-block mb-2">Amenities</label>
                                <div class="card bg-light border-0 p-3">
                                    <div class="row">
                                        @foreach ($amenities as $amenity)
                                            <div class="col-md-4 col-lg-3 mb-2">
                                                <div class="form-check">
                                                    <input type="checkbox" class="form-check-input" name="amenities[]"
                                                        value="{{ $amenity->id }}" id="am_{{ $amenity->id }}"
                                                        {{ $room->amenities->contains($amenity->id) ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="am_{{ $amenity->id }}">
                                                        {{ $amenity->name }}
                                                        @if($amenity->icon) <i class="{{ $amenity->icon }} ms-1 text-muted"></i> @endif
                                                    </label>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="form-label fw-bold">Main Room Image</label>
                                <div class="d-flex align-items-start gap-3 flex-wrap">
                                    @if ($room->image_url)
                                        <div class="text-center current-image-preview">
                                            <img src="{{ $room->image_url }}" alt="Main Image" class="img-thumbnail shadow-sm"
                                                style="width: 150px; height: 100px; object-fit: cover;">
                                            <small class="d-block text-muted mt-1">Current</small>
                                        </div>
                                    @endif
                                    <div class="flex-grow-1">
                                        <input type="file" name="image" id="primary_image" class="form-control" accept="image/*">
                                        <div id="primary_preview" class="upload-preview mt-2"></div>
                                        <small class="text-muted">Max 20MB. Images will be automatically compressed.</small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 mb-4">
                                <label class="form-label fw-bold">Room Gallery</label>
                                <div class="card bg-light border-0 p-3">
                                    @if ($room->images->count() > 0)
                                        <div class="row mb-3">
                                            <div class="col-12"><small class="text-muted mb-2 d-block fw-bold">Current Gallery Images ({{ $room->images->count() }})</small></div>
                                            @foreach ($room->images as $img)
                                                <div class="col-6 col-md-3 col-lg-2 position-relative mb-2">
                                                    <img src="{{ $img->image_url }}" class="img-thumbnail w-100 shadow-sm"
                                                        style="height: 100px; object-fit: cover;">
                                                    <button type="button"
                                                        class="btn btn-danger btn-sm position-absolute top-0 end-0 m-1 rounded-circle p-0 d-flex align-items-center justify-content-center shadow"
                                                        style="width: 24px; height: 24px;"
                                                        title="Delete this image"
                                                        onclick="if(confirm('Are you sure you want to delete this image?')) { document.getElementById('delete-img-{{ $img->id }}').submit(); }">
                                                        <i class="fas fa-times" style="font-size: 12px;"></i>
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    @endif

                                    <div class="form-group">
                                        <label>Add More Gallery Images</label>
                                        <input type="file" name="gallery_images[]" id="gallery_images"
                                            class="form-control" multiple accept="image/*">
                                        <div id="gallery_preview" class="upload-preview mt-2 d-flex flex-wrap gap-2"></div>
                                        <small class="text-muted">Select multiple images. They will be automatically compressed.</small>
                                    </div>
                                </div>
                            </div>

                            {{-- Upload Progress Overlay --}}
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

                            <div class="col-12 mt-3">
                                <button type="submit" class="btn btn-primary btn-lg shadow-sm" id="submitBtn">
                                    <i class="fas fa-save me-1"></i> Save Changes
                                </button>
                            </div>
                        </div>
                    </form> @foreach ($room->images as $img)
                        <form id="delete-img-{{ $img->id }}"
                            action="{{ route('website.admin.rooms.image.delete', $img->id) }}" method="POST"
                            style="display: none;">
                            @csrf
                            @method('DELETE')
                        </form>
                    @endforeach

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
    .upload-preview-item.new-image img {
        border-color: #198754;
    }
    .upload-preview-item .new-badge {
        background: #198754;
        color: #fff;
        font-size: 0.6rem;
        padding: 1px 5px;
        border-radius: 3px;
        position: absolute;
        top: 4px;
        left: 4px;
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
    const form = document.querySelector('form[action*="rooms"]');
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
    const primaryInput = document.getElementById('primary_image');
    if (primaryInput) {
        primaryInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            const preview = document.getElementById('primary_preview');
            preview.innerHTML = '';
            
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'upload-preview-item new-image';
                    div.innerHTML = `
                        <span class="new-badge">NEW</span>
                        <img src="${e.target.result}" alt="Preview">
                        <span class="file-size">${formatFileSize(file.size)}</span>
                        <div class="file-info">${file.name.substring(0, 20)}${file.name.length > 20 ? '...' : ''}</div>
                    `;
                    preview.appendChild(div);
                }
                reader.readAsDataURL(file);
            }
        });
    }

    // Gallery Images Preview
    const galleryInput = document.getElementById('gallery_images');
    if (galleryInput) {
        galleryInput.addEventListener('change', function(e) {
            const files = e.target.files;
            const preview = document.getElementById('gallery_preview');
            preview.innerHTML = '';
            
            let totalSize = 0;
            Array.from(files).forEach(file => {
                totalSize += file.size;
                const reader = new FileReader();
                reader.onload = function(ev) {
                    const div = document.createElement('div');
                    div.className = 'upload-preview-item new-image';
                    div.innerHTML = `
                        <span class="new-badge">NEW</span>
                        <img src="${ev.target.result}" alt="Preview">
                        <span class="file-size">${formatFileSize(file.size)}</span>
                        <div class="file-info">${file.name.substring(0, 15)}...</div>
                    `;
                    preview.appendChild(div);
                }
                reader.readAsDataURL(file);
            });

            if (files.length > 0) {
                setTimeout(() => {
                    const totalInfo = document.createElement('div');
                    totalInfo.className = 'w-100 mt-2 small text-muted';
                    totalInfo.innerHTML = `<i class="fas fa-images me-1"></i> ${files.length} new images selected (${formatFileSize(totalSize)} total)`;
                    preview.appendChild(totalInfo);
                }, 100);
            }
        });
    }

    // Video URL Live Check
    const videoInput = document.getElementById('video_url');
    if (videoInput) {
        videoInput.addEventListener('input', function(e) {
            const url = e.target.value;
            const container = document.getElementById('video_preview_link');
            if (url.includes('http')) {
                container.innerHTML = `<a href="${url}" target="_blank" class="text-success mt-1 d-block"><i class="fas fa-external-link-alt me-1"></i> Valid Link Format</a>`;
            } else {
                container.innerHTML = '';
            }
        });
    }

    // Form Submit with Progress
    if (form) {
        form.addEventListener('submit', function(e) {
            const primaryImage = document.getElementById('primary_image')?.files[0];
            const galleryImages = document.getElementById('gallery_images')?.files || [];
            
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
                            statusText.textContent = 'Almost done, saving changes...';
                        }
                    }
                });
                
                xhr.addEventListener('load', function() {
                    if (xhr.status >= 200 && xhr.status < 300) {
                        progressBar.classList.remove('progress-bar-animated');
                        progressBar.classList.add('bg-success');
                        statusText.textContent = 'Upload complete! Refreshing...';
                        progressText.textContent = '100%';
                        
                        // Reload the page to show updated images
                        setTimeout(() => {
                            window.location.reload();
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
    }
});
</script>
@endpush
