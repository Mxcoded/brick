@extends('layouts.master')

@section('title', $property->name)

@section('page-content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="mb-1 fw-bold"><i class="fas fa-building me-2"></i>{{ $property->name }}</h4>
            <p class="text-muted mb-0">{{ $property->city }}{{ $property->city && $property->state ? ', ' : '' }}{{ $property->state }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('frontdesk.properties.edit', $property) }}" class="btn btn-outline-secondary"><i class="fas fa-edit me-1"></i> Edit</a>
            <a href="{{ route('frontdesk.properties.index') }}" class="btn btn-light"><i class="fas fa-arrow-left me-1"></i> Back</a>
        </div>
    </div>

    <div class="row g-3 mb-4">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-3 fw-bold"><i class="fas fa-info-circle me-2"></i>Details</div>
                <div class="card-body">
                    <table class="table table-sm mb-0">
                        <tr><td style="width:140px">Code</td><td><code>{{ $property->code }}</code></td></tr>
                        <tr><td>Slug</td><td><code>{{ $property->slug }}</code></td></tr>
                        <tr><td>Address</td><td>{{ $property->address ?? '—' }}</td></tr>
                        <tr><td>City / State</td><td>{{ $property->city ?? '—' }}{{ $property->city && $property->state ? ', ' : '' }}{{ $property->state ?? '' }}</td></tr>
                        <tr><td>Country</td><td>{{ $property->country }}</td></tr>
                        <tr><td>Contact</td><td>{{ $property->contact_email ?? '—' }}{{ $property->contact_email && $property->contact_phone ? ' / ' : '' }}{{ $property->contact_phone ?? '' }}</td></tr>
                        <tr><td>Currency</td><td>{{ $property->currency }}</td></tr>
                        <tr><td>Timezone</td><td>{{ $property->timezone }}</td></tr>
                        <tr><td>Status</td>
                            <td>
                                @if($property->is_active) <span class="badge bg-success">Active</span> @else <span class="badge bg-secondary">Inactive</span> @endif
                                @if($property->is_headquarters) <span class="badge bg-primary">Headquarters</span> @endif
                            </td>
                        </tr>
                        <tr><td>Users Assigned</td><td>{{ $property->users_count }}</td></tr>
                        <tr><td>Created</td><td>{{ $property->created_at->format('M d, Y') }}</td></tr>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-0 py-3 fw-bold"><i class="fas fa-users me-2"></i>Recent Users</div>
                <div class="card-body">
                    @if($recentUsers->isNotEmpty())
                    <ul class="list-unstyled mb-0">
                        @foreach($recentUsers as $user)
                        <li class="py-1"><i class="fas fa-user me-2 text-muted"></i>{{ $user->name }}</li>
                        @endforeach
                    </ul>
                    @else
                    <p class="text-muted mb-0">No users assigned.</p>
                    @endif
                    <a href="{{ route('frontdesk.properties.users', $property) }}" class="btn btn-sm btn-outline-primary mt-3">Manage Users</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
