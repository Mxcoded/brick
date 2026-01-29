@extends('website::layouts.master')

@section('title', 'Dining & Menu')

@section('content')
{{-- Hero Section --}}
<div class="position-relative py-5 bg-dark" style="background: url('{{ asset('images/dining-hero.jpg') }}') center/cover no-repeat; min-height: 400px;">
    <div class="position-absolute top-0 start-0 w-100 h-100 bg-dark opacity-75"></div>
    <div class="container position-relative z-2 h-100 d-flex flex-column justify-content-center text-center text-white">
        <h1 class="display-4 fw-bold font-heading animate__animated animate__fadeInDown">Exquisite Dining</h1>
        <p class="lead animate__animated animate__fadeInUp">Savor the finest flavors in a luxurious setting</p>
    </div>
</div>

<div class="container py-5">
    <div class="row g-4 justify-content-center">
        @forelse($diningOptions as $dining)
            <div class="col-lg-10">
                <div class="card border-0 shadow-sm overflow-hidden rounded-4 mb-3">
                    <div class="row g-0">
                        <div class="col-md-5 position-relative">
                            @if($dining->image_url)
                                <img src="{{ $dining->image_url }}" class="img-fluid h-100 w-100 object-fit-cover" style="min-height: 300px;" alt="{{ $dining->name }}">
                            @else
                                <div class="bg-light h-100 d-flex align-items-center justify-content-center text-muted">
                                    <i class="fas fa-utensils fa-3x opacity-25"></i>
                                </div>
                            @endif
                        </div>
                        <div class="col-md-7">
                            <div class="card-body p-4 p-lg-5 d-flex flex-column h-100 justify-content-center">
                                <div class="mb-2">
                                    <h3 class="card-title fw-bold text-primary font-heading mb-1">{{ $dining->name }}</h3>
                                    @if($dining->cuisine_type)
                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill mb-2">{{ $dining->cuisine_type }}</span>
                                    @endif
                                </div>
                                
                                <p class="card-text text-muted mb-4 lead" style="font-size: 1rem;">
                                    {{ $dining->description }}
                                </p>
                                
                                <div class="d-flex flex-wrap gap-4 text-sm text-muted mb-4 border-top pt-3">
                                    @if($dining->opening_hours)
                                        <div class="d-flex align-items-center">
                                            <i class="far fa-clock text-primary me-2"></i>
                                            <span>{{ $dining->opening_hours }}</span>
                                        </div>
                                    @endif
                                    @if($dining->dress_code)
                                        <div class="d-flex align-items-center">
                                            <i class="fas fa-tshirt text-primary me-2"></i>
                                            <span>{{ $dining->dress_code }}</span>
                                        </div>
                                    @endif
                                </div>

                                @if($dining->menu_link)
                                    <div>
                                        <a href="{{ $dining->menu_link }}" target="_blank" class="btn btn-outline-primary rounded-pill px-4">
                                            <i class="fas fa-book-open me-2"></i>View Menu
                                        </a>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 text-center py-5">
                <div class="text-muted opacity-50 mb-3">
                    <i class="fas fa-utensils fa-3x"></i>
                </div>
                <h3>Coming Soon</h3>
                <p>We are currently updating our dining options.</p>
            </div>
        @endforelse
    </div>
</div>
@endsection