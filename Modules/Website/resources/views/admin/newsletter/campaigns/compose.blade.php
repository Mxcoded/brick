@extends('layouts.master')

@section('title', isset($newsletter) ? 'Edit Campaign' : 'Create Campaign')

@section('page-content')
<style>
    :root {
        --bp-gold: #C8A165;
        --bp-gold-light: #D4B88A;
        --bp-charcoal: #333333;
    }

    .btn-bp-gold {
        background-color: var(--bp-gold);
        border-color: var(--bp-gold);
        color: white;
    }
    .btn-bp-gold:hover {
        background-color: var(--bp-gold-light);
        border-color: var(--bp-gold-light);
        color: white;
    }

    .compose-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }

    .sidebar-card {
        border: none;
        border-radius: 12px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.08);
    }

    .info-item {
        display: flex;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    .info-item:last-child {
        border-bottom: none;
    }
    .info-item i {
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(200, 161, 101, 0.1);
        border-radius: 8px;
        color: var(--bp-gold);
        margin-right: 12px;
        flex-shrink: 0;
    }

    .form-label {
        font-weight: 600;
        color: var(--bp-charcoal);
        font-size: 0.85rem;
        margin-bottom: 0.35rem;
    }

    .char-counter {
        font-size: 0.72rem;
        color: #999;
        text-align: right;
        margin-top: 3px;
    }
    .char-counter.warning {
        color: #e6a23c;
    }
    .char-counter.danger {
        color: #ef4444;
    }

    .inbox-preview {
        background: #fafafa;
        border: 1px solid #e8e8e8;
        border-radius: 10px;
        padding: 14px 16px;
        cursor: pointer;
        transition: border-color 0.2s;
    }
    .inbox-preview:hover {
        border-color: var(--bp-gold);
    }
    .inbox-preview .sender {
        font-size: 0.75rem;
        color: #888;
        margin-bottom: 2px;
    }
    .inbox-preview .subject-preview {
        font-size: 0.9rem;
        font-weight: 600;
        color: var(--bp-charcoal);
        margin-bottom: 1px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .inbox-preview .preview-preview {
        font-size: 0.78rem;
        color: #999;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    /* Quill Editor Styles */
    .ql-container {
        font-family: 'Proxima Nova', Arial, sans-serif;
        font-size: 14px;
        border-bottom-left-radius: 8px;
        border-bottom-right-radius: 8px;
        min-height: 400px;
    }

    .ql-toolbar {
        border-top-left-radius: 8px;
        border-top-right-radius: 8px;
        background: #f8f9fa;
        border-color: #dee2e6;
    }

    .ql-container {
        border-color: #dee2e6;
    }

    .ql-editor {
        min-height: 350px;
        line-height: 1.6;
        color: #333333;
    }

    .ql-editor a {
        color: var(--bp-gold);
    }

    .ql-editor h1, .ql-editor h2, .ql-editor h3 {
        color: var(--bp-charcoal);
    }

    .ql-snow .ql-picker.ql-header {
        width: 110px;
    }

    .ql-toolbar.ql-snow .ql-formats {
        margin-right: 10px;
    }

    .ql-container.ql-snow:focus-within {
        border-color: var(--bp-gold);
        box-shadow: 0 0 0 0.2rem rgba(200, 161, 101, 0.25);
    }

    .ql-toolbar.ql-snow + .ql-container.ql-snow:focus-within {
        border-top-color: #dee2e6;
    }

    .action-buttons {
        position: sticky;
        bottom: 20px;
        background: white;
        padding: 15px;
        border-radius: 12px;
        box-shadow: 0 -4px 20px rgba(0,0,0,0.1);
        z-index: 10;
    }

    .schedule-input {
        display: none;
    }
    .schedule-input.active {
        display: block;
    }

    .schedule-option {
        border: 1px solid #e8e8e8;
        border-radius: 10px;
        padding: 12px 14px;
        margin-bottom: 8px;
        cursor: pointer;
        transition: all 0.2s;
    }
    .schedule-option:hover {
        border-color: var(--bp-gold-light);
        background: #fefcf8;
    }
    .schedule-option.selected {
        border-color: var(--bp-gold);
        background: rgba(200, 161, 101, 0.06);
    }
    .schedule-option .form-check-input:checked {
        background-color: var(--bp-gold);
        border-color: var(--bp-gold);
    }

    @media (max-width: 992px) {
        .action-buttons {
            position: static;
            box-shadow: none;
            padding: 0;
            margin-top: 16px;
        }
    }
</style>

<div class="container-fluid py-4">

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ isset($newsletter) ? route('website.admin.newsletter.campaigns.update', $newsletter) : route('website.admin.newsletter.campaigns.store') }}" 
          method="POST" id="campaignForm">
        @csrf
        @if(isset($newsletter))
            @method('PUT')
        @endif

        {{-- Header --}}
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
            <div>
                <a href="{{ route('website.admin.newsletter.campaigns.index') }}" class="text-muted text-decoration-none mb-2 d-inline-block">
                    <i class="fas fa-arrow-left me-1"></i> Back to Campaigns
                </a>
                <h1 class="h3 fw-bold mb-0">
                    <i class="fas fa-edit me-2" style="color: var(--bp-gold);"></i>
                    {{ isset($newsletter) ? 'Edit Campaign' : 'Create New Campaign' }}
                </h1>
            </div>
            @if(isset($newsletter))
                <a href="{{ route('website.admin.newsletter.campaigns.preview', $newsletter) }}" target="_blank" class="btn btn-outline-dark">
                    <i class="fas fa-eye me-1"></i> Preview
                </a>
            @endif
        </div>

        <div class="row">
            {{-- Main Content --}}
            <div class="col-lg-8">
                <div class="card compose-card mb-4">
                    <div class="card-body p-4">

                        {{-- Inbox Preview (live preview of subject + preview text) --}}
                        <div class="mb-4">
                            <label class="form-label">Inbox Preview</label>
                            <div class="inbox-preview" id="inboxPreview">
                                <div class="sender">Brickspoint Boutique Aparthotel &lt;no-reply@brickspoint.com&gt;</div>
                                <div class="subject-preview" id="inboxSubject">{{ old('subject', $newsletter->subject ?? 'Your subject line appears here') }}</div>
                                <div class="preview-preview" id="inboxPreviewText">{{ old('preview_text', $newsletter->preview_text ?? 'Preview text appears below the subject in most email clients') }}</div>
                            </div>
                        </div>

                        {{-- Subject --}}
                        <div class="mb-4">
                            <label for="subject" class="form-label">
                                Subject Line <span class="text-danger">*</span>
                                <span class="fw-normal text-muted" style="font-weight: 400;">({{ isset($newsletter) ? $newsletter->subject_length : 0 }}/255)</span>
                            </label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('subject') is-invalid @enderror" 
                                   id="subject" 
                                   name="subject" 
                                   placeholder="Enter email subject..."
                                   value="{{ old('subject', $newsletter->subject ?? '') }}"
                                   maxlength="255"
                                   required>
                            <div class="d-flex justify-content-between">
                                <div class="form-text">Make it compelling — this is the first thing recipients see.</div>
                                <div class="char-counter" id="subjectCounter">0 / 255</div>
                            </div>
                            @error('subject')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Preview Text --}}
                        <div class="mb-4">
                            <label for="preview_text" class="form-label">Preview Text</label>
                            <input type="text" 
                                   class="form-control @error('preview_text') is-invalid @enderror" 
                                   id="preview_text" 
                                   name="preview_text" 
                                   placeholder="Brief preview shown in inbox after the subject..."
                                   value="{{ old('preview_text', $newsletter->preview_text ?? '') }}"
                                   maxlength="500">
                            <div class="d-flex justify-content-between">
                                <div class="form-text">Shows below the subject in most email clients. Helps increase open rates.</div>
                                <div class="char-counter" id="previewCounter">0 / 500</div>
                            </div>
                            @error('preview_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Content Editor (Quill) --}}
                        <div class="mb-3">
                            <label class="form-label">
                                Email Content <span class="text-danger">*</span>
                                <span class="fw-normal text-muted" style="font-weight: 400;" id="contentLength">0 chars</span>
                            </label>
                            <div id="quill-editor">{!! old('content', $newsletter->content ?? '') !!}</div>
                            <input type="hidden" name="content" id="content">
                            @error('content')
                                <div class="text-danger small mt-1">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                {{-- Campaign Info --}}
                <div class="card sidebar-card mb-4">
                    <div class="card-header bg-white border-0 pb-0 pt-3 px-3">
                        <h5 class="mb-0 fs-6">
                            <i class="fas fa-info-circle me-2 text-muted"></i>Campaign Info
                        </h5>
                    </div>
                    <div class="card-body px-3 py-2">
                        <div class="info-item">
                            <i class="fas fa-users" style="font-size: 0.85rem;"></i>
                            <div>
                                <small class="text-muted d-block" style="font-size: 0.7rem;">Recipients</small>
                                <strong style="font-size: 0.85rem;">{{ number_format($subscriberCount) }} Active Subscribers</strong>
                            </div>
                        </div>
                        @if(isset($newsletter))
                            <div class="info-item">
                                <i class="fas fa-tag" style="font-size: 0.85rem;"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Status</small>
                                    <span class="badge bg-{{ $newsletter->status_color }}" style="font-size: 0.7rem;">{{ ucfirst($newsletter->status) }}</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-calendar" style="font-size: 0.85rem;"></i>
                                <div>
                                    <small class="text-muted d-block" style="font-size: 0.7rem;">Created</small>
                                    <strong style="font-size: 0.8rem;">{{ $newsletter->created_at->format('M d, Y h:i A') }}</strong>
                                </div>
                            </div>
                            @if($newsletter->author)
                                <div class="info-item">
                                    <i class="fas fa-user" style="font-size: 0.85rem;"></i>
                                    <div>
                                        <small class="text-muted d-block" style="font-size: 0.7rem;">Author</small>
                                        <strong style="font-size: 0.8rem;">{{ $newsletter->author->name }}</strong>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>

                {{-- Schedule Options --}}
                <div class="card sidebar-card mb-4">
                    <div class="card-header bg-white border-0 pb-0 pt-3 px-3">
                        <h5 class="mb-0 fs-6">
                            <i class="fas fa-clock me-2 text-muted"></i>Delivery
                        </h5>
                    </div>
                    <div class="card-body px-3 py-2">
                        <div class="schedule-option" id="optDraft" onclick="document.getElementById('actionDraft').click();">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="radio" name="action" value="draft" id="actionDraft" checked>
                                <label class="form-check-label w-100" for="actionDraft">
                                    <strong style="font-size: 0.85rem;">Save as Draft</strong>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Keep editing later</small>
                                </label>
                            </div>
                        </div>
                        <div class="schedule-option" id="optSchedule" onclick="document.getElementById('actionSchedule').click();">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="radio" name="action" value="schedule" id="actionSchedule">
                                <label class="form-check-label w-100" for="actionSchedule">
                                    <strong style="font-size: 0.85rem;">Schedule</strong>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Send at a specific time</small>
                                </label>
                            </div>
                        </div>
                        <div id="scheduleInput" class="schedule-input mt-2">
                            <label for="scheduled_at" class="form-label" style="font-size: 0.8rem;">Schedule Date & Time</label>
                            <input type="datetime-local" 
                                   class="form-control form-control-sm @error('scheduled_at') is-invalid @enderror" 
                                   id="scheduled_at" 
                                   name="scheduled_at"
                                   value="{{ old('scheduled_at', isset($newsletter) && $newsletter->scheduled_at ? $newsletter->scheduled_at->format('Y-m-d\TH:i') : '') }}"
                                   min="{{ now()->format('Y-m-d\TH:i') }}">
                            @error('scheduled_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="schedule-option" id="optSend" onclick="document.getElementById('actionSend').click();">
                            <div class="form-check mb-0">
                                <input class="form-check-input" type="radio" name="action" value="send" id="actionSend">
                                <label class="form-check-label w-100" for="actionSend">
                                    <strong style="font-size: 0.85rem;">Send Immediately</strong>
                                    <small class="text-muted d-block" style="font-size: 0.75rem;">Send to all <strong>{{ number_format($subscriberCount) }}</strong> subscribers now</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Test Email --}}
                @if(isset($newsletter))
                <div class="card sidebar-card mb-4">
                    <div class="card-header bg-white border-0 pb-0 pt-3 px-3">
                        <h5 class="mb-0 fs-6">
                            <i class="fas fa-flask me-2 text-muted"></i>Send Test
                        </h5>
                    </div>
                    <div class="card-body px-3 py-2">
                        <p class="text-muted small mb-2" style="font-size: 0.75rem;">Preview how the email looks in your inbox before sending.</p>
                        <div class="input-group input-group-sm">
                            <input type="email" class="form-control" id="testEmail" placeholder="your@email.com" value="{{ auth()->user()->email }}">
                            <button type="button" class="btn btn-outline-dark" id="sendTestBtn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <div id="testResult" class="mt-2" style="font-size: 0.78rem;"></div>
                    </div>
                </div>
                @endif

                {{-- Action Buttons --}}
                <div class="action-buttons">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-bp-gold btn-lg" id="submitBtn" style="font-size: 0.95rem;">
                            <i class="fas fa-save me-2"></i><span id="submitText">Save Draft</span>
                        </button>
                        <a href="{{ route('website.admin.newsletter.campaigns.index') }}" class="btn btn-outline-dark">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<link href="https://cdn.quilljs.com/1.3.7/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.7/quill.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Quill Editor
    const quill = new Quill('#quill-editor', {
        theme: 'snow',
        placeholder: 'Write your newsletter content here...',
        modules: {
            toolbar: [
                [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                [{ 'font': [] }],
                [{ 'size': ['small', false, 'large', 'huge'] }],
                ['bold', 'italic', 'underline', 'strike'],
                [{ 'color': [] }, { 'background': [] }],
                [{ 'align': [] }],
                [{ 'list': 'ordered' }, { 'list': 'bullet' }],
                [{ 'indent': '-1' }, { 'indent': '+1' }],
                ['blockquote', 'code-block'],
                ['link', 'image', 'video'],
                ['clean']
            ]
        }
    });

    // Sync Quill content to hidden input on text change
    quill.on('text-change', function() {
        document.getElementById('content').value = quill.root.innerHTML;
        updateContentLength();
    });

    // Initialize hidden input with existing content
    document.getElementById('content').value = quill.root.innerHTML;

    // ==========================================
    // Character Counters & Inbox Preview
    // ==========================================
    const subjectInput = document.getElementById('subject');
    const previewInput = document.getElementById('preview_text');
    const subjectCounter = document.getElementById('subjectCounter');
    const previewCounter = document.getElementById('previewCounter');
    const inboxSubject = document.getElementById('inboxSubject');
    const inboxPreviewText = document.getElementById('inboxPreviewText');
    const contentLength = document.getElementById('contentLength');

    function updateSubjectPreview() {
        const val = subjectInput.value || 'Your subject line appears here';
        inboxSubject.textContent = val;
        const len = val.length;
        subjectCounter.textContent = len + ' / 255';
        subjectCounter.className = 'char-counter' + (len > 220 ? (len > 250 ? ' danger' : ' warning') : '');
    }

    function updatePreviewTextPreview() {
        const val = previewInput.value || 'Preview text appears below the subject in most email clients';
        inboxPreviewText.textContent = val;
        const len = val.length;
        previewCounter.textContent = len + ' / 500';
        previewCounter.className = 'char-counter' + (len > 450 ? (len > 490 ? ' danger' : ' warning') : '');
    }

    function updateContentLength() {
        const text = quill.getText().trim();
        const len = text.length;
        contentLength.textContent = len > 0 ? len + ' chars' : '0 chars';
    }

    subjectInput.addEventListener('input', updateSubjectPreview);
    previewInput.addEventListener('input', updatePreviewTextPreview);

    // Initial sync
    updateSubjectPreview();
    updatePreviewTextPreview();
    updateContentLength();

    // ==========================================
    // Schedule Options
    // ==========================================
    const actionRadios = document.querySelectorAll('input[name="action"]');
    const scheduleInput = document.getElementById('scheduleInput');
    const submitText = document.getElementById('submitText');
    const submitBtn = document.getElementById('submitBtn');
    const optDraft = document.getElementById('optDraft');
    const optSchedule = document.getElementById('optSchedule');
    const optSend = document.getElementById('optSend');

    function updateUI() {
        const selected = document.querySelector('input[name="action"]:checked').value;

        // Toggle schedule input
        scheduleInput.classList.toggle('active', selected === 'schedule');

        // Update schedule option styling
        [optDraft, optSchedule, optSend].forEach(el => el.classList.remove('selected'));
        if (selected === 'draft') optDraft.classList.add('selected');
        else if (selected === 'schedule') optSchedule.classList.add('selected');
        else if (selected === 'send') optSend.classList.add('selected');

        // Update button text and style
        switch(selected) {
            case 'draft':
                submitText.textContent = 'Save Draft';
                submitBtn.className = 'btn btn-bp-gold btn-lg';
                break;
            case 'schedule':
                submitText.textContent = 'Schedule Campaign';
                submitBtn.className = 'btn btn-primary btn-lg';
                break;
            case 'send':
                submitText.textContent = 'Send Now';
                submitBtn.className = 'btn btn-success btn-lg';
                break;
        }
    }

    actionRadios.forEach(radio => {
        radio.addEventListener('change', updateUI);
    });

    // Initialize UI
    updateUI();

    // ==========================================
    // Form Submit
    // ==========================================
    document.getElementById('campaignForm').addEventListener('submit', function(e) {
        const selected = document.querySelector('input[name="action"]:checked').value;

        if (selected === 'send') {
            if (!confirm('Are you sure you want to send this newsletter to {{ $subscriberCount }} subscribers immediately?')) {
                e.preventDefault();
                return false;
            }
        }

        // Sync Quill content to hidden input
        document.getElementById('content').value = quill.root.innerHTML;

        // Validate content is not empty
        if (quill.getText().trim().length === 0) {
            e.preventDefault();
            alert('Please enter some content for your newsletter.');
            return false;
        }
    });

    // ==========================================
    // Test Email
    // ==========================================
    @if(isset($newsletter))
    document.getElementById('sendTestBtn').addEventListener('click', function() {
        const email = document.getElementById('testEmail').value;
        const resultDiv = document.getElementById('testResult');
        const btn = this;

        if (!email) {
            resultDiv.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> Please enter an email</span>';
            return;
        }

        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        resultDiv.innerHTML = '<span class="text-muted"><i class="fas fa-spinner fa-spin"></i> Sending...</span>';

        fetch('{{ route("website.admin.newsletter.campaigns.test", $newsletter) }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ email: email })
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                resultDiv.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> ' + data.message + '</span>';
            } else {
                resultDiv.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> ' + data.message + '</span>';
            }
        })
        .catch(() => {
            resultDiv.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-circle"></i> Connection error</span>';
        })
        .finally(() => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        });
    });
    @endif
});
</script>
@endsection
