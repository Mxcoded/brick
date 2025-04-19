@extends('website::layouts.admin')

@section('title', 'Manage Amenities')

@section('content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">Manage Amenities</h1>
            <a href="{{ route('website.admin.amenities.create') }}" class="btn btn-primary">Add New Amenity</a>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if ($amenities->isEmpty())
                <p>No amenities found.</p>
            @else
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Icon</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($amenities as $amenity)
                            <tr>
                                <td>{{ $amenity->name }}</td>
                                <td><i class="{{ $amenity->icon ?? 'fas fa-question' }}"></i> {{ $amenity->icon ?? 'None' }}</td>
                                <td>
                                    <a href="{{ route('website.admin.amenities.show', $amenity) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('website.admin.amenities.edit', $amenity) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('website.admin.amenities.destroy', $amenity) }}" method="POST" class="d-inline">
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