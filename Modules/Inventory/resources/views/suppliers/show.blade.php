@extends('layouts.master')

@section('title', $supplier->name)

@section('page-content')
<div class="container-fluid p-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="display-5 text-dark">{{ $supplier->name }}</h1>
            <p class="text-muted mb-0">Supplier details and contact information.</p>
        </div>
        <a href="{{ route('inventory.suppliers.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left me-2"></i>Back to Suppliers
        </a>
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0 fw-bold">Contact Information</h5></div>
                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Contact Person</dt>
                        <dd class="col-sm-8">{{ $supplier->contact_person ?? '—' }}</dd>
                        <dt class="col-sm-4">Email</dt>
                        <dd class="col-sm-8">{{ $supplier->email ?? '—' }}</dd>
                        <dt class="col-sm-4">Phone</dt>
                        <dd class="col-sm-8">{{ $supplier->phone ?? '—' }}</dd>
                        <dt class="col-sm-4">Address</dt>
                        <dd class="col-sm-8">{{ $supplier->address ?? '—' }}</dd>
                    </dl>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card shadow border-0 mb-4">
                <div class="card-header bg-white border-bottom"><h5 class="mb-0 fw-bold">Items Supplied</h5></div>
                <div class="card-body">
                    @php $items = $supplier->items; @endphp
                    @if ($items->count() > 0)
                        <ul class="list-group list-group-flush">
                            @foreach ($items as $item)
                                <li class="list-group-item px-0 d-flex justify-content-between">
                                    <span>{{ $item->description }}</span>
                                    <span class="text-muted">{{ $item->storeItems->sum('quantity') }} in stock</span>
                                </li>
                            @endforeach
                        </ul>
                    @else
                        <p class="text-muted mb-0">No items from this supplier.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
