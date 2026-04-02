@extends('layouts.master')

@section('title', 'Inbox')

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

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800"><i class="fas fa-inbox me-2 text-primary"></i>Inbox</h1>
        <div>
            @if($unreadCount > 0)
                <span class="badge bg-danger me-2">{{ $unreadCount }} unread</span>
            @endif
            <span class="badge bg-primary">{{ $messages->total() }} messages</span>
        </div>
    </div>

    <!-- Archive Tabs -->
    <ul class="nav nav-tabs mb-4">
        <li class="nav-item">
            <a class="nav-link {{ request('archive') !== 'archived' ? 'active' : '' }}" 
               href="{{ route('website.admin.contact-messages.index', array_merge(request()->except('archive'), ['archive' => 'active'])) }}">
                <i class="fas fa-inbox me-1"></i> Active
                <span class="badge bg-primary ms-1">{{ $activeCount }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link {{ request('archive') === 'archived' ? 'active' : '' }}" 
               href="{{ route('website.admin.contact-messages.index', array_merge(request()->except('archive'), ['archive' => 'archived'])) }}">
                <i class="fas fa-archive me-1"></i> Archived
                <span class="badge bg-secondary ms-1">{{ $archivedCount }}</span>
            </a>
        </li>
    </ul>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('website.admin.contact-messages.index') }}" method="GET" class="row g-3">
                <input type="hidden" name="archive" value="{{ request('archive', 'active') }}">
                <div class="col-md-6">
                    <div class="input-group">
                        <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="Search sender or subject..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Messages</option>
                        <option value="unread" {{ request('status') == 'unread' ? 'selected' : '' }}>Unread Only</option>
                        <option value="read" {{ request('status') == 'read' ? 'selected' : '' }}>Read Only</option>
                        <option value="replied" {{ request('status') == 'replied' ? 'selected' : '' }}>Replied Only</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <button type="submit" class="btn btn-primary w-100"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Sender</th>
                        <th>Subject / Message</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $msg)
                    <tr class="{{ $msg->status === 'unread' ? 'fw-bold' : '' }}" style="{{ $msg->status === 'unread' ? 'background-color: #f0f7ff;' : '' }}">
                        <td class="ps-4">
                            {{ $msg->name }}<br>
                            <small class="text-muted fw-normal">{{ $msg->email }}</small>
                        </td>
                        <td>
                            @if($msg->subject)
                                {{ Str::limit($msg->subject, 30) }} 
                                <span class="text-muted fw-normal mx-1">-</span> 
                            @endif
                            <small class="text-muted fw-normal">{{ Str::limit($msg->message, 40) }}</small>
                            @if($msg->replies_count > 0 || $msg->replies->count() > 0)
                                <br><small class="text-info"><i class="fas fa-comments"></i> {{ $msg->replies->count() }} {{ Str::plural('reply', $msg->replies->count()) }}</small>
                            @endif
                        </td>
                        <td>
                            @if($msg->status === 'unread')
                                <span class="badge bg-primary"><i class="fas fa-envelope me-1"></i>New</span>
                            @elseif($msg->status === 'replied')
                                <span class="badge bg-success"><i class="fas fa-reply me-1"></i>Replied</span>
                            @else
                                <span class="badge bg-secondary"><i class="fas fa-envelope-open me-1"></i>Read</span>
                            @endif
                        </td>
                        <td class="text-muted small">
                            {{ $msg->created_at->diffForHumans() }}
                            @if($msg->last_reply_at)
                                <br><small class="text-info">Last reply: {{ $msg->last_reply_at->diffForHumans() }}</small>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group" role="group">
                                <a href="{{ route('website.admin.contact-messages.show', $msg->id) }}" class="btn btn-sm btn-outline-primary" title="View">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('website.admin.contact-messages.reply', $msg->id) }}" class="btn btn-sm btn-outline-success" title="Reply">
                                    <i class="fas fa-reply"></i>
                                </a>
                                @if($msg->is_archived)
                                    <form action="{{ route('website.admin.contact-messages.restore', $msg->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-info" title="Restore">
                                            <i class="fas fa-undo"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('website.admin.contact-messages.archive', $msg->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Archive">
                                            <i class="fas fa-archive"></i>
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('website.admin.contact-messages.destroy', $msg->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this message?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fas fa-inbox fa-3x mb-3 d-block"></i>
                            @if(request('archive') === 'archived')
                                No archived messages.
                            @else
                                No messages found.
                            @endif
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($messages->hasPages())
            <div class="card-footer bg-white">
                {{ $messages->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
