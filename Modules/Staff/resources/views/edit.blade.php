@extends('staff::layouts.master')

@section('content')
<div class="container">
    <h1>Edit Staff</h1>
     <!-- Back Button -->
    <div class="mt-4">
        <a href="{{ route('staff.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Back to Staff List
        </a>
    </div>
    <form action="{{ route('staff.update', $employee->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

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
        <h5 class="card-title mb-0">Employment History</h5>
    </div>
    <div class="card-body">
        <div id="employment-history-container">
            @foreach($employee->employmentHistories as $index => $history)
                @include('staff::partials.employment_history_form', [
                    'index' => $index,
                    'history' => $history
                ])
            @endforeach
        </div>
        <button type="button" id="add-employment-history" class="btn btn-sm btn-success">
            <i class="fas fa-plus"></i> Add Another Employment History
        </button>
    </div>
</div>

<!-- Educational Background Section -->
<div class="card mb-4">
    <div class="card-header bg-secondary text-white">
        <h5 class="card-title mb-0">Educational Background</h5>
    </div>
    <div class="card-body">
        <div id="educational-background-container">
            @foreach($employee->educationalBackgrounds as $index => $education)
                @include('staff::partials.educational_background_form', [
                    'index' => $index,
                    'education' => $education
                ])
            @endforeach
        </div>
        <button type="button" id="add-educational-background" class="btn btn-sm btn-success">
            <i class="fas fa-plus"></i> Add Another Educational Background
        </button>
    </div>
</div>

        <!-- Submit Button -->
        <button type="submit" class="btn btn-primary">Update</button>
    </form>
</div>

<!-- JavaScript for Dynamic Form Fields -->
@section('scripts')
    <script src="{{ asset('js/staff/dynamic-forms.js') }}"></script>
@endsection