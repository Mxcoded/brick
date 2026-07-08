@extends('layouts.master')

@section('title', "Locations - $store->name")

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 text-dark">Store Locations</h1>
            <p class="text-muted mb-0">{{ $store->name }} &mdash; Manage bins, zones, aisles, racks, and shelves.</p>
        </div>
        <a href="{{ route('inventory.locations.create', $store) }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add Location
        </a>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <table class="table table-hover mb-0">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Code</th>
                        <th>Zone</th>
                        <th>Aisle</th>
                        <th>Rack</th>
                        <th>Shelf</th>
                        <th>Items</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($locations as $loc)
                        <tr>
                            <td>{{ $loc->id }}</td>
                            <td><code>{{ $loc->code ?? '—' }}</code></td>
                            <td>{{ $loc->zone ?? '—' }}</td>
                            <td>{{ $loc->aisle ?? '—' }}</td>
                            <td>{{ $loc->rack ?? '—' }}</td>
                            <td>{{ $loc->shelf ?? '—' }}</td>
                            <td>{{ $loc->storeItems->count() }}</td>
                            <td>
                                <a href="{{ route('inventory.locations.edit', [$store, $loc]) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-edit"></i></a>
                                <form action="{{ route('inventory.locations.destroy', [$store, $loc]) }}" method="POST" class="d-inline" onsubmit="return confirm('Delete this location?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No locations defined for this store.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
