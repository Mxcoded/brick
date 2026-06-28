@extends('layouts.master')

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Charge Types</h4>
    <a href="{{ route('frontdesk.charge-types.create') }}" class="btn btn-primary">Add Charge Type</a>
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
                        <th>Code</th>
                        <th>Name</th>
                        <th>Icon</th>
                        <th>Description</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($chargeTypes as $type)
                    <tr>
                        <td><code>{{ $type->code }}</code></td>
                        <td>{{ $type->name }}</td>
                        <td>@if($type->icon) <i class="{{ $type->icon }}"></i> @else — @endif</td>
                        <td>{{ Str::limit($type->description, 50) }}</td>
                        <td>{!! $type->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning">No</span>' !!}</td>
                        <td>
                            <a href="{{ route('frontdesk.charge-types.edit', $type) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('frontdesk.charge-types.destroy', $type) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete {{ $type->name }}?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center">No charge types yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $chargeTypes->links() }}
    </div>
</div>
@endsection
