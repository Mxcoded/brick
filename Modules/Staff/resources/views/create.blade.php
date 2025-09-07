@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Add New Staff</li>
@endsection

@section('page-content')
    <div class="container-fluid my-4">
        <h1>Add New Staff</h1>
        <div class="mt-4">
            <a href="{{ route('staff.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left me-1"></i> Back to Staff List
            </a>
        </div>
        <form action="{{ route('staff.store') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Personal Details Section -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="card-title mb-0">Personal Details</h5>
                </div>
                <div class="card-body">
                    @include('staff::partials.personal_details_form')
                </div>
            </div>

            <!-- Employment History Section -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">Employment History (Optional)</h5>
                </div>
                <div class="card-body">
                    <div id="employment-history-container">
                        @include('staff::partials.employment_history_form', ['index' => 0])
                    </div>
                    <button type="button" id="add-employment-history" class="btn btn-sm btn-success">
                        <i class="fas fa-plus me-1"></i> Add Another Employment History
                    </button>
                </div>
            </div>

            <!-- Educational Background Section -->
            <div class="card mb-4">
                <div class="card-header bg-secondary text-white">
                    <h5 class="card-title mb-0">Educational Background (Optional)</h5>
                </div>
                <div class="card-body">
                    <div id="educational-background-container">
                        @include('staff::partials.educational_background_form', ['index' => 0])
                    </div>
                    <button type="button" id="add-educational-background" class="btn btn-sm btn-success">
                        <i class="fas fa-plus me-1"></i> Add Another Educational Background
                    </button>
                </div>
            </div>

            <!-- Submit Button -->
            <button type="submit" class="btn btn-primary"><i class="fas fa-save me-1"></i> Submit</button>
        </form>
    </div>
@endsection

@section('page-scripts')
    <script src="{{ asset('js/staff/dynamic-forms.js') }}"></script>
@endsection