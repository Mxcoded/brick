@extends('layouts.master')

@section('title', 'Inbox')

@section('page-content')
<div class="container-fluid py-4">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">Inbox</h1>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('website.admin.contact-messages.index') }}" method="GET" class="row g-3">
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
                    </select>
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
                        <th>Subject</th>
                        <th>Date</th>
                        <th class="text-end pe-4">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($messages as $msg)
                    <tr class="{{ $msg->is_read ? '' : 'fw-bold bg-light' }}">
                        <td class="ps-4">
                            {{ $msg->name }} <br>
                            <small class="text-muted fw-normal">{{ $msg->email }}</small>
                        </td>
                        <td>
                            {{ Str::limit($msg->subject, 30) }} 
                            <span class="text-muted fw-normal mx-2">-</span> 
                            <small class="text-muted fw-normal">{{ Str::limit($msg->message, 40) }}</small>
                        </td>
                        <td class="text-muted small">
                            {{ $msg->created_at->diffForHumans() }}
                        </td>
                        <td class="text-end pe-4">
                            <a href="{{ route('website.admin.contact-messages.show', $msg->id) }}" class="btn btn-sm btn-outline-primary">
                                View
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="text-center py-5 text-muted">No messages found.</td>
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