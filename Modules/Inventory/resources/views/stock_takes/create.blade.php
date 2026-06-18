@extends('layouts.master')

@section('title', 'New Stock Take')

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 text-dark">New Stock Take</h1>
            <p class="text-muted mb-0">Select a store to begin a physical inventory count.</p>
        </div>
        <a href="{{ route('inventory.stock-takes.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back
        </a>
    </div>

    <div class="card shadow border-0">
        <div class="card-body p-4">
            <form method="POST" action="{{ route('inventory.stock-takes.store') }}">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold">Store</label>
                    <select name="store_id" class="form-select" required>
                        <option value="">Select a store...</option>
                        @foreach ($stores as $store)
                            <option value="{{ $store->id }}">{{ $store->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Notes (optional)</label>
                    <textarea name="notes" class="form-control" rows="2" placeholder="Reason for count, shift notes, etc."></textarea>
                </div>
                <button type="submit" class="btn btn-primary px-4">
                    <i class="fas fa-play me-2"></i>Start Stock Take
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
