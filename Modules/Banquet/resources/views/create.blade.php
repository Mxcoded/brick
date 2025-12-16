@extends('layouts.master')
@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Create New Order</li>
@endsection

@section('page-content')
<div class="container-fluid px-4">
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