@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('banquet.index') }}">Banquet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('banquet.customers.index') }}">Customers</a></li>
    <li class="breadcrumb-item active">{{ $customer->name }}</li>
@endsection

@section('page-content')
<div class="container-fluid py-4 banquet-theme">
    
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

    {{-- Page Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold text-charcoal">
                <i class="fas fa-user me-2 text-gold"></i>{{ $customer->name }}
            </h1>
            <p class="text-muted mb-0">{{ $customer->organization ?? 'Private Customer' }}</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('banquet.customers.edit', $customer->id) }}" class="btn btn-gold">
                <i class="fas fa-edit me-2"></i>Edit
            </a>
            <a href="{{ route('banquet.customers.index') }}" class="btn btn-outline-charcoal">
                <i class="fas fa-arrow-left me-2"></i>Back
            </a>
        </div>
    </div>

    <div class="row g-4">
        {{-- Customer Info Card --}}
        <div class="col-md-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-gold text-white py-3">
                    <h5 class="card-title mb-0"><i class="fas fa-id-card me-2"></i>Contact Information</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <label class="small text-muted">Full Name</label>
                        <p class="mb-0 fw-bold">{{ $customer->name }}</p>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted">Email Address</label>
                        <p class="mb-0">
                            <a href="mailto:{{ $customer->email }}" class="text-gold">
                                <i class="fas fa-envelope me-1"></i>{{ $customer->email }}
                            </a>
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted">Phone Number</label>
                        <p class="mb-0">
                            <a href="tel:{{ $customer->phone }}" class="text-gold">
                                <i class="fas fa-phone me-1"></i>{{ $customer->phone }}
                            </a>
                        </p>
                    </div>
                    <div class="mb-3">
                        <label class="small text-muted">Organization</label>
                        <p class="mb-0">{{ $customer->organization ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <label class="small text-muted">Customer Since</label>
                        <p class="mb-0">{{ $customer->created_at->format('F d, Y') }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statistics --}}
        <div class="col-md-8">
            <div class="row g-3 mb-4">
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100 bg-gold text-white">
                        <div class="card-body text-center">
                            <h3 class="fw-bold mb-0">{{ number_format($stats['total_orders']) }}</h3>
                            <small class="opacity-75">Total Orders</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <h3 class="fw-bold mb-0 text-success">₦{{ number_format($stats['total_spent']) }}</h3>
                            <small class="text-muted">Total Spent</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <h3 class="fw-bold mb-0 text-primary">₦{{ number_format($stats['avg_order_value']) }}</h3>
                            <small class="text-muted">Avg Order</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body text-center">
                            <h6 class="fw-bold mb-0 text-charcoal">
                                {{ $stats['last_order'] ? $stats['last_order']->format('M d, Y') : 'N/A' }}
                            </h6>
                            <small class="text-muted">Last Order</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Recent Orders --}}
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-gold"><i class="fas fa-history me-2"></i>Recent Orders</h5>
                </div>
                <div class="card-body">
                    @if($customer->banquetOrders->isEmpty())
                        <div class="text-center py-4">
                            <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No orders yet</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light">
                                    <tr>
                                        <th>Order ID</th>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th class="text-end">Revenue</th>
                                        <th class="text-end">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customer->banquetOrders as $order)
                                        <tr>
                                            <td>
                                                <span class="fw-bold text-gold">#{{ $order->order_id }}</span>
                                            </td>
                                            <td>{{ $order->preparation_date->format('M d, Y') }}</td>
                                            <td>
                                                @php
                                                    $statusColors = ['Pending' => 'warning', 'Confirmed' => 'primary', 'Completed' => 'success', 'Cancelled' => 'danger'];
                                                @endphp
                                                <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                                    {{ $order->status }}
                                                </span>
                                            </td>
                                            <td class="text-end fw-bold">₦{{ number_format($order->total_revenue) }}</td>
                                            <td class="text-end">
                                                <a href="{{ route('banquet.orders.show', $order->order_id) }}" class="btn btn-sm btn-outline-primary">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .banquet-theme { font-family: 'Proxima Nova', Arial, Helvetica, sans-serif; }
    .text-gold { color: #C8A165 !important; }
    .text-charcoal { color: #333333 !important; }
    .bg-gold { background-color: #C8A165 !important; }
    .btn-gold { background-color: #C8A165; border-color: #C8A165; color: #FFFFFF; }
    .btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: #FFFFFF; }
    .btn-outline-charcoal { color: #333333; border-color: #333333; }
    .btn-outline-charcoal:hover { background-color: #333333; color: #FFFFFF; }
</style>
@endsection
