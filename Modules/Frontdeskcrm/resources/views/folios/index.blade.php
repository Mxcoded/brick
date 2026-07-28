@extends('layouts.master')

@section('title', "Folios - {$registration->full_name}")
@section('page-content')

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="mb-0 fw-bold">Folios: {{ $registration->full_name }}</h4>
        <a href="{{ route('frontdesk.registrations.show', $registration) }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>Back to Registration
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @forelse($folios as $folio)
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <div>
                <strong>{{ $folio->folio_name }}</strong>
                <span class="ms-3 text-muted small">{{ $folio->folio_number }}</span>
                <span class="ms-2 badge bg-{{ $folio->status === 'open' ? 'success' : ($folio->status === 'closed' ? 'secondary' : 'danger') }}">
                    {{ ucfirst($folio->status) }}
                </span>
            </div>
            <div>
                <span class="fw-bold me-3">Balance: {{ number_format($folio->balance, 2) }}</span>
                <a href="{{ route('frontdesk.folios.show', $folio) }}" class="btn btn-sm btn-outline-primary">View</a>
            </div>
        </div>
        <div class="card-body p-0">
            <table class="table table-sm mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th class="text-end">Amount</th>
                        <th class="text-end">Tax</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($folio->items as $item)
                    <tr>
                        <td>{{ $item->post_date->format('M d, Y') }}</td>
                        <td>{{ ucfirst($item->charge_type) }}</td>
                        <td>{{ $item->description ?? '—' }}</td>
                        <td class="text-end">{{ number_format($item->amount, 2) }}</td>
                        <td class="text-end">{{ number_format($item->tax_amount, 2) }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center text-muted py-3">No items on this folio.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @empty
    <div class="alert alert-info">No folios found for this registration.</div>
    @endforelse
</div>
@endsection
