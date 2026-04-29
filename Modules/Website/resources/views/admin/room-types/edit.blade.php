@extends('layouts.master')

@section('title', 'Edit ' . $roomType->name)

@section('page-content')
    <div class="container-fluid py-4">
        <div class="mb-4">
            <a href="{{ route('website.admin.room-types.index') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> Back to Room Types
            </a>
        </div>

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="mb-0"><i class="fas fa-edit me-2 text-primary"></i> Edit Room Type: {{ $roomType->name }}</h5>
            </div>

            <div class="card-body p-4">
                @if ($errors->any())
                    <div class="alert alert-danger mb-4">
                        <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form id="roomTypeForm" action="{{ route('website.admin.room-types.update', $roomType->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row">
                        {{-- Basic Information --}}
                        <div class="col-md-8">
                            <h6 class="text-muted mb-3"><i class="fas fa-info-circle me-1"></i> Basic Information</h6>
                            
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label fw-bold">Room Type Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" 
                                           value="{{ old('name', $roomType->name) }}" required>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Display Order</label>
                                    <input type="number" name="display_order" class="form-control" 
                                           value="{{ old('display_order', $roomType->display_order) }}" min="0">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Price per Night <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text">₦</span>
                                        <input type="number" name="price" class="form-control" 
                                               value="{{ old('price', $roomType->price) }}" min="0" step="0.01" required>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Capacity <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="number" name="capacity" class="form-control" 
                                               value="{{ old('capacity', $roomType->capacity) }}" min="1" required>
                                        <span class="input-group-text">guests</span>
                                    </div>
                                </div>
                                <div class="col-md-4 mb-3">
                                    <label class="form-label fw-bold">Room Size</label>
                                    <input type="text" name="size" class="form-control" 
                                           value="{{ old('size', $roomType->size) }}" placeholder="e.g., 45 sqm">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Bed Type</label>
                                    <select name="bed_type" class="form-select">
                                        <option value="">Select bed type</option>
                                        @foreach(['King Size', 'Queen Size', 'Double Bed', 'Twin Beds', 'Single Bed'] as $type)
                                            <option value="{{ $type }}" {{ old('bed_type', $roomType->bed_type) == $type ? 'selected' : '' }}>
                                                {{ $type }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-bold">Video URL</label>
                                    <input type="url" name="video_url" class="form-control" 
                                           value="{{ old('video_url', $roomType->video_url) }}" placeholder="YouTube or Vimeo link">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-bold">Description <span class="text-danger">*</span></label>
                                <textarea name="description" class="form-control" rows="4" required>{{ old('description', $roomType->description) }}</textarea>
                            </div>

                            {{-- Amenities --}}
                            <div class="mb-4">
                                <label class="form-label fw-bold">Amenities</label>
                                <div class="row">
                                    @php $selectedAmenities = old('amenities', $roomType->amenities->pluck('id')->toArray()); @endphp
                                    @foreach ($amenities as $amenity)
                                        <div class="col-md-4 col-6">
                                            <div class="form-check">
                                                <input type="checkbox" name="amenities[]" value="{{ $amenity->id }}" 
                                                       class="form-check-input" id="amenity{{ $amenity->id }}"
                                                       {{ in_array($amenity->id, $selectedAmenities) ? 'checked' : '' }}>
                                                <label class="form-check-label" for="amenity{{ $amenity->id }}">
                                                    <i class="{{ $amenity->icon ?? 'fas fa-check' }} text-muted me-1"></i>
                                                    {{ $amenity->name }}
                                                </label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- Sidebar --}}
                        <div class="col-md-4">
                            {{-- Status Options --}}
                            <div class="card bg-light border-0 mb-3">
                                <div class="card-body">
                                    <h6 class="mb-3">Status</h6>
                                    <div class="form-check form-switch mb-2">
                                        <input type="checkbox" name="is_active" value="1" class="form-check-input" 
                                               id="is_active" {{ old('is_active', $roomType->is_active) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_active">Active (visible on website)</label>
                                    </div>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" name="is_featured" value="1" class="form-check-input" 
                                               id="is_featured" {{ old('is_featured', $roomType->is_featured) ? 'checked' : '' }}>
                                        <label class="form-check-label" for="is_featured">Featured (show on homepage)</label>
                                    </div>
                                </div>
                            </div>

                            {{-- Current Main Image --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">Main Image</label>
                                @if($roomType->image_url)
                                    <div class="mb-2" id="currentMainImage">
                                        <img src="{{ $roomType->image_url }}" class="img-thumbnail" style="max-height: 150px;">
                                    </div>
                                @endif
                                <div id="mainImagePreview" class="mb-2 d-none">
                                    <img src="" class="img-thumbnail" style="max-height: 150px;">
                                    <span class="badge bg-warning ms-2">New</span>
                                </div>
                                <input type="file" name="image" id="mainImageInput" class="form-control" accept="image/*">
                                <small class="text-muted">Leave empty to keep current image.</small>
                            </div>

                            {{-- Gallery --}}
                            <div class="mb-3">
                                <label class="form-label fw-bold">Gallery Images</label>
                                <div id="existingGallery" class="d-flex flex-wrap gap-2 mb-2">
                                    @if($roomType->images->count() > 0)
                                        @foreach($roomType->images as $img)
                                            <div class="position-relative gallery-item">
                                                <img src="{{ $img->image_url }}" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                                                <button type="button" class="btn btn-danger btn-sm position-absolute top-0 end-0 p-0 delete-gallery-image" 
                                                        style="width: 18px; height: 18px; font-size: 10px; line-height: 1;"
                                                        data-image-id="{{ $img->id }}">
                                                    <i class="fas fa-times"></i>
                                                </button>
                                            </div>
                                        @endforeach
                                    @endif
                                </div>
                                <div id="newGalleryPreview" class="d-flex flex-wrap gap-2 mb-2"></div>
                                <input type="file" name="gallery_images[]" id="galleryImagesInput" class="form-control" accept="image/*" multiple>
                                <small class="text-muted">Add more images to the gallery.</small>
                            </div>

                            {{-- Units Summary --}}
                            <div class="card bg-primary-subtle border-0">
                                <div class="card-body">
                                    <h6 class="mb-2">Room Units</h6>
                                    <p class="mb-2">
                                        <strong>{{ $roomType->units->count() }}</strong> unit(s) configured
                                    </p>
                                    <a href="{{ route('website.admin.room-types.show', $roomType->id) }}" class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-door-open me-1"></i> Manage Units
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    {{-- Upload Progress --}}
                    <div id="uploadProgress" class="mb-4 d-none">
                        <div class="d-flex align-items-center mb-2">
                            <i class="fas fa-cloud-upload-alt text-primary me-2"></i>
                            <span class="fw-bold">Uploading...</span>
                            <span id="uploadPercentage" class="ms-auto fw-bold text-primary">0%</span>
                        </div>
                        <div class="progress" style="height: 20px;">
                            <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                                 role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                        </div>
                        <small id="uploadStatus" class="text-muted">Preparing upload...</small>
                    </div>

                    <div id="submitButtons" class="d-flex justify-content-end gap-2">
                        <a href="{{ route('website.admin.room-types.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" id="submitBtn" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Hidden form for deleting gallery images (outside main form to avoid nesting) --}}
    <form id="deleteGalleryImageForm" method="POST" style="display: none;">
        @csrf
        @method('DELETE')
    </form>
@endsection

@section('page-scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('roomTypeForm');
        const mainImageInput = document.getElementById('mainImageInput');
        const galleryImagesInput = document.getElementById('galleryImagesInput');
        const mainImagePreview = document.getElementById('mainImagePreview');
        const newGalleryPreview = document.getElementById('newGalleryPreview');
        const uploadProgress = document.getElementById('uploadProgress');
        const uploadProgressBar = document.getElementById('uploadProgressBar');
        const uploadPercentage = document.getElementById('uploadPercentage');
        const uploadStatus = document.getElementById('uploadStatus');
        const submitBtn = document.getElementById('submitBtn');

        // Main image preview
        mainImageInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    mainImagePreview.querySelector('img').src = e.target.result;
                    mainImagePreview.classList.remove('d-none');
                    const currentMain = document.getElementById('currentMainImage');
                    if (currentMain) currentMain.classList.add('d-none');
                };
                reader.readAsDataURL(file);
            } else {
                mainImagePreview.classList.add('d-none');
                const currentMain = document.getElementById('currentMainImage');
                if (currentMain) currentMain.classList.remove('d-none');
            }
        });

        // Gallery images preview
        galleryImagesInput.addEventListener('change', function(e) {
            newGalleryPreview.innerHTML = '';
            const files = e.target.files;
            
            Array.from(files).forEach((file, index) => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const div = document.createElement('div');
                    div.className = 'position-relative';
                    div.innerHTML = `
                        <img src="${e.target.result}" class="img-thumbnail" style="width: 60px; height: 60px; object-fit: cover;">
                        <span class="badge bg-warning position-absolute" style="bottom: 0; left: 0; font-size: 8px;">New</span>
                    `;
                    newGalleryPreview.appendChild(div);
                };
                reader.readAsDataURL(file);
            });
        });

        // Form submission with XHR for progress tracking
        form.addEventListener('submit', function(e) {
            // Check if there are files to upload
            const hasMainImage = mainImageInput.files.length > 0;
            const hasGalleryImages = galleryImagesInput.files.length > 0;
            
            if (!hasMainImage && !hasGalleryImages) {
                // No files, use normal form submission
                return true;
            }

            e.preventDefault();

            const formData = new FormData(form);
            const xhr = new XMLHttpRequest();

            // Show progress UI
            uploadProgress.classList.remove('d-none');
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Uploading...';

            // Calculate total file size for status
            let totalSize = 0;
            if (hasMainImage) totalSize += mainImageInput.files[0].size;
            if (hasGalleryImages) {
                Array.from(galleryImagesInput.files).forEach(f => totalSize += f.size);
            }
            const totalSizeMB = (totalSize / (1024 * 1024)).toFixed(2);

            // Track upload progress
            xhr.upload.addEventListener('progress', function(e) {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    const loadedMB = (e.loaded / (1024 * 1024)).toFixed(2);
                    
                    uploadProgressBar.style.width = percent + '%';
                    uploadProgressBar.setAttribute('aria-valuenow', percent);
                    uploadPercentage.textContent = percent + '%';
                    uploadStatus.textContent = `Uploaded ${loadedMB}MB of ${totalSizeMB}MB`;

                    if (percent >= 100) {
                        uploadStatus.textContent = 'Processing images, please wait...';
                        uploadProgressBar.classList.remove('progress-bar-animated');
                        uploadProgressBar.classList.add('bg-success');
                    }
                }
            });

            // Handle completion
            xhr.addEventListener('load', function() {
                try {
                    const response = JSON.parse(xhr.responseText);
                    
                    if (xhr.status >= 200 && xhr.status < 400 && response.success) {
                        uploadProgressBar.classList.add('bg-success');
                        uploadPercentage.textContent = '100%';
                        uploadStatus.innerHTML = '<i class="fas fa-check text-success"></i> Upload complete! Reloading...';
                        
                        // Reload to show changes
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    } else {
                        // Error handling with specific message
                        uploadProgressBar.classList.remove('bg-primary', 'bg-success');
                        uploadProgressBar.classList.add('bg-danger');
                        
                        let errorMessage = response.message || 'Upload failed. Please try again.';
                        
                        // Handle validation errors
                        if (response.errors) {
                            const errorList = Object.values(response.errors).flat();
                            errorMessage = errorList.join('<br>');
                        }
                        
                        uploadStatus.innerHTML = '<i class="fas fa-exclamation-circle text-danger"></i> ' + errorMessage;
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save Changes';
                    }
                } catch (e) {
                    // Response wasn't JSON - might be a redirect or HTML error page
                    if (xhr.status >= 200 && xhr.status < 400) {
                        // Assume success if status is OK
                        uploadProgressBar.classList.add('bg-success');
                        uploadPercentage.textContent = '100%';
                        uploadStatus.innerHTML = '<i class="fas fa-check text-success"></i> Upload complete! Reloading...';
                        setTimeout(() => {
                            window.location.reload();
                        }, 500);
                    } else {
                        uploadProgressBar.classList.remove('bg-primary', 'bg-success');
                        uploadProgressBar.classList.add('bg-danger');
                        uploadStatus.innerHTML = '<i class="fas fa-exclamation-circle text-danger"></i> Upload failed. Please try again.';
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save Changes';
                        console.error('Server response:', xhr.responseText);
                    }
                }
            });

            // Handle network errors
            xhr.addEventListener('error', function() {
                uploadProgressBar.classList.remove('bg-primary');
                uploadProgressBar.classList.add('bg-danger');
                uploadStatus.innerHTML = '<i class="fas fa-exclamation-circle text-danger"></i> Network error. Please check your connection.';
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save Changes';
            });

            // Handle abort
            xhr.addEventListener('abort', function() {
                uploadProgress.classList.add('d-none');
                submitBtn.disabled = false;
                submitBtn.innerHTML = '<i class="fas fa-save me-1"></i> Save Changes';
            });

            // Send request
            xhr.open('POST', form.action);
            xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            xhr.send(formData);
        });

        // Handle gallery image deletion
        document.querySelectorAll('.delete-gallery-image').forEach(function(btn) {
            btn.addEventListener('click', function(e) {
                e.preventDefault();
                e.stopPropagation();
                
                if (!confirm('Delete this image?')) {
                    return;
                }
                
                const imageId = this.getAttribute('data-image-id');
                const deleteForm = document.getElementById('deleteGalleryImageForm');
                deleteForm.action = '{{ url("website/admin/room-types/images") }}/' + imageId;
                deleteForm.submit();
            });
        });
    });
</script>
@endsection
