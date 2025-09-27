@extends('layouts.master')

@section('page-content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h4>Booking Sources</h4>
    <a href="{{ route('frontdesk.booking-sources.create') }}" class="btn btn-primary">Add New Source</a>
</div>

@if (session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
@endif
@if (session('error'))
    <div class="alert alert-danger">{{ session('error') }}</div>
@endif

<div class="card shadow-sm">
    <div class="card-body">
        <form method="GET" class="mb-3">
            <div class="row">
                <div class="col-md-4">
                    <select name="type" class="form-select">
                        <option value="">All Types</option>
                        <option value="online" {{ request('type') == 'online' ? 'selected' : '' }}>Online</option>
                        <option value="offline" {{ request('type') == 'offline' ? 'selected' : '' }}>Offline</option>
                        <option value="partner" {{ request('type') == 'partner' ? 'selected' : '' }}>Partner</option>
                    </select>
                </div>
                <div class="col-md-8">
                    <button type="submit" class="btn btn-outline-secondary">Filter</button>
                    <a href="{{ route('frontdesk.booking-sources.index') }}" class="btn btn-outline-secondary">Clear</a>
                </div>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Commission %</th>
                        <th>Bookings</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sources as $source)
                    <tr>
                        <td>{{ $source->name }}</td>
                        <td><span class="badge bg-secondary">{{ ucfirst($source->type ?? 'General') }}</span></td>
                        <td>{{ $source->commission_rate }}%</td>
                        <td>{{ $source->registrations_count }}</td>
                        <td>{{ $source->is_active ? '<span class="badge bg-success">Yes</span>' : '<span class="badge bg-warning">No</span>' }}</td>
                        <td>
                            <a href="{{ route('frontdesk.booking-sources.show', $source) }}" class="btn btn-sm btn-info">View</a>
                            <a href="{{ route('frontdesk.booking-sources.edit', $source) }}" class="btn btn-sm btn-warning">Edit</a>
                            <form action="{{ route('frontdesk.booking-sources.destroy', $source) }}" method="POST" class="d-inline">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete?')">Delete</button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="text-center">No sources yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        {{ $sources->appends(request()->query())->links() }}
    </div>
</div>
@endsection