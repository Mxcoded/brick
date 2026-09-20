@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('contracts.dashboard') }}">Contracts</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contracts.templates.index') }}">Templates</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $template->name }}</li>
@endsection

@php
    $type = \Modules\Contracts\Enums\AgreementType::from($template->type);
@endphp

@section('page-content')
<div class="container-fluid py-4 contracts-theme">

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

    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
        <div class="d-flex align-items-center gap-2">
            <h4 class="fw-bold mb-0">{{ $template->name }}</h4>
            <span class="badge {{ $template->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $template->is_active ? 'Active' : 'Inactive' }}</span>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('contracts.agreements.create') }}?template={{ $template->id }}" class="btn btn-gold btn-sm"><i class="fas fa-file-contract me-1"></i>Start agreement from template</a>
            <a href="{{ route('contracts.templates.edit', $template) }}" class="btn btn-outline-primary btn-sm"><i class="fas fa-edit me-1"></i>Edit</a>
        </div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Template info</div>
                <div class="card-body small">
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Type</span><span class="fw-semibold">{{ $type->label() }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Clauses</span><span class="fw-semibold">{{ $template->clauses->count() }}</span></div>
                    <div class="d-flex justify-content-between mb-2"><span class="text-muted">Used by</span><span class="fw-semibold">{{ $template->agreements()->count() }} agreement(s)</span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted">Created</span><span class="fw-semibold">{{ $template->created_at?->format('d M Y') }}</span></div>
                    @if ($template->description)
                        <hr>
                        <div>{{ $template->description }}</div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white fw-semibold">Standard Clauses</div>
                <div class="card-body">
                    @forelse ($template->clauses as $clause)
                        <div class="mb-3">
                            <h6 class="fw-semibold mb-1">{{ $loop->iteration }}. {{ $clause->title }}</h6>
                            <div class="text-muted small" style="white-space: pre-wrap;">{{ $clause->content }}</div>
                        </div>
                    @empty
                        <div class="text-muted small">No clauses in this template.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<style>
    .contracts-theme { font-family: 'Proxima Nova', Arial, Helvetica, sans-serif; }
    .btn-gold { background-color: #C8A165; border-color: #C8A165; color: #FFFFFF; }
    .btn-gold:hover { background-color: #b08d55; border-color: #b08d55; color: #FFFFFF; }
</style>
@endsection