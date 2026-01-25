@extends('website::layouts.master')

@section('title', 'Manage My Booking')

@section('content')
<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-5">
                    <div class="text-center mb-4">
                        <div class="bg-light rounded-circle d-inline-flex p-3 mb-3">
                            <i class="fas fa-search fa-2x text-primary"></i>
                        </div>
                        <h3 class="fw-bold">Find My Booking</h3>
                        <p class="text-muted">Enter your booking details to view confirmation or print receipt.</p>
                    </div>

                    @if(session('error'))
                        <div class="alert alert-danger">{{ session('error') }}</div>
                    @endif

                    <form action="{{ route('website.booking.find') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Booking Reference</label>
                            <input type="text" name="booking_reference" class="form-control form-control-lg text-uppercase" placeholder="e.g. BK-25-X9Y2Z" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Email Address</label>
                            <input type="email" name="email" class="form-control form-control-lg" placeholder="email@example.com" required>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 btn-lg fw-bold">
                            Find Booking
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection