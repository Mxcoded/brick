@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('contracts.dashboard') }}">Contracts</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contracts.templates.index') }}">Templates</a></li>
    <li class="breadcrumb-item active" aria-current="page">New Template</li>
@endsection

@section('page-content')
<div class="container-fluid py-4 contracts-theme">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">New Template</h4>
            <span class="text-muted small">Clauses become editable starting points for new agreements.</span>
        </div>
        <a href="{{ route('contracts.templates.index') }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Templates</a>
    </div>

    @include('contracts::templates._form', ['types' => $types])
</div>
@endsection