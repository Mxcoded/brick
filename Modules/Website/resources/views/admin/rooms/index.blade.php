@extends('website::layouts.admin')

@section('title', 'Manage Rooms')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">Manage Rooms</h1>
            <a href="{{ route('website.admin.rooms.create') }}" class="btn btn-primary">Add New Room</a>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if ($rooms->isEmpty())
                <p>No rooms found.</p>
            @else
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Price/Night</th>
                            <th>Capacity</th>
                            <th>Size</th>
                            <th>Featured</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($rooms as $room)
                            <tr>
                                <td>{{ $room->name }}</td>
                                <td>{{ number_format($room->price, 2) }}</td>
                                <td>{{ $room->capacity }}</td>
                                <td>{{ $room->size ?? 'N/A' }}</td>
                                <td>{{ $room->featured ? 'Yes' : 'No' }}</td>
                                <td>
                                    <a href="{{ route('website.admin.rooms.show', $room) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('website.admin.rooms.edit', $room) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('website.admin.rooms.destroy', $room) }}" method="POST" class="d-inline">
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