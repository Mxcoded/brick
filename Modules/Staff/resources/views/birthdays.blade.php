@extends('layouts.master')

@section('page-content')
<div class="container-fluid">
    <div class="row mb-4">
        <div class="col-md-12 d-flex justify-content-between align-items-center">
            <div>
                <h1 class="h3 mb-0 text-gray-800">Staff Birthdays</h1>
                <p class="text-muted">Upcoming birthdays for the next 12 months</p>
            </div>
            <a href="{{ route('staff.index') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        </div>
    </div>

    <div class="row">
        @if($paginatedBirthdays->isEmpty())
            <div class="col-12 text-center py-5">
                <div class="text-muted">No active staff records found.</div>
            </div>
        @else
            @php $currentMonth = null; @endphp

            {{-- LOOP THROUGH THE PAGINATED ITEMS --}}
            @foreach($paginatedBirthdays as $staff)
                @php
                    $monthName = $staff->next_birthday->format('F Y');
                @endphp

                {{-- Group Header for Each Month --}}
                @if($currentMonth !== $monthName)
                    <div class="col-12 mb-3 mt-2">
                        <h5 class="border-bottom pb-2 text-primary font-weight-bold">
                            <i class="far fa-calendar-alt me-2"></i> {{ $monthName }}
                        </h5>
                    </div>
                    @php $currentMonth = $monthName; @endphp
                @endif

                {{-- Staff Card --}}
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        {{ $staff->next_birthday->format('l, jS') }} 
                                        @if($staff->next_birthday->isToday())
                                            <span class="badge bg-success ms-2">Today!</span>
                                        @endif
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $staff->name }}</div>
                                    <div class="text-muted small">{{ $staff->position }}</div>
                                    <div class="text-muted small mt-1">
                                        <i class="fas fa-birthday-cake text-danger"></i> Turning {{ $staff->turning_age }}
                                    </div>
                                </div>
                                <div class="col-auto">
                                    @if($staff->profile_image)
                                        <img src="{{ asset('storage/' . $staff->profile_image) }}" 
                                             class="rounded-circle" 
                                             style="width: 50px; height: 50px; object-fit: cover;" 
                                             alt="{{ $staff->name }}">
                                    @else
                                        <div class="rounded-circle bg-gray-200 d-flex align-items-center justify-content-center" 
                                             style="width: 50px; height: 50px;">
                                            <i class="fas fa-user text-gray-400 fa-lg"></i>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    {{-- PAGINATION LINKS --}}
    <div class="row mt-3">
        <div class="col-12 d-flex justify-content-end">
            {{-- We explicitly ask for Bootstrap 5 pagination styles if your app uses Bootstrap --}}
            {{ $paginatedBirthdays->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection