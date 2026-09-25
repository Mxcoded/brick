@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('contracts.dashboard') }}">Contracts</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contracts.agreements.index') }}">Agreements</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contracts.agreements.show', $agreement) }}">{{ $agreement->agreement_number }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('page-content')
<div class="container-fluid py-4 contracts-theme">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Edit Agreement — {{ $agreement->agreement_number }}</h4>
            <span class="text-muted small">Editing is possible while the agreement is not executed or terminal.</span>
        </div>
        <a href="{{ route('contracts.agreements.show', $agreement) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to agreement</a>
    </div>

    @include('contracts::agreements._form', ['agreement' => $agreement, 'templates' => $templates, 'types' => $types])
</div>
@endsection