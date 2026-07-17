@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Admin</a></li>
    <li class="breadcrumb-item active" aria-current="page">Modules</li>
@endsection

@section('page-content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-charcoal mb-0"><i class="fas fa-cubes me-2"></i> Module Manager</h3>
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
        <div class="card-header bg-white py-3">
            <h5 class="mb-0 fw-bold">{{ count($allModules) }} Modules Installed</h5>
        </div>
        <div class="card-body p-0">
            <table class="table table-hover mb-0 align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>Module</th>
                        <th>Status</th>
                        <th>Version</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($allModules as $module)
                        <tr>
                            <td class="fw-semibold">
                                <i class="fas fa-cube me-2 text-muted"></i> {{ $module->getName() }}
                            </td>
                            <td>
                                @if ($module->isEnabled())
                                    <span class="badge bg-success">Enabled</span>
                                @else
                                    <span class="badge bg-secondary">Disabled</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $module->get('version', '—') }}</td>
                            <td>
                                <form action="{{ route('admin.modules.toggle', $module->getName()) }}" method="POST" class="d-inline">
                                    @csrf
                                    @if ($module->isEnabled())
                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Disable {{ $module->getName() }}? This will hide all its routes and menus.')">
                                            <i class="fas fa-power-off me-1"></i> Disable
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-outline-success">
                                            <i class="fas fa-check-circle me-1"></i> Enable
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection