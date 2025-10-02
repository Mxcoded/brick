@extends('layouts.master')

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Guest Types</h4>
    <a href="{{ route('frontdesk.guest-types.create') }}" class="btn btn-primary">Add New Type</a>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card shadow-sm" style="background: var(--glass-effect); border: 1px solid var(--glass-border);">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Description</th>
                        <th>Discount %</th>
                        <th>Registrations</th>
                        <th>Revenue</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($types as $type)
                    <tr>
                        <td><span class="badge" style="background-color: {{ $type->color }}; color: white;">{{ $type->name }}</span></td>
                        <td>{{ Str::limit($type->description, 50) }}</td>
                        <td>{{ $type->discount_rate }}%</td>
                        <td>{{ $type->registrations_count }}</td>
                        <td>&#8358;{{ number_format($type->total_revenue, 2) }}</td>
                        <td>{!! $type->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning">No</span>' !!}</td>
                        <td>
                            <a href="{{ route('frontdesk.guest-types.show', $type) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('frontdesk.guest-types.edit', $type) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('frontdesk.guest-types.destroy', $type) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete? This will affect reports.')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="7" class="text-center">No guest types yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $types->links() }}
    </div>
</div>
@endsection