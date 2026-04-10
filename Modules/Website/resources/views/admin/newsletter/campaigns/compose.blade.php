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
    
    .btn-bp-outline {
        background-color: transparent;
        border-color: var(--bp-gold);
        color: var(--bp-gold);
    }
    .btn-bp-outline:hover {
        background-color: var(--bp-gold);
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
        padding: 12px 0;
        border-bottom: 1px solid #eee;
    }
    .info-item:last-child {
        border-bottom: none;
    }
    .info-item i {
        width: 35px;
        height: 35px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(200, 161, 101, 0.1);
        border-radius: 8px;
        color: var(--bp-gold);
        margin-right: 12px;
    }
    
    .tox-tinymce {
        border-radius: 8px !important;
    }
    
    .form-label {
        font-weight: 600;
        color: var(--bp-charcoal);
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
        <div class="d-flex justify-content-between align-items-center mb-4">
            <div>
                <a href="{{ route('website.admin.newsletter.campaigns.index') }}" class="text-muted text-decoration-none mb-2 d-inline-block">
                    <i class="fas fa-arrow-left me-1"></i> Back to Campaigns
                </a>
                <h1 class="h3 text-gray-800 mb-0">
                    <i class="fas fa-edit me-2" style="color: var(--bp-gold);"></i>
                    {{ isset($newsletter) ? 'Edit Campaign' : 'Create New Campaign' }}
                </h1>
            </div>
            @if(isset($newsletter))
                <a href="{{ route('website.admin.newsletter.campaigns.preview', $newsletter) }}" target="_blank" class="btn btn-outline-secondary">
                    <i class="fas fa-eye me-1"></i> Preview
                </a>
            @endif
        </div>

        <div class="row">
            {{-- Main Content --}}
            <div class="col-lg-8">
                <div class="card compose-card mb-4">
                    <div class="card-body p-4">
                        {{-- Subject --}}
                        <div class="mb-4">
                            <label for="subject" class="form-label">Subject Line <span class="text-danger">*</span></label>
                            <input type="text" 
                                   class="form-control form-control-lg @error('subject') is-invalid @enderror" 
                                   id="subject" 
                                   name="subject" 
                                   placeholder="Enter email subject..."
                                   value="{{ old('subject', $newsletter->subject ?? '') }}"
                                   required>
                            <div class="form-text">This is the first thing recipients will see. Make it compelling!</div>
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
                                   placeholder="Brief preview shown in inbox..."
                                   value="{{ old('preview_text', $newsletter->preview_text ?? '') }}"
                                   maxlength="500">
                            <div class="form-text">Shows below the subject line in most email clients. Max 500 characters.</div>
                            @error('preview_text')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Content Editor --}}
                        <div class="mb-3">
                            <label for="content" class="form-label">Email Content <span class="text-danger">*</span></label>
                            <textarea class="form-control @error('content') is-invalid @enderror" 
                                      id="content" 
                                      name="content" 
                                      rows="20">{{ old('content', $newsletter->content ?? '') }}</textarea>
                            @error('content')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="col-lg-4">
                {{-- Campaign Info --}}
                <div class="card sidebar-card mb-4">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-0">
                            <i class="fas fa-info-circle me-2 text-muted"></i>Campaign Info
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="info-item">
                            <i class="fas fa-users"></i>
                            <div>
                                <small class="text-muted d-block">Recipients</small>
                                <strong>{{ number_format($subscriberCount) }} Active Subscribers</strong>
                            </div>
                        </div>
                        @if(isset($newsletter))
                            <div class="info-item">
                                <i class="fas fa-tag"></i>
                                <div>
                                    <small class="text-muted d-block">Status</small>
                                    <span class="badge bg-{{ $newsletter->status_color }}">{{ ucfirst($newsletter->status) }}</span>
                                </div>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-calendar"></i>
                                <div>
                                    <small class="text-muted d-block">Created</small>
                                    <strong>{{ $newsletter->created_at->format('M d, Y h:i A') }}</strong>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Schedule Options --}}
                <div class="card sidebar-card mb-4">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-0">
                            <i class="fas fa-clock me-2 text-muted"></i>Schedule
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="action" value="draft" id="actionDraft" checked>
                                <label class="form-check-label" for="actionDraft">
                                    <strong>Save as Draft</strong>
                                    <small class="text-muted d-block">Save and continue editing later</small>
                                </label>
                            </div>
                            <div class="form-check mb-2">
                                <input class="form-check-input" type="radio" name="action" value="schedule" id="actionSchedule">
                                <label class="form-check-label" for="actionSchedule">
                                    <strong>Schedule</strong>
                                    <small class="text-muted d-block">Send at a specific time</small>
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="action" value="send" id="actionSend">
                                <label class="form-check-label" for="actionSend">
                                    <strong>Send Immediately</strong>
                                    <small class="text-muted d-block">Send to all subscribers now</small>
                                </label>
                            </div>
                        </div>
                        
                        <div class="schedule-input" id="scheduleInput">
                            <label for="scheduled_at" class="form-label">Schedule Date & Time</label>
                            <input type="datetime-local" 
                                   class="form-control @error('scheduled_at') is-invalid @enderror" 
                                   id="scheduled_at" 
                                   name="scheduled_at"
                                   value="{{ old('scheduled_at', isset($newsletter) && $newsletter->scheduled_at ? $newsletter->scheduled_at->format('Y-m-d\TH:i') : '') }}"
                                   min="{{ now()->format('Y-m-d\TH:i') }}">
                            @error('scheduled_at')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Test Email --}}
                @if(isset($newsletter))
                <div class="card sidebar-card mb-4">
                    <div class="card-header bg-white border-0 pb-0">
                        <h5 class="mb-0">
                            <i class="fas fa-flask me-2 text-muted"></i>Send Test
                        </h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small mb-3">Send a test email to preview how it will look in recipients' inboxes.</p>
                        <div class="input-group">
                            <input type="email" class="form-control" id="testEmail" placeholder="your@email.com" value="{{ auth()->user()->email }}">
                            <button type="button" class="btn btn-outline-secondary" id="sendTestBtn">
                                <i class="fas fa-paper-plane"></i>
                            </button>
                        </div>
                        <div id="testResult" class="mt-2"></div>
                    </div>
                </div>
                @endif

                {{-- Action Buttons --}}
                <div class="action-buttons">
                    <div class="d-grid gap-2">
                        <button type="submit" class="btn btn-bp-gold btn-lg" id="submitBtn">
                            <i class="fas fa-save me-2"></i><span id="submitText">Save Draft</span>
                        </button>
                        <a href="{{ route('website.admin.newsletter.campaigns.index') }}" class="btn btn-outline-secondary">
                            Cancel
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

{{-- TinyMCE CDN --}}
<script src="https://cdn.tiny.cloud/1/1f6zi61nyc7yshki2cdwti1pzec9vlcdz2xi78xu6fpfmo8u/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Initialize TinyMCE
    tinymce.init({
        selector: '#content',
        height: 500,
        plugins: [
            'advlist', 'autolink', 'lists', 'link', 'image', 'charmap', 'preview',
            'anchor', 'searchreplace', 'visualblocks', 'code', 'fullscreen',
            'insertdatetime', 'media', 'table', 'help', 'wordcount'
        ],
        toolbar: 'undo redo | blocks | ' +
            'bold italic forecolor backcolor | alignleft aligncenter ' +
            'alignright alignjust | bullist numlist outdent indent | ' +
            'link image | removeformat | help',
        content_style: `
            body { 
                font-family: 'Proxima Nova', Arial, sans-serif; 
                font-size: 14px; 
                line-height: 1.6;
                color: #333333;
            }
            a { color: #C8A165; }
            h1, h2, h3 { color: #333333; }
        `,
        branding: false,
        promotion: false,
        menubar: true,
        statusbar: true,
        setup: function(editor) {
            editor.on('change', function() {
                tinymce.triggerSave();
            });
        }
    });

    // Handle action radio buttons
    const actionRadios = document.querySelectorAll('input[name="action"]');
    const scheduleInput = document.getElementById('scheduleInput');
    const submitText = document.getElementById('submitText');
    const submitBtn = document.getElementById('submitBtn');

    function updateUI() {
        const selected = document.querySelector('input[name="action"]:checked').value;
        
        // Toggle schedule input
        scheduleInput.classList.toggle('active', selected === 'schedule');
        
        // Update button text
        switch(selected) {
            case 'draft':
                submitText.textContent = 'Save Draft';
                submitBtn.classList.remove('btn-success', 'btn-primary');
                submitBtn.classList.add('btn-bp-gold');
                break;
            case 'schedule':
                submitText.textContent = 'Schedule Campaign';
                submitBtn.classList.remove('btn-bp-gold', 'btn-success');
                submitBtn.classList.add('btn-primary');
                break;
            case 'send':
                submitText.textContent = 'Send Now';
                submitBtn.classList.remove('btn-bp-gold', 'btn-primary');
                submitBtn.classList.add('btn-success');
                break;
        }
    }

    actionRadios.forEach(radio => {
        radio.addEventListener('change', updateUI);
    });

    // Form submit confirmation for send
    document.getElementById('campaignForm').addEventListener('submit', function(e) {
        const selected = document.querySelector('input[name="action"]:checked').value;
        
        if (selected === 'send') {
            if (!confirm('Are you sure you want to send this newsletter to {{ $subscriberCount }} subscribers immediately?')) {
                e.preventDefault();
                return false;
            }
        }
        
        // Sync TinyMCE content
        tinymce.triggerSave();
    });

    // Test email functionality
    @if(isset($newsletter))
    document.getElementById('sendTestBtn').addEventListener('click', function() {
        const email = document.getElementById('testEmail').value;
        const resultDiv = document.getElementById('testResult');
        const btn = this;
        
        if (!email) {
            resultDiv.innerHTML = '<span class="text-danger small"><i class="fas fa-exclamation-circle"></i> Please enter an email</span>';
            return;
        }
        
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        resultDiv.innerHTML = '<span class="text-muted small"><i class="fas fa-spinner fa-spin"></i> Sending...</span>';
        
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
                resultDiv.innerHTML = '<span class="text-success small"><i class="fas fa-check-circle"></i> ' + data.message + '</span>';
            } else {
                resultDiv.innerHTML = '<span class="text-danger small"><i class="fas fa-exclamation-circle"></i> ' + data.message + '</span>';
            }
        })
        .catch(error => {
            resultDiv.innerHTML = '<span class="text-danger small"><i class="fas fa-exclamation-circle"></i> Failed to send test email</span>';
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
