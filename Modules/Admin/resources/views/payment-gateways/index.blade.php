@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active" aria-current="page">Payment Gateways</li>
@endsection

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-charcoal mb-0"><i class="fas fa-credit-card me-2"></i> Payment Gateways</h3>
        <a href="{{ route('admin.payment-gateways.create') }}" class="btn btn-primary">
            <i class="fas fa-plus me-1"></i> Add Gateway
        </a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show">{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show">{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Driver</th>
                        <th>Status</th>
                        <th>Default</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($gateways as $gateway)
                        <tr>
                            <td class="fw-semibold">{{ $gateway->name }}</td>
                            <td><code>{{ $gateway->code }}</code></td>
                            <td>{{ $gateway->driver }}</td>
                            <td>
                                @if ($gateway->is_active)
                                    <span class="badge bg-success">Active</span>
                                @else
                                    <span class="badge bg-gold">Inactive</span>
                                @endif
                            </td>
                            <td>
                                @if ($gateway->is_default)
                                    <span class="badge bg-primary">Default</span>
                                @else
                                    <form action="{{ route('admin.payment-gateways.set-default', $gateway) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-primary">Set Default</button>
                                    </form>
                                @endif
                            </td>
                            <td class="text-end">
                                <form action="{{ route('admin.payment-gateways.toggle', $gateway) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-gold">
                                        {{ $gateway->is_active ? 'Deactivate' : 'Activate' }}
                                    </button>
                                </form>
                                <a href="{{ route('admin.payment-gateways.edit', $gateway) }}" class="btn btn-sm btn-outline-dark">Edit</a>
                                <form action="{{ route('admin.payment-gateways.destroy', $gateway) }}" method="POST" class="d-inline"
                                      onsubmit="return confirm('Delete this gateway?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger">Delete</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No payment gateways configured yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
