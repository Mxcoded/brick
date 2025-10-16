@extends('frontdeskcrm::layouts.master') {{-- Or your main public-facing layout --}}

@section('title', 'Submission Received')

@section('page-content')
<div class="container my-5 text-center">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-body py-5">
                    <i class="fas fa-check-circle text-success fa-4x mb-3"></i>
                    <h2 class="card-title">Thank You!</h2>
                    <p class="lead">Your check-in information has been submitted successfully.</p>
                    <p>Please proceed to the front desk to finalize your check-in and receive your room key.</p>
                    <hr>
                    <a href="{{ route('frontdesk.registrations.create') }}" class="btn btn-outline-primary mt-3">Start a New Check-in</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
