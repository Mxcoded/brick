@extends('layouts.master')
@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Create New Order</li>
@endsection

@section('page-content')
<div class="container-fluid px-4">
    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-triangle me-2"></i><strong>Please fix the following errors:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-5">
        <h1 class="fw-bold display-5 text-gold">
            <i class="fas fa-utensils me-3"></i>Create New Banquet Order
        </h1>
        <a href="{{ route('banquet.orders.index') }}" class="btn btn-outline-charcoal">
            <i class="fas fa-arrow-left me-2"></i>Back to Orders
        </a>
    </div>

    @include('banquet::partials.customer-form', ['order' => null, 'customers' => $customers])
</div>
<style>
    .text-gold { color: #C8A165 !important; }
    .btn-outline-charcoal { color: #333333; border-color: #333333; }
    .btn-outline-charcoal:hover { background-color: #333333; color: white; }
</style>
@endsection