@extends('website::layouts.master')

@section('title', 'Booking Details')

@section('content')
    <section class="py-5 bg-light">
        <div class="container">
            {{-- Progress Indicator --}}
            @include('website::partials.booking-progress', ['step' => 4])

            <div class="row justify-content-center">
                <div class="col-lg-8">
                    
                    {{-- ✅ NO-PRINT AREA: Navigation/Success Messages --}}
                    <div class="d-print-none mb-4">
                        @if(session('success'))
                            <div class="alert alert-success border-0 shadow-sm rounded-3 d-flex align-items-center">
                                <i class="fas fa-check-circle fa-lg me-3"></i>
                                <div>
                                    <h5 class="mb-0 fw-bold">Success!</h5>
                                    <p class="mb-0 small">{{ session('success') }}</p>
                                </div>
                            </div>
                        @endif
                        
                        <a href="{{ route('website.home') }}" class="btn btn-outline-secondary btn-sm">
                            <i class="fas fa-arrow-left me-2"></i> Return to Home
                        </a>
                    </div>

                    {{-- ✅ PRINTABLE CARD: The Actual Receipt --}}
                    <div class="card border-0 shadow-sm rounded-3 overflow-hidden" id="printableArea">
                        
                        {{-- Header (Branded) --}}
                        <div class="card-header bg-white border-bottom p-4 d-flex justify-content-between align-items-center">
                            <div>
                                <h5 class="fw-bold text-dark mb-0 text-uppercase">Booking @if($booking->status === 'confirmed')Receipt @else Invoice @endif</h5>
                                Payment Status: <span class="badge {{ $booking->status === 'confirmed' ? 'bg-success' : 'bg-danger' }}">
                                    @if($booking->status === 'confirmed') Paid @else Pay on Arrival @endif
                                </span>
                            </div>
                            <div class="text-end">
                                <small class="text-muted d-block">Reference</small>
                                <span class="h4 fw-bold text-primary">{{ $booking->booking_reference }}</span>
                            </div>
                        </div>

                        <div class="card-body p-5">
                            
                            {{-- Guest Info --}}
                            <div class="text-center mb-5">
                                <h2 class="fw-bold text-dark mb-1">Hello, {{ $booking->guest_name }}</h2>
                                <p class="text-muted">
                                    <i class="fas fa-envelope me-1"></i> {{ $booking->guest_email }} &bull; 
                                    <i class="fas fa-phone ms-1 me-1"></i> {{ $booking->guest_phone }}
                                </p>
                            </div>

                            {{-- Stay Details Grid --}}
                            <div class="row g-4 mb-5 p-4 bg-light rounded-3 border">
                                <div class="col-6 col-md-3 text-center border-end">
                                    <small class="text-muted text-uppercase fw-bold d-block mb-1">Check-in</small>
                                    <span class="fw-bold text-dark fs-5">{{ $booking->check_in_date->format('M d') }}</span>
                                    <small class="d-block text-muted">{{ $booking->check_in_date->format('Y') }}</small>
                                </div>
                                <div class="col-6 col-md-3 text-center border-end-md">
                                    <small class="text-muted text-uppercase fw-bold d-block mb-1">Check-out</small>
                                    <span class="fw-bold text-dark fs-5">{{ $booking->check_out_date->format('M d') }}</span>
                                    <small class="d-block text-muted">{{ $booking->check_out_date->format('Y') }}</small>
                                </div>
                                <div class="col-6 col-md-3 text-center border-end">
                                    <small class="text-muted text-uppercase fw-bold d-block mb-1">Nights</small>
                                    <span class="fw-bold text-dark fs-5">
                                        {{ $booking->check_in_date->diffInDays($booking->check_out_date) ?: 1 }}
                                    </span>
                                </div>
                                <div class="col-6 col-md-3 text-center">
                                    <small class="text-muted text-uppercase fw-bold d-block mb-1">Guests</small>
                                    <span class="fw-bold text-dark fs-5">{{ $booking->adults }} Ad, {{ $booking->children }} Ch</span>
                                </div>
                            </div>

                            {{-- Room & Payment --}}
                            <div class="mb-4">
                                <table class="table table-borderless">
                                    <tbody>
                                        <tr>
                                            <td class="ps-0">
                                                <span class="fw-bold text-dark d-block">{{ $booking->room->name ?? 'Room' }}</span>
                                                <small class="text-muted">Accommodation Charge</small>
                                            </td>
                                            <td class="text-end fw-bold pe-0 align-middle">
                                                ₦{{ number_format($booking->total_amount, 2) }}
                                            </td>
                                        </tr>
                                        @if($booking->payment_status === 'paid')
                                        <tr>
                                            <td class="ps-0 text-success"><i class="fas fa-check-circle me-1"></i> Amount Paid</td>
                                            <td class="text-end text-success fw-bold pe-0">- ₦{{ number_format($booking->amount_paid, 2) }}</td>
                                        </tr>
                                        @endif
                                        <tr class="border-top">
                                            <td class="ps-0 pt-3 h5 fw-bold">Total Due</td>
                                            <td class="text-end pt-3 h4 fw-bold text-primary pe-0">
                                                ₦{{ number_format($booking->total_amount - ($booking->amount_paid ?? 0), 2) }}
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- Email Controls (Hidden when Printing) --}}
                            <div class="d-print-none mt-5 pt-4 border-top">
                                <p class="text-muted small mb-2"><i class="fas fa-info-circle me-1"></i> Need a copy?</p>
                                <div class="d-flex gap-2">
                                    {{-- 1. Resend Email Button --}}
                                    <form action="{{ route('website.booking.resend') }}" method="POST" class="d-inline">
                                        @csrf
                                        <input type="hidden" name="booking_reference" value="{{ $booking->booking_reference }}">
                                        <button type="submit" class="btn btn-light btn-sm text-muted border">
                                            <i class="fas fa-envelope me-1"></i> Email Receipt
                                        </button>
                                    </form>

                                    {{-- 2. Print Button (THE NEW FEATURE) --}}
                                    <button onclick="window.print()" class="btn btn-dark btn-sm">
                                        <i class="fas fa-print me-1"></i> Print / Save PDF
                                    </button>
                                </div>
                            </div>
                        </div>

                        {{-- Footer for Print --}}
                        <div class="card-footer bg-light p-4 text-center border-top">
                            <small class="text-muted">
                                <strong>Brickspoint ApartHotel</strong> &bull; 24 Jose Marti Crescent, Asokoro, Abuja
                                <br> +234 (809) 999-9627 &bull; rsv@brickspoint.com
                            </small>
                        </div>
                    </div>

                    {{-- Bottom Actions --}}
                    <div class="d-print-none mt-4 text-center">
                        <div class="d-flex justify-content-center gap-3 flex-wrap">
                            @auth
                                <a href="{{ route('guest.bookings') }}" class="btn btn-primary px-4 shadow-sm">
                                    <i class="fas fa-list me-2"></i>View My Bookings
                                </a>
                                <a href="{{ route('website.rooms.index') }}" class="btn btn-outline-primary px-4">
                                    <i class="fas fa-plus me-2"></i>Book Another Stay
                                </a>
                            @else
                                <a href="{{ route('website.rooms.index') }}" class="btn btn-primary px-4 shadow-sm">
                                    <i class="fas fa-plus me-2"></i>Book Another Stay
                                </a>
                                <a href="{{ route('website.home') }}" class="btn btn-outline-secondary px-4">
                                    <i class="fas fa-home me-2"></i>Return Home
                                </a>
                            @endauth
                        </div>
                        @guest
                            <p class="text-muted small mt-3 mb-0">
                                <i class="fas fa-user-plus me-1"></i> 
                                <a href="{{ route('register') }}" class="text-primary">Create an account</a> to manage your bookings easily
                            </p>
                        @endguest
                    </div>

                </div>
            </div>
        </div>
    </section>

    {{-- Print CSS --}}
    <style>
        @media print {
            body * { visibility: hidden; }
            #printableArea, #printableArea * { visibility: visible; }
            #printableArea { position: absolute; left: 0; top: 0; width: 100%; border: none !important; box-shadow: none !important; }
            .d-print-none { display: none !important; }
            .bg-light { background-color: white !important; }
        }
    </style>
@endsection