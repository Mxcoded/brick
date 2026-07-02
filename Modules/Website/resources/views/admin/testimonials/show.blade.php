@extends('layouts.master')

@section('title', 'View Testimonial')

@section('page-content')
    <div class="card">
        <div class="card-header">
            <h1 class="h3 mb-0">View Testimonial</h1>
        </div>
        <div class="card-body">
            <dl class="row">
                <dt class="col-sm-3">Guest Name</dt>
                <dd class="col-sm-9">
                    @if ($testimonial->guest_image)
                        <img src="{{ $testimonial->guest_image }}" class="rounded-circle me-2" width="32" height="32" alt="">
                    @endif
                    {{ $testimonial->guest_name }}
                </dd>
                <dt class="col-sm-3">Rating</dt>
                <dd class="col-sm-9">
                    @for ($i = 0; $i < 5; $i++)
                        <i class="fas fa-star{{ $i < $testimonial->rating ? '' : '-empty' }} text-warning"></i>
                    @endfor
                </dd>
                <dt class="col-sm-3">Review</dt>
                <dd class="col-sm-9">{{ $testimonial->text }}</dd>
                <dt class="col-sm-3">Stay Type</dt>
                <dd class="col-sm-9">{{ $testimonial->stay_type ?? 'N/A' }}</dd>
                <dt class="col-sm-3">Status</dt>
                <dd class="col-sm-9">
                    @if ($testimonial->approved)
                        <span class="badge bg-success">Approved</span>
                    @else
                        <span class="badge bg-secondary">Pending</span>
                    @endif
                </dd>
            </dl>
            <a href="{{ route('website.admin.testimonials.edit', $testimonial) }}" class="btn btn-warning">Edit</a>
            <a href="{{ route('website.admin.testimonials.index') }}" class="btn btn-secondary">Back</a>
        </div>
    </div>
@endsection
