@extends('layouts.master')

@section('title', "Edit Location - $store->name")

@section('page-content')
<div class="container-fluid p-4">
    <div class="mb-4">
        <h1 class="display-5 text-dark">Edit Location</h1>
        <p class="text-muted mb-0">{{ $store->name }} &mdash; {{ $location->display_name }}</p>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('inventory.locations.update', [$store, $location]) }}">
                @csrf @method('PUT')
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Code <small class="text-muted">(e.g., A-01-03)</small></label>
                        <input type="text" name="code" class="form-control" value="{{ old('code', $location->code) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Zone</label>
                        <input type="text" name="zone" class="form-control" value="{{ old('zone', $location->zone) }}" placeholder="e.g., Dry Storage">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Aisle</label>
                        <input type="text" name="aisle" class="form-control" value="{{ old('aisle', $location->aisle) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Rack</label>
                        <input type="text" name="rack" class="form-control" value="{{ old('rack', $location->rack) }}">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Shelf</label>
                        <input type="text" name="shelf" class="form-control" value="{{ old('shelf', $location->shelf) }}">
                    </div>
                    <div class="col-12">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control" rows="2">{{ old('notes', $location->notes) }}</textarea>
                    </div>
                </div>
                <div class="mt-4">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save me-2"></i>Update Location</button>
                    <a href="{{ route('inventory.locations.index', $store) }}" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
