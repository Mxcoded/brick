@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('contracts.dashboard') }}">Contracts</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contracts.templates.index') }}">Templates</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contracts.templates.show', $template) }}">{{ $template->name }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('page-content')
<div class="container-fluid py-4 contracts-theme">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">Edit Template</h4>
            <span class="text-muted small">{{ $template->name }}</span>
        </div>
        <a href="{{ route('contracts.templates.show', $template) }}" class="btn btn-outline-secondary btn-sm"><i class="fas fa-arrow-left me-1"></i>Back to template</a>
    </div>

    @include('contracts::templates._form', ['template' => $template, 'types' => $types])
</div>
@endsection