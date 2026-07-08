@extends('layouts.master')

@section('title', 'View Contact Message')

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
            <a href="{{ route('website.admin.contact-messages.index') }}" class="btn btn-sm btn-outline-secondary mb-2">
                <i class="fas fa-arrow-left me-1"></i> Back to Inbox
            </a>
            <h1 class="h3 text-gray-800 mb-0">
                <i class="fas fa-envelope me-2 text-primary"></i>
                @if($contactMessage->subject)
                    {{ $contactMessage->subject }}
                @else
                    Message from {{ $contactMessage->name }}
                @endif
            </h1>
        </div>
        <div class="btn-group">
            <a href="{{ route('website.admin.contact-messages.reply', $contactMessage) }}" class="btn btn-success">
                <i class="fas fa-reply me-1"></i> Reply
            </a>
            @if($contactMessage->is_archived)
                <form action="{{ route('website.admin.contact-messages.restore', $contactMessage) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-info">
                        <i class="fas fa-undo me-1"></i> Restore
                    </button>
                </form>
            @else
                <form action="{{ route('website.admin.contact-messages.archive', $contactMessage) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-secondary">
                        <i class="fas fa-archive me-1"></i> Archive
                    </button>
                </form>
            @endif
        </div>
    </div>

    <div class="row">
        <!-- Main Conversation Thread -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="fas fa-comments me-2 text-primary"></i>Conversation Thread</h5>
                </div>
                <div class="card-body p-0">
                    <!-- Original Message -->
                    <div class="p-4 border-bottom" style="background-color: #f8f9fa;">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                    <i class="fas fa-user"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong class="text-dark">{{ $contactMessage->name }}</strong>
                                        <span class="badge bg-info ms-2">Guest</span>
                                        <br>
                                        <small class="text-muted">{{ $contactMessage->email }}</small>
                                    </div>
                                    <small class="text-muted">{{ $contactMessage->created_at->format('M d, Y \a\t h:i A') }}</small>
                                </div>
                                <div class="message-content p-3 bg-white rounded border">
                                    {!! nl2br(e($contactMessage->message)) !!}
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Replies -->
                    @forelse($contactMessage->replies as $reply)
                    <div class="p-4 border-bottom">
                        <div class="d-flex">
                            <div class="flex-shrink-0">
                                <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center" style="width: 45px; height: 45px;">
                                    <i class="fas fa-headset"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1 ms-3">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <div>
                                        <strong class="text-dark">{{ $reply->user->name ?? 'Staff' }}</strong>
                                        <span class="badge bg-success ms-2">Staff Reply</span>
                                    </div>
                                    <small class="text-muted">{{ $reply->sent_at ? $reply->sent_at->format('M d, Y \a\t h:i A') : $reply->created_at->format('M d, Y \a\t h:i A') }}</small>
                                </div>
                                <div class="message-content p-3 bg-light rounded border-start border-success border-3">
                                    {!! nl2br(e($reply->message)) !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    @empty
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-comments fa-2x mb-2 d-block"></i>
                        No replies yet. <a href="{{ route('website.admin.contact-messages.reply', $contactMessage) }}">Send a reply</a> to start the conversation.
                    </div>
                    @endforelse
                </div>
            </div>

            <!-- Quick Reply Form -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="fas fa-reply me-2 text-success"></i>Quick Reply</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('website.admin.contact-messages.send-reply', $contactMessage) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <textarea name="message" class="form-control @error('message') is-invalid @enderror" rows="4" 
                                placeholder="Type your reply here..." required minlength="10">{{ old('message') }}</textarea>
                            @error('message')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted">This reply will be sent to {{ $contactMessage->email }}</small>
                        </div>
                        <button type="submit" class="btn btn-success">
                            <i class="fas fa-paper-plane me-1"></i> Send Reply
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="col-lg-4">
            <!-- Contact Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="fas fa-user-circle me-2 text-primary"></i>Contact Info</h5>
                </div>
                <div class="card-body">
                    <dl class="mb-0">
                        <dt class="text-muted small">Name</dt>
                        <dd class="mb-3">{{ $contactMessage->name }}</dd>
                        
                        <dt class="text-muted small">Email</dt>
                        <dd class="mb-3">
                            <a href="mailto:{{ $contactMessage->email }}">{{ $contactMessage->email }}</a>
                        </dd>
                        
                        @if($contactMessage->phone)
                        <dt class="text-muted small">Phone</dt>
                        <dd class="mb-3">
                            <a href="tel:{{ $contactMessage->phone }}">{{ $contactMessage->phone }}</a>
                        </dd>
                        @endif
                        
                        <dt class="text-muted small">Received</dt>
                        <dd class="mb-0">{{ $contactMessage->created_at->format('M d, Y \a\t h:i A') }}</dd>
                    </dl>
                </div>
            </div>

            <!-- Status & Actions -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom">
                    <h5 class="mb-0"><i class="fas fa-cog me-2 text-secondary"></i>Status & Actions</h5>
                </div>
                <div class="card-body">
                    <!-- Current Status -->
                    <div class="mb-3">
                        <label class="text-muted small d-block mb-1">Current Status</label>
                        @if($contactMessage->status === 'unread')
                            <span class="badge bg-primary fs-6"><i class="fas fa-envelope me-1"></i>Unread</span>
                        @elseif($contactMessage->status === 'replied')
                            <span class="badge bg-success fs-6"><i class="fas fa-reply me-1"></i>Replied</span>
                        @else
                            <span class="badge bg-secondary fs-6"><i class="fas fa-envelope-open me-1"></i>Read</span>
                        @endif

                        @if($contactMessage->is_archived)
                            <span class="badge bg-warning fs-6 ms-1"><i class="fas fa-archive me-1"></i>Archived</span>
                        @endif
                    </div>

                    <!-- Update Status Form -->
                    <form action="{{ route('website.admin.contact-messages.update', $contactMessage) }}" method="POST" class="mb-3">
                        @csrf
                        @method('PUT')
                        <label for="status" class="text-muted small d-block mb-1">Update Status</label>
                        <div class="input-group mb-2">
                            <select class="form-select" id="status" name="status">
                                <option value="unread" {{ $contactMessage->status === 'unread' ? 'selected' : '' }}>Unread</option>
                                <option value="read" {{ $contactMessage->status === 'read' ? 'selected' : '' }}>Read</option>
                                <option value="replied" {{ $contactMessage->status === 'replied' ? 'selected' : '' }}>Replied</option>
                            </select>
                            <button type="submit" class="btn btn-outline-primary">Update</button>
                        </div>

                        <label for="assigned_to" class="text-muted small d-block mb-1">Assign To</label>
                        <div class="input-group mb-2">
                            <select class="form-select" id="assigned_to" name="assigned_to">
                                <option value="">Unassigned</option>
                                @foreach($staffUsers as $user)
                                    <option value="{{ $user->id }}" {{ $contactMessage->assigned_to == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <label for="follow_up_status" class="text-muted small d-block mb-1">Follow-up Status</label>
                        <div class="input-group">
                            <select class="form-select" id="follow_up_status" name="follow_up_status">
                                <option value="pending" {{ ($contactMessage->follow_up_status ?? 'pending') === 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="followed_up" {{ $contactMessage->follow_up_status === 'followed_up' ? 'selected' : '' }}>Followed Up</option>
                                <option value="closed" {{ $contactMessage->follow_up_status === 'closed' ? 'selected' : '' }}>Closed</option>
                            </select>
                            <button type="submit" class="btn btn-outline-primary">Save</button>
                        </div>
                    </form>

                    <!-- Replies Count -->
                    @if($contactMessage->replies->count() > 0)
                    <div class="mb-3">
                        <label class="text-muted small d-block mb-1">Replies</label>
                        <span class="badge bg-info fs-6">{{ $contactMessage->replies->count() }} {{ Str::plural('reply', $contactMessage->replies->count()) }}</span>
                    </div>
                    @endif

                    @if($contactMessage->last_reply_at)
                    <div class="mb-3">
                        <label class="text-muted small d-block mb-1">Last Reply</label>
                        <span>{{ $contactMessage->last_reply_at->format('M d, Y \a\t h:i A') }}</span>
                    </div>
                    @endif

                    <hr>

                    <!-- Danger Zone -->
                    <form action="{{ route('website.admin.contact-messages.destroy', $contactMessage) }}" method="POST" 
                          onsubmit="return confirm('Are you sure you want to permanently delete this message and all its replies?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                            <i class="fas fa-trash me-1"></i> Delete Message
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
