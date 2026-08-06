@extends('layouts.master')

@section('title', 'View Add-on')

@section('page-content')
    <div class="card">
        <div class="card-header">
            <h1 class="h3 mb-0">View Add-on</h1>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Name</dt>
                <dd class="col-sm-9">{{ $addon->name }}</dd>
                <dt class="col-sm-3">Slug</dt>
                <dd class="col-sm-9"><code>{{ $addon->slug }}</code></dd>
                <dt class="col-sm-3">Description</dt>
                <dd class="col-sm-9">{{ $addon->description ?: 'None' }}</dd>
                <dt class="col-sm-3">Price</dt>
                <dd class="col-sm-9">&#8358;{{ number_format($addon->price, 2) }}</dd>
                <dt class="col-sm-3">Billing</dt>
                <dd class="col-sm-9">{{ $addon->is_per_night ? 'Per night' : 'One-time' }}</dd>
                <dt class="col-sm-3">Icon</dt>
                <dd class="col-sm-9"><i class="{{ $addon->icon ?? 'fas fa-star' }}"></i> {{ $addon->icon ?? 'None' }}</dd>
                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">
                    @if ($addon->is_active)
                        <span class="badge bg-success">Active</span>
                    @else
                        <span class="badge bg-secondary">Inactive</span>
                    @endif
                </dd>
                <dt class="col-sm-3">Times Booked</dt>
                <dd class="col-sm-9">{{ $addon->bookings_count }}</dd>
                <dt class="col-sm-3">Sort Order</dt>
                <dd class="col-sm-9">{{ $addon->sort_order }}</dd>
            </dl>
            <a href="{{ route('website.admin.addons.edit', $addon) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('website.admin.addons.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
@endsection
