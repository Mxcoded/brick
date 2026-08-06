@extends('layouts.master')

@section('title', 'Manage Add-ons')

@section('page-content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">Manage Add-ons</h1>
            <a href="{{ route('website.admin.addons.create') }}" class="btn btn-primary">Add New Add-on</a>
        </div>
        <div class="card-body">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif
            @if ($addons->isEmpty())
                <p>No add-ons found. Add breakfast, airport pickup, late checkout and more to upsell at booking.</p>
            @else
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Price</th>
                            <th>Billing</th>
                            <th>Status</th>
                            <th>Bookings</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($addons as $addon)
                            <tr>
                                <td>
                                    <i class="{{ $addon->icon ?? 'fas fa-star' }} me-1"></i>
                                    {{ $addon->name }}
                                </td>
                                <td>&#8358;{{ number_format($addon->price, 2) }}</td>
                                <td>{{ $addon->is_per_night ? 'Per night' : 'One-time' }}</td>
                                <td>
                                    @if ($addon->is_active)
                                        <span class="badge bg-success">Active</span>
                                    @else
                                        <span class="badge bg-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $addon->bookings_count }}</td>
                                <td>
                                    <a href="{{ route('website.admin.addons.show', $addon) }}" class="btn btn-sm btn-info">View</a>
                                    <a href="{{ route('website.admin.addons.edit', $addon) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('website.admin.addons.destroy', $addon) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this add-on? Historical bookings keep their snapshot.')">Delete</button>
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
