@extends('layouts.master')

@section('title', 'Properties')

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-building me-2"></i>Properties</h4>
            <p class="text-muted mb-0">Manage multi-property hotels and branches</p>
        </div>
        <a href="{{ route('admin.properties.create') }}" class="btn btn-primary fw-bold">
            <i class="fas fa-plus me-1"></i> Add Property
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif

    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Name</th>
                            <th>Code</th>
                            <th>Location</th>
                            <th>Users</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($properties as $property)
                        <tr>
                            <td class="fw-bold">
                                {{ $property->name }}
                                @if($property->is_headquarters)
                                    <span class="badge bg-primary ms-1">HQ</span>
                                @endif
                            </td>
                            <td><code>{{ $property->code }}</code></td>
                            <td>{{ $property->city }}{{ $property->city && $property->state ? ', ' : '' }}{{ $property->state }}</td>
                            <td>{{ $property->users_count }}</td>
                            <td>
                                @if($property->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-secondary">Inactive</span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.properties.show', $property) }}" class="btn btn-sm btn-outline-primary"><i class="fas fa-eye"></i></a>
                                    <a href="{{ route('admin.properties.edit', $property) }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-edit"></i></a>
                                    <a href="{{ route('admin.properties.users', $property) }}" class="btn btn-sm btn-outline-info"><i class="fas fa-users"></i></a>
                                    @if(! $property->is_headquarters)
                                    <form action="{{ route('admin.properties.set-hq', $property) }}" method="POST" onsubmit="return confirm('Set as headquarters?')" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-warning" title="Set as HQ"><i class="fas fa-star"></i></button>
                                    </form>
                                    @endif
                                    @if(! $property->is_headquarters)
                                    <form action="{{ route('admin.properties.destroy', $property) }}" method="POST" onsubmit="return confirm('Delete this property?')" class="d-inline">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fas fa-trash"></i></button>
                                    </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-building fa-2x mb-2"></i>
                                <p class="mb-0">No properties yet. Create your first property to get started.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
