@extends('website::layouts.admin')

@section('title', 'View Contact Message')

@section('content')
    <div class="card">
        <div class="card-header">
            <h1 class="h3 mb-0">View Contact Message</h1>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Name</dt>
                <dd class="col-sm-9">{{ $contactMessage->name }}</dd>
                <dt class="col-sm-3">Email</dt>
                <dd class="col-sm-9">{{ $contactMessage->email }}</dd>
                <dt class="col-sm-3">Message</dt>
                <dd class="col-sm-9">{{ $contactMessage->message }}</dd>
                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">{{ ucfirst($contactMessage->status) }}</dd>
                <dt class="col-sm-3">Received</dt>
                <dd class="col-sm-9">{{ $contactMessage->created_at->format('d M Y, H:i') }}</dd>
            </dl>
            <form action="{{ route('website.admin.contact-messages.update', $contactMessage) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="mb-3">
                    <label for="status" class="form-label">Status</label>
                    <select class="form-control" id="status" name="status">
                        <option value="unread" {{ $contactMessage->status === 'unread' ? 'selected' : '' }}>Unread</option>
                        <option value="read" {{ $contactMessage->status === 'read' ? 'selected' : '' }}>Read</option>
                        <option value="replied" {{ $contactMessage->status === 'replied' ? 'selected' : '' }}>Replied</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">Update Status</button>
            </form>
            <a href="{{ route('website.admin.contact-messages.index') }}" class="btn btn-secondary mt-2">Back</a>
        </div>
    </div>
@endsection