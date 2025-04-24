@extends('website::layouts.admin')

@section('title', 'Manage Contact Messages')

@section('content')
    <div class="card">
        <div class="card-header">
            <h1 class="h3 mb-0">Manage Contact Messages</h1>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if ($messages->isEmpty())
                <p>No messages found.</p>
            @else
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Message</th>
                            <th>Status</th>
                            <th>Received</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($messages as $message)
                            <tr>
                                <td>{{ $message->name }}</td>
                                <td>{{ $message->email }}</td>
                                <td>{{ Str::limit($message->message, 50) }}</td>
                                <td>
                                    <span class="badge bg-{{ $message->status === 'unread' ? 'warning' : ($message->status === 'read' ? 'info' : 'success') }}">
                                        {{ ucfirst($message->status) }}
                                    </span>
                                </td>
                                <td>{{ $message->created_at->format('d M Y') }}</td>
                                <td>
                                    <a href="{{ route('website.admin.contact-messages.show', $message) }}" class="btn btn-sm btn-info">View</a>
                                    <form action="{{ route('website.admin.contact-messages.destroy', $message) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    </div>
@endsection