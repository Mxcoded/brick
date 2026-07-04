@extends('restaurant::layouts.adminMaster')
@section('title', "Order #{$order->id}")
@section('admin-content')
    <div class="container-fluid">
        <div class="mb-4">
            <a href="{{ route('restaurant.admin.dashboard') }}" class="btn btn-outline-secondary rounded-pill btn-sm">
                <i class="bi bi-arrow-left me-1"></i>Back to Dashboard
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="row g-4 mb-4">
            <div class="col-lg-7">
                <div class="card shadow-sm border-0 rounded-4 h-100">
                    <div class="card-header bg-light py-3 d-flex justify-content-between align-items-center">
                        <h3 class="fw-bold mb-0">Order #{{ $order->id }}</h3>
                        @php
                            $statusBadge = match($order->status) {
                                'pending' => 'bg-warning text-dark',
                                'accepted' => 'bg-info',
                                'completed' => 'bg-success',
                                'rejected' => 'bg-danger',
                                'void' => 'bg-dark',
                                default => 'bg-light text-dark',
                            };
                        @endphp
                        <span class="badge {{ $statusBadge }} fs-6">{{ ucfirst($order->status) }}</span>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3 mb-4">
                            <div class="col-md-6">
                                <small class="text-muted d-block">Source</small>
                                <strong>
                                    @switch($order->type)
                                        @case('walk_in')
                                            {{ $order->customer_name ?: 'Walk-in' }}
                                            @break
                                        @case('table')
                                            Table {{ $order->source_id }}
                                            @break
                                        @case('room')
                                            Room {{ $order->source_id }}
                                            @break
                                        @case('online')
                                            Online
                                            @break
                                        @default
                                            {{ ucfirst($order->type) }}
                                    @endswitch
                                </strong>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Date</small>
                                <strong>{{ $order->created_at->format('d M Y, H:i') }}</strong>
                            </div>
                            @if($order->customer_name)
                            <div class="col-md-6">
                                <small class="text-muted d-block">Customer Name</small>
                                <strong>{{ $order->customer_name }}</strong>
                            </div>
                            @endif
                            @if($order->customer_phone)
                            <div class="col-md-6">
                                <small class="text-muted d-block">Phone</small>
                                <strong>{{ $order->customer_phone }}</strong>
                            </div>
                            @endif
                            @if($order->delivery_address)
                            <div class="col-12">
                                <small class="text-muted d-block">Delivery Address</small>
                                <strong>{{ $order->delivery_address }}</strong>
                            </div>
                            @endif
                            @if($order->reason)
                            <div class="col-12">
                                <small class="text-muted d-block">Reason</small>
                                <strong class="text-danger">{{ $order->reason }}</strong>
                            </div>
                            @endif
                            @if($order->shift)
                            <div class="col-md-6">
                                <small class="text-muted d-block">Shift</small>
                                <strong>{{ $order->shift->clock_in ? $order->shift->clock_in->format('d M Y, H:i') : 'N/A' }}</strong>
                            </div>
                            <div class="col-md-6">
                                <small class="text-muted d-block">Cashier</small>
                                <strong>{{ $order->shift->user?->name ?? 'N/A' }}</strong>
                            </div>
                            @endif
                        </div>

                        <h5 class="fw-bold mb-3">Items</h5>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead class="table-light">
                                    <tr>
                                        <th>Item</th>
                                        <th>Price</th>
                                        <th>Qty</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->orderItems as $item)
                                    <tr>
                                        <td>
                                            <strong>{{ $item->menuItem?->name ?? 'Deleted Item' }}</strong>
                                            @if($item->instructions)
                                                <br><small class="text-muted">{{ $item->instructions }}</small>
                                            @endif
                                        </td>
                                        <td>₦{{ number_format($item->menuItem?->price ?? 0, 2) }}</td>
                                        <td>{{ $item->quantity }}</td>
                                        <td class="text-end fw-bold">₦{{ number_format(($item->menuItem?->price ?? 0) * $item->quantity, 2) }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="row justify-content-end mt-3">
                            <div class="col-md-5">
                                <table class="table table-borderless mb-0">
                                    <tr>
                                        <td>Subtotal</td>
                                        <td class="text-end">₦{{ number_format($order->subtotal, 2) }}</td>
                                    </tr>
                                    @if($order->discount > 0)
                                    <tr>
                                        <td>
                                            Discount
                                            @if($order->discount_type === 'percentage')
                                                <span class="text-muted">(%)</span>
                                            @endif
                                        </td>
                                        <td class="text-end text-danger">
                                            -₦{{ number_format($order->discount, 2) }}
                                        </td>
                                    </tr>
                                    @endif
                                    @if($order->vat_rate > 0)
                                    <tr>
                                        <td>VAT ({{ number_format($order->vat_rate, 1) }}%)</td>
                                        <td class="text-end">₦{{ number_format($order->vat, 2) }}</td>
                                    </tr>
                                    @endif
                                    <tr class="border-top">
                                        <th class="fw-bold">Grand Total</th>
                                        <th class="text-end fw-bold fs-5">₦{{ number_format($order->grand_total, 2) }}</th>
                                    </tr>
                                    @if($order->relationLoaded('payments') && $order->payments->isNotEmpty())
                                    <tr>
                                        <td>Paid via</td>
                                        <td class="text-end">{{ ucfirst(str_replace('_', ' ', $order->payments->first()->method)) }}</td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="card shadow-sm border-0 rounded-4">
                    <div class="card-header bg-light py-3">
                        <h3 class="fw-bold mb-0">Update Status</h3>
                    </div>
                    <div class="card-body p-4">
                        <form action="{{ route('restaurant.admin.order.update', $order->id) }}" method="POST">
                            @csrf
                            <div class="mb-3">
                                <label for="status" class="form-label fw-medium">Order Status</label>
                                <select name="status" id="status" class="form-select form-select-lg">
                                    @foreach($statuses as $s)
                                        <option value="{{ $s }}" {{ $order->status === $s ? 'selected' : '' }}>
                                            {{ ucfirst($s) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            @if($order->type === 'online')
                            <div class="mb-3">
                                <label for="tracking_status" class="form-label fw-medium">Tracking Status</label>
                                <select name="tracking_status" id="tracking_status" class="form-select form-select-lg">
                                    <option value="">—</option>
                                    <option value="pending" {{ $order->tracking_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                    <option value="preparing" {{ $order->tracking_status === 'preparing' ? 'selected' : '' }}>Preparing</option>
                                    <option value="delivered" {{ $order->tracking_status === 'delivered' ? 'selected' : '' }}>Delivered</option>
                                </select>
                            </div>
                            @endif

                            <button type="submit" class="btn button w-100 py-2 fw-bold mt-3">
                                <i class="bi bi-check-lg me-1"></i>Update Order
                            </button>
                        </form>
                    </div>
                </div>

                <div class="card shadow-sm border-0 rounded-4 mt-4">
                    <div class="card-header bg-light py-3">
                        <h3 class="fw-bold mb-0">Order Timeline</h3>
                    </div>
                    <div class="card-body p-4">
                        <ul class="list-unstyled mb-0">
                            <li class="mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-success rounded-pill me-2"><i class="bi bi-clock"></i></span>
                                    <div>
                                        <strong>Created</strong>
                                        <div class="small text-muted">{{ $order->created_at->format('d M Y, H:i') }}</div>
                                    </div>
                                </div>
                            </li>
                            @if($order->status === 'accepted' || $order->status === 'completed')
                            <li class="mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-info rounded-pill me-2"><i class="bi bi-check"></i></span>
                                    <div>
                                        <strong>Accepted</strong>
                                        <div class="small text-muted">
                                            @if($order->updated_at && $order->updated_at != $order->created_at)
                                                {{ $order->updated_at->format('d M Y, H:i') }}
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </li>
                            @endif
                            @if($order->status === 'completed')
                            <li class="mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-primary rounded-pill me-2"><i class="bi bi-check-all"></i></span>
                                    <div>
                                        <strong>Completed</strong>
                                        <div class="small text-muted">{{ $order->updated_at->format('d M Y, H:i') }}</div>
                                    </div>
                                </div>
                            </li>
                            @endif
                            @if($order->status === 'rejected')
                            <li class="mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-danger rounded-pill me-2"><i class="bi bi-x"></i></span>
                                    <div>
                                        <strong>Rejected</strong>
                                        <div class="small text-muted">{{ $order->updated_at->format('d M Y, H:i') }}</div>
                                    </div>
                                </div>
                            </li>
                            @endif
                            @if($order->status === 'void')
                            <li class="mb-3">
                                <div class="d-flex align-items-center">
                                    <span class="badge bg-dark rounded-pill me-2"><i class="bi bi-ban"></i></span>
                                    <div>
                                        <strong>Voided</strong>
                                        <div class="small text-muted">{{ $order->updated_at->format('d M Y, H:i') }}</div>
                                    </div>
                                </div>
                            </li>
                            @endif
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endSection
