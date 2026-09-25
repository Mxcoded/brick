@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('contracts.dashboard') }}">Contracts</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contracts.agreements.index') }}">Agreements</a></li>
    <li class="breadcrumb-item active" aria-current="page">New Agreement</li>
@endsection

@section('page-content')
<div class="container-fluid py-4 contracts-theme">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">New Agreement</h4>
            <span class="text-muted small">Created as a draft — review it, then move it through the workflow.</span>
        </div>
        <a href="{{ route('contracts.agreements.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Agreements</a>
    </div>

    @include('contracts::agreements._form', ['templates' => $templates, 'types' => $types, 'selectedTemplate' => $selectedTemplate])
</div>
@endsection