@extends('layouts.master')

@section('title', 'Reply to Message')

@section('page-content')
<div class="container-fluid py-4">
    
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('website.admin.contact-messages.show', $contactMessage) }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Message
            </a>
            <h1 class="h3 text-gray-800 mb-0">
                <i class="fas fa-reply me-2 text-success"></i>
                Reply to {{ $contactMessage->name }}
            </h1>
        </div>
    </div>

    <div class="row">
        <!-- Reply Form -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="fas fa-paper-plane me-2 text-success"></i>Compose Reply</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('website.admin.contact-messages.send-reply', $contactMessage) }}" method="POST">
                        @csrf
                        
                        <div class="mb-3">
                            <label class="form-label text-muted">Sending to:</label>
                            <div class="form-control-plaintext">
                                <strong>{{ $contactMessage->name }}</strong> &lt;{{ $contactMessage->email }}&gt;
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="message" class="form-label">Your Reply <span class="text-danger">*</span></label>
                            <textarea name="message" id="message" 
                                class="form-control @error('message') is-invalid @enderror" 
                                rows="8" 
                                placeholder="Type your reply here..."
                                required 
                                minlength="10">{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">Minimum 10 characters required</small>
                        </div>

                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-success">
                                <i class="fas fa-paper-plane me-1"></i> Send Reply
                            </button>
                            <a href="{{ route('website.admin.contact-messages.show', $contactMessage) }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Previous Replies -->
            @if($contactMessage->replies->count() > 0)
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="fas fa-history me-2 text-info"></i>Previous Replies ({{ $contactMessage->replies->count() }})</h5>
                </div>
                <div class="card-body p-0">
                    @foreach($contactMessage->replies as $reply)
                    <div class="p-3 border-bottom">
                        <div class="d-flex justify-content-between align-items-start mb-2">
                            <div>
                                <strong>{{ $reply->user->name ?? 'Staff' }}</strong>
                                <span class="badge bg-success ms-1">Staff</span>
                            </div>
                            <small class="text-muted">{{ $reply->sent_at ? $reply->sent_at->format('M d, Y \a\t h:i A') : $reply->created_at->format('M d, Y \a\t h:i A') }}</small>
                        </div>
                        <div class="text-muted">
                            {!! nl2br(e(Str::limit($reply->message, 200))) !!}
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar - Original Message -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="fas fa-envelope me-2 text-primary"></i>Original Message</h5>
                </div>
                <div class="card-body">
                    <dl class="mb-0">
                        <dt class="text-muted small">From</dt>
                        <dd class="mb-3">
                            {{ $contactMessage->name }}<br>
                            <small class="text-muted">{{ $contactMessage->email }}</small>
                        </dd>
                        
                        @if($contactMessage->subject)
                        <dt class="text-muted small">Subject</dt>
                        <dd class="mb-3">{{ $contactMessage->subject }}</dd>
                        @endif
                        
                        <dt class="text-muted small">Received</dt>
                        <dd class="mb-3">{{ $contactMessage->created_at->format('M d, Y \a\t h:i A') }}</dd>
                        
                        <dt class="text-muted small">Message</dt>
                        <dd class="mb-0">
                            <div class="p-3 bg-light rounded" style="max-height: 300px; overflow-y: auto;">
                                {!! nl2br(e($contactMessage->message)) !!}
                            </div>
                        </dd>
                    </dl>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
