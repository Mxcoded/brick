@extends('layouts.master')

@section('title', 'Manage Dining')

@section('page-content')
<div class="container-fluid py-4">
    
    {{-- ✅ ADDED: Notification Alert --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dining Options</h1>
        <a href="{{ route('website.admin.dining.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Add New Dining
        </a>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Image</th>
                            <th>Name</th>
                            <th>Opening Hours</th>
                            <th>Description</th>
                            <th class="text-end pe-4">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($diningOptions as $dining)
                        <tr>
                            <td class="ps-4">
                                @if($dining->image_url)
                                    {{-- Added timestamp to force browser cache refresh on update --}}
                                    <img src="{{ $dining->image_url }}?t={{ time() }}" class="rounded" style="width: 60px; height: 60px; object-fit: cover;">
                                @else
                                    <span class="badge bg-secondary">No Image</span>
                                @endif
                            </td>
                            <td class="fw-bold">{{ $dining->name }}</td>
                            <td>{{ $dining->opening_hours ?? 'N/A' }}</td>
                            <td class="text-muted small text-truncate" style="max-width: 200px;">
                                {{ Str::limit($dining->description, 50) }}
                            </td>
                            <td class="text-end pe-4">
                                <a href="{{ route('website.admin.dining.edit', $dining->id) }}" class="btn btn-sm btn-outline-primary me-1">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <form action="{{ route('website.admin.dining.destroy', $dining->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center py-5 text-muted">No dining options found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white">
            {{ $diningOptions->links() }}
        </div>
    </div>
</div>
@endsection