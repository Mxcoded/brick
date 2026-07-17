@extends('layouts.master')

@section('title', 'Upload Files')

@section('styles')
<style>
    .drop-zone:hover, .drop-zone.dragover { border-color: #C8A165 !important; background: #fdfaf5; }
    .drop-zone.dragover { background: #fdf5e6; }
    .form-control:focus, .form-control:active { border-color: #C8A165; box-shadow: 0 0 0 3px rgba(200,161,101,0.15); }
    .progress-thin { height: 8px; border-radius: 4px; }
    .progress-thin .progress-bar { border-radius: 4px; transition: width 0.3s ease; }
    .file-progress-item:last-child { border-bottom: none !important; }
</style>
@endsection

@section('page-content')
<div class="container-fluid py-4 px-lg-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div>
            <h1 class="h3 fw-bold mb-1" style="color: #1a1a2e;"><i class="fas fa-upload me-2" style="color: #C8A165;"></i>Upload Files</h1>
            <p class="text-muted small mb-0">Share documents with the team — max 300 MB per file</p>
        </div>
        <a href="{{ route('staff.documents.index') }}" class="btn btn-sm px-3 fw-semibold" style="color: #2c3e50; border: 1px solid #d0d0d0; border-radius: 8px;">
            <i class="fas fa-arrow-left me-1"></i> Back to Files
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show">
            <strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-transparent py-3 border-bottom">
                    <h6 class="mb-0 fw-bold" style="color: #2c3e50;"><i class="fas fa-cloud-upload-alt me-2" style="color: #C8A165;"></i>Select Files</h6>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('staff.documents.store') }}" enctype="multipart/form-data" id="uploadForm">
                        @csrf

                        <div class="mb-4">
                            <label class="form-label fw-semibold">Choose Files <span class="text-danger">*</span></label>
                            <div class="drop-zone border rounded-3 p-5 text-center" id="dropZone"
                                style="border: 2px dashed #d0d0d0; cursor: pointer; transition: all 0.2s;">
                                <i class="fas fa-cloud-upload-alt fa-3x mb-3 d-block" style="color: #C8A165;"></i>
                                <h6 class="fw-bold mb-1">Drag & drop files here</h6>
                                <p class="text-muted small mb-3">or click to browse — up to 300 MB per file</p>
                                <button type="button" class="btn btn-sm px-4 fw-semibold" style="background: #C8A165; color: #fff; border-radius: 8px;" id="browseBtn">
                                    <i class="fas fa-folder-open me-1"></i> Browse Files
                                </button>
                                <input type="file" name="files[]" id="fileInput" style="position:absolute;left:-9999px;opacity:0;width:0;height:0;pointer-events:none;" multiple accept="*">
                            </div>
                        </div>

                        <div id="fileList" class="mb-4 d-none">
                            <label class="form-label fw-semibold">Selected Files</label>
                            <div class="list-group" id="selectedFiles"></div>
                        </div>

                        <div id="descriptionField" class="mb-4">
                            <label for="description" class="form-label fw-semibold">Description <span class="text-muted small">(optional)</span></label>
                            <textarea name="description" id="description" class="form-control" rows="2" placeholder="Brief description of these files..." style="border-color: #e0e0e0;">{{ old('description') }}</textarea>
                        </div>

                        <div class="d-flex gap-2" id="formActions">
                            <button type="submit" class="btn btn-lg px-5 fw-semibold" id="submitBtn" style="background: #C8A165; color: #fff; border: none; border-radius: 8px;">
                                <i class="fas fa-upload me-2"></i> <span id="submitText">Upload Files</span>
                            </button>
                            <a href="{{ route('staff.documents.index') }}" class="btn btn-lg px-4 fw-semibold" style="color: #2c3e50; border: 1px solid #d0d0d0; border-radius: 8px;">Cancel</a>
                        </div>
                    </form>

                    {{-- Progress Section --}}
                    <div id="progressSection" class="d-none">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="fw-semibold small" id="progressLabel">Uploading files...</span>
                                <span class="fw-bold" style="color: #C8A165;" id="progressPercent">0%</span>
                            </div>
                            <div class="progress progress-thin">
                                <div class="progress-bar" id="progressBar" role="progressbar"
                                    style="width: 0%; background: linear-gradient(90deg, #C8A165, #e0b87a);"
                                    aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                        <div id="fileProgressContainer" class="mb-3"></div>
                        <div id="uploadComplete" class="d-none text-center py-4">
                            <i class="fas fa-check-circle fa-3x mb-2" style="color: #27ae60;"></i>
                            <h6 class="fw-bold" id="completeMessage">Upload Complete!</h6>
                            <a href="{{ route('staff.documents.index') }}" class="btn btn-sm px-4 fw-semibold mt-2" style="background: #C8A165; color: #fff; border-radius: 8px;">
                                <i class="fas fa-folder-open me-1"></i> View Files
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-transparent py-3 border-bottom">
                    <h6 class="mb-0 fw-bold small text-uppercase" style="color: #2c3e50;"><i class="fas fa-info-circle me-2" style="color: #C8A165;"></i>Guidelines</h6>
                </div>
                <div class="card-body small" style="color: #5a5a5a;">
                    <ul class="mb-0 ps-3">
                        <li class="mb-2">Maximum file size: <strong>300 MB</strong> per file</li>
                        <li class="mb-2">Supported: PDF, Word, Excel, PowerPoint, images, archives</li>
                        <li class="mb-2">Upload multiple files at once</li>
                        <li class="mb-2">All authenticated staff can download shared files</li>
                        <li class="mb-2">Only admins can delete files</li>
                        <li class="mb-0 fw-semibold" style="color: #c0392b;"><i class="fas fa-clock me-1"></i> Files auto-delete after <strong>7 days</strong></li>
                    </ul>
                </div>
            </div>

            <div class="card border-0 shadow-sm" style="background: linear-gradient(135deg, #1a1a2e 0%, #2c3e50 100%);">
                <div class="card-body text-center text-white py-4">
                    <i class="fas fa-shield-alt fa-2x mb-2 opacity-75" style="color: #C8A165;"></i>
                    <h6 class="fw-bold mb-1">Need Larger Files?</h6>
                    <p class="small mb-0 opacity-75" style="font-size: 0.8rem;">
                        Files over 300 MB are not supported through this portal. Contact IT for alternative transfer methods.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-scripts')
<script>
(function () {
    var fileInput = document.getElementById('fileInput');
    var fileList = document.getElementById('fileList');
    var selectedFiles = document.getElementById('selectedFiles');
    var dropZone = document.getElementById('dropZone');
    var form = document.getElementById('uploadForm');
    var submitBtn = document.getElementById('submitBtn');
    var submitText = document.getElementById('submitText');
    var formActions = document.getElementById('formActions');
    var descriptionField = document.getElementById('descriptionField');
    var progressSection = document.getElementById('progressSection');
    var progressBar = document.getElementById('progressBar');
    var progressPercent = document.getElementById('progressPercent');
    var progressLabel = document.getElementById('progressLabel');
    var fileProgressContainer = document.getElementById('fileProgressContainer');
    var uploadComplete = document.getElementById('uploadComplete');
    var completeMessage = document.getElementById('completeMessage');

    var currentFiles = [];

    function formatSize(bytes) {
        if (bytes >= 1073741824) return (bytes / 1073741824).toFixed(2) + ' GB';
        if (bytes >= 1048576) return (bytes / 1048576).toFixed(1) + ' MB';
        if (bytes >= 1024) return (bytes / 1024).toFixed(0) + ' KB';
        return bytes + ' B';
    }

    function updateFileList(files) {
        currentFiles = Array.from(files);
        selectedFiles.innerHTML = '';
        if (currentFiles.length === 0) {
            fileList.classList.add('d-none');
            return;
        }
        fileList.classList.remove('d-none');
        currentFiles.forEach(function (f) {
            var div = document.createElement('div');
            div.className = 'list-group-item list-group-item-action d-flex justify-content-between align-items-center py-2 px-3';
            div.innerHTML = '<span><i class="fas fa-file me-2"></i>' + f.name + '</span><small class="text-muted">' + formatSize(f.size) + '</small>';
            selectedFiles.appendChild(div);
        });
    }

    fileInput.addEventListener('change', function () {
        updateFileList(this.files);
        this.value = '';
    });

    dropZone.addEventListener('dragover', function (e) {
        e.preventDefault();
        this.classList.add('dragover');
    });
    dropZone.addEventListener('dragleave', function () {
        this.classList.remove('dragover');
    });
    dropZone.addEventListener('drop', function (e) {
        e.preventDefault();
        this.classList.remove('dragover');
        if (e.dataTransfer.files.length) {
            updateFileList(e.dataTransfer.files);
        }
    });
    dropZone.addEventListener('click', function () {
        if (currentFiles.length === 0) fileInput.click();
    });

    // ---- AJAX Upload with Progress ----
    form.addEventListener('submit', function (e) {
        if (currentFiles.length === 0) {
            e.preventDefault();
            return;
        }
        e.preventDefault();

        // Hide form, show progress
        formActions.classList.add('d-none');
        descriptionField.classList.add('d-none');
        dropZone.style.opacity = '0.4';
        progressSection.classList.remove('d-none');
        uploadComplete.classList.add('d-none');
        fileProgressContainer.innerHTML = '';

        // Build file progress items
        currentFiles.forEach(function (f) {
            var safeName = f.name.replace(/[^a-zA-Z0-9]/g, '_');
            var item = document.createElement('div');
            item.className = 'py-2 border-bottom file-progress-item';
            item.id = 'fp-' + safeName;
            item.innerHTML = '<div class="d-flex justify-content-between align-items-center">'
                + '<small class="text-truncate" style="max-width: 250px;">' + f.name + '</small>'
                + '<small id="fp-status-' + safeName + '" style="white-space: nowrap; font-weight: 600;">waiting...</small>'
                + '</div>'
                + '<div class="mt-1" style="display: none;" id="fp-err-' + safeName + '"></div>';
            fileProgressContainer.appendChild(item);
        });

        var totalFiles = currentFiles.length;
        var totalBytes = currentFiles.reduce(function (sum, f) { return sum + f.size; }, 0);
        var successCount = 0;
        var failCount = 0;
        var completedCount = 0;
        var completedSize = 0;
        var inFlight = {}; // {idx: loaded} for active uploads

        function showCompletion() {
            if (failCount > 0) {
                completeMessage.textContent = successCount + ' file(s) uploaded, ' + failCount + ' failed.';
                completeMessage.style.color = '#e67e22';
                document.querySelector('#uploadComplete i').className = 'fas fa-exclamation-triangle fa-3x mb-2';
                document.querySelector('#uploadComplete i').style.color = '#e67e22';
            } else {
                completeMessage.textContent = 'All ' + totalFiles + ' file(s) uploaded successfully!';
                completeMessage.style.color = '#1a1a2e';
                document.querySelector('#uploadComplete i').className = 'fas fa-check-circle fa-3x mb-2';
                document.querySelector('#uploadComplete i').style.color = '#27ae60';
            }
            progressLabel.textContent = 'Upload complete';
            progressPercent.textContent = '100%';
            progressBar.style.width = '100%';
            uploadComplete.classList.remove('d-none');
        }

        function recalcOverall() {
            var loaded = completedSize;
            for (var idx in inFlight) {
                loaded += inFlight[idx];
            }
            var pct = Math.round((loaded / totalBytes) * 100);
            if (pct > 100) pct = 100;
            progressPercent.textContent = pct + '%';
            progressBar.style.width = pct + '%';
        }

        function updateLabel() {
            var active = 0;
            for (var idx in inFlight) active++;
            if (active > 0) {
                progressLabel.textContent = 'Uploading ' + active + ' file(s)... (' + completedCount + ' done)';
            } else {
                progressLabel.textContent = 'Finishing...';
            }
        }

        // Start all files in parallel
        currentFiles.forEach(function (file, idx) {
            var safeName = file.name.replace(/[^a-zA-Z0-9]/g, '_');
            var statusEl = document.getElementById('fp-status-' + safeName);
            var errEl = document.getElementById('fp-err-' + safeName);
            statusEl.textContent = 'waiting...';
            statusEl.style.color = '#999';
            errEl.style.display = 'none';

            // Mark in-flight
            inFlight[idx] = 0;

            var fd = new FormData();
            fd.append('files[]', file);
            fd.append('description', document.getElementById('description').value || '');
            fd.append('_token', document.querySelector('input[name="_token"]').value);

            var xhr = new XMLHttpRequest();

            xhr.upload.addEventListener('progress', function (evt) {
                if (evt.lengthComputable) {
                    var filePct = Math.round((evt.loaded / evt.total) * 100);
                    statusEl.textContent = filePct + '%';
                    statusEl.style.color = '#C8A165';
                    inFlight[idx] = evt.loaded;
                    recalcOverall();
                    updateLabel();
                }
            });

            xhr.addEventListener('load', function () {
                delete inFlight[idx];
                var ok = false;
                var errMsg = '';
                try {
                    var resp = JSON.parse(xhr.responseText);
                    ok = resp.success === true;
                    if (!ok) {
                        if (resp.errors && Array.isArray(resp.errors) && resp.errors.length) {
                            errMsg = resp.errors.join('; ');
                        } else if (resp.message) {
                            errMsg = resp.message;
                        } else {
                            var allErrors = resp.errors;
                            if (allErrors && typeof allErrors === 'object') {
                                var msgs = [];
                                for (var field in allErrors) {
                                    if (allErrors.hasOwnProperty(field) && Array.isArray(allErrors[field])) {
                                        msgs.push(allErrors[field][0]);
                                    }
                                }
                                errMsg = msgs.join('; ');
                            } else {
                                errMsg = 'server rejected the file';
                            }
                        }
                    }
                } catch (e) {
                    ok = xhr.status >= 200 && xhr.status < 300;
                    if (!ok && xhr.status === 413) errMsg = 'file exceeds server size limit';
                    else if (!ok && xhr.status === 419) errMsg = 'session expired, please refresh';
                    else if (!ok && xhr.status === 500) errMsg = 'internal server error';
                    else if (!ok) errMsg = 'server error (HTTP ' + xhr.status + ')';
                }

                if (ok) {
                    completedSize += file.size;
                    successCount++;
                    statusEl.textContent = 'done';
                    statusEl.style.color = '#27ae60';
                } else {
                    failCount++;
                    statusEl.textContent = 'failed';
                    statusEl.style.color = '#c0392b';
                    var reason = errMsg || 'unknown error';
                    statusEl.title = reason;
                    errEl.textContent = '\u26A0 ' + reason;
                    errEl.style.cssText = 'display:block;font-size:0.75rem;color:#c0392b;margin-top:2px;';
                }

                completedCount++;
                recalcOverall();
                updateLabel();
                if (completedCount >= totalFiles) showCompletion();
            });

            xhr.addEventListener('error', function () {
                delete inFlight[idx];
                failCount++;
                completedCount++;
                statusEl.textContent = 'failed';
                statusEl.style.color = '#c0392b';
                errEl.textContent = '\u26A0 network error - check connection';
                errEl.style.cssText = 'display:block;font-size:0.75rem;color:#c0392b;margin-top:2px;';
                recalcOverall();
                updateLabel();
                if (completedCount >= totalFiles) showCompletion();
            });

            xhr.open('POST', form.action, true);
            xhr.send(fd);
        });
    });
})();
</script>
@endsection
