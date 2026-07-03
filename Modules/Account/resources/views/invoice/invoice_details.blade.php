@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('account.invoice') }}">Invoices</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $invoice->invoice_number }}</li>
@endsection

@section('page-content')
    <div class="container-fluid my-4">
        <h1 class="mb-4 fw-bold text-dark">Invoice Details</h1>

        <!-- Main Employee Card -->
        <div class="card shadow-sm mb-5">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center py-3">
                <h5 class="mb-0 fw-semibold  text-white">{{ $invoice->order->guest->full_name ?? 'N/A' }}</h5>
                <span class="badge bg-light text-dark px-2 py-1">{{ $invoice->order->guest->contact_number ?? 'N/A' }}</span>
                <span class="badge bg-light text-dark px-2 py-1">{{ $invoice->order->guest->company_name ?? 'N/A' }}</span>
            </div>
            <div class="card-body p-4">
                <div class="row g-4">
                    
                    <div class="col-md-8">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-map-marker-alt me-2"></i> Invoice Number:</strong> 
                                    {{ $invoice->invoice_number ?? 'N/A' }}</p>
                                <p><strong><i class="fas fa-city me-2"></i> Total Amount Due:</strong> 
                                    {{ $invoice->amount_due ?? 'N/A' }}</p>
                                <p><strong><i class="fas fa-globe me-2"></i> Total Paid:</strong> 
                                    {{ $invoice->amount_paid ?? 'N/A' }}</p>
                                <p><strong><i class="fas fa-venus-mars me-2"></i> Status:</strong> 
                                    {{ $invoice->status ?? 'N/A' }}</p>
                                <p><strong><i class="fas fa-birthday-cake me-2"></i> Date of Issue:</strong> 
                                    {{ \Carbon\Carbon::parse($invoice->created_at)->format('d M, Y') ?? 'N/A' }}</p>
                                <p><strong><i class="fas fa-briefcase me-2"></i> Due Date:</strong> 
                                    {{ \Carbon\Carbon::parse($invoice->due_date)->format('d M, Y') ?? 'N/A' }}</p>
                            </div>
                            <div class="col-md-6">
                                <p><strong><i class="fas fa-flag me-2"></i> Order Number:</strong> 
                                    {{ $invoice->order->order_number ?? 'N/A' }}</p>
                                <p><strong><i class="fas fa-tint me-2"></i> Order Module:</strong> 
                                    {{ $invoice->order->module ?? 'N/A' }}</p>
                                <p><strong><i class="fas fa-tint me-2"></i> Order Status:</strong> 
                                    {{ $invoice->order->status ?? 'N/A' }}</p>
                                <p><strong><i class="fas fa-dna me-2"></i> Module Order ID:</strong> 
                                    {{ $invoice->order->module_order_id ?? 'N/A' }}</p>
                                <p><strong><i class="fas fa-phone me-2"></i> Order Sub Total:</strong> 
                                    {{ $invoice->order->sub_total ?? 'N/A' }}</p>
                                <p><strong><i class="fas fa-home me-2"></i>Order Tax:</strong> 
                                    {{ $invoice->order->tax ?? 'N/A' }}</p>
                                <p><strong><i class="fas fa-id-card me-2"></i> Order discount:</strong> 
                                    {{ $invoice->order->discount ?? 'N/A' }}</p>
                                <p>
                                    <strong><i class="fas fa-bank me-2"></i> Order Total:</strong>
                                        <span id="bvn-display">
                                            {{ $invoice->order->total ?? 'N/A' }}
                                        </span>
                                        
                                
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="mt-4 row g-4">
                    <div class="col-md-6">
                        <h5 class="border-bottom pb-2 fw-semibold text-dark">
                            <i class="fas fa-user-friends me-2"></i> Order Items
                        </h5>
                        <div class="table-responsive">
                            <table class="table table-hover table-bordered align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">Item Name</th>
                                        <th scope="col">Quantity</th>
                                        <th scope="col">Price</th>
                                        <th scope="col">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($invoice->order->items as $item)
                                        <tr>
                                            <td>{{ $item->itemable->name ?? 'N/A' }}</td>
                                            <td>{{ $item->quantity ?? 'N/A' }}</td>
                                            <td>{{ $item->price ?? 'N/A' }}</td>
                                            <td>{{ $item->total ?? 'N/A' }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                    </div>
                    
                </div>

                <div class="mt-4">
                    <h5 class="border-bottom pb-2 fw-semibold text-dark">
                        <i class="fas fa-file-alt me-2"></i> CV
                    </h5>
                        <a href="#" target="_blank"
                            class="btn btn-sm btn-primary">
                            <i class="fas fa-download me-1"></i> Download CV
                        </a>
                  
                </div>
            </div>
        </div>

       

      
        <div class="mt-5 d-flex justify-content-between flex-wrap gap-3">
            <a href="{{ route('staff.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Staff List
            </a>
            <a href="#" class="btn btn-primary">
                <i class="fas fa-edit me-1"></i> Edit Staff
            </a>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .card {
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            border-radius: 8px 8px 0 0;
        }

        .table th,
        .table td {
            vertical-align: middle;
        }

        .badge {
            font-size: 0.9rem;
            padding: 0.4em 0.6em;
        }

        h5 {
            font-weight: 600;
            color: #343a40;
        }

        p {
            margin-bottom: 0.75rem;
        }

        .btn-link {
            color: #495057;
            text-decoration: none;
        }

        .btn-link:hover {
            color: #0d6efd;
        }

        @media (max-width: 767.98px) {
            .text-center img, .text-center div {
                margin: 0 auto;
            }
        }
    </style>
@endsection

