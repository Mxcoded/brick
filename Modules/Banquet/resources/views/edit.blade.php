@extends('layouts.master')
@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Update Banquet order</li>
@endsection

@section('page-content')
<div class="container-fluid my-4">
    <div class="d-flex justify-content-between align-items-center mb-5">
        <h1 class="fw-bold display-5 text-gold">
            <i class="fas fa-utensils me-3"></i>Update Banquet Order
        </h1>
        <a href="{{ route('banquet.orders.show', $order->order_id) }}" class="btn btn-outline-charcoal">
            <i class="fas fa-arrow-left me-2"></i>Back to Order
        </a>
    </div>

    @include('banquet::partials.customer-form', ['order' => $order, 'customers' => $customers])
</div>
<style>
    .text-gold { color: #C8A165 !important; }
    .btn-outline-charcoal { color: #333333; border-color: #333333; }
    .btn-outline-charcoal:hover { background-color: #333333; color: white; }
</style>
@endsection