@extends('layouts.master')

@section('title', 'View Testimonial')

@section('page-content')
    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h1 class="h3 mb-0">View Testimonial</h1>
            <div class="d-flex gap-2">
                <form action="{{ route('website.admin.testimonials.toggle-approve', $testimonial) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn {{ $testimonial->approved ? 'btn-warning' : 'btn-success' }}">
                        <i class="fas {{ $testimonial->approved ? 'fa-times-circle' : 'fa-check-circle' }} me-1"></i>
                        {{ $testimonial->approved ? 'Unapprove' : 'Approve' }}
                    </button>
                </form>
                <a href="{{ route('website.admin.testimonials.edit', $testimonial) }}" class="btn btn-primary">
                    <i class="fas fa-edit me-1"></i> Edit
                </a>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <dl class="row">
                        <dt class="col-sm-3">Guest Name</dt>
                        <dd class="col-sm-9">
                            <div class="d-flex align-items-center">
                                @if ($testimonial->guest_image)
                                    <img src="{{ $testimonial->guest_image }}" class="rounded-circle me-2" width="32" height="32" alt="">
                                @else
                                    <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold me-2" style="width:32px;height:32px;background:rgba(200,161,101,0.2);color:#C8A165;font-size:0.85rem;">{{ substr($testimonial->guest_name, 0, 1) }}</div>
                                @endif
                                <span class="fw-semibold">{{ $testimonial->guest_name }}</span>
                            </div>
                        </dd>
                        <dt class="col-sm-3">Email</dt>
                        <dd class="col-sm-9">{{ $testimonial->email ?? 'N/A' }}</dd>
                        <dt class="col-sm-3">Rating</dt>
                        <dd class="col-sm-9">
                            @for ($i = 0; $i < 5; $i++)
                                <i class="fa{{ $i < $testimonial->rating ? 's' : 'r' }} fa-star text-warning"></i>
                            @endfor
                        </dd>
                        <dt class="col-sm-3">Type</dt>
                        <dd class="col-sm-9">
                            <span class="badge rounded-pill px-3"
                                  style="background: {{ $testimonial->type === 'restaurant' ? 'rgba(40,167,69,0.12)' : ($testimonial->type === 'event' ? 'rgba(0,123,255,0.12)' : 'rgba(200,161,101,0.12)') }};
                                         color: {{ $testimonial->type === 'restaurant' ? '#28a745' : ($testimonial->type === 'event' ? '#007bff' : 'var(--theme-primary-dark)') }};">
                                {{ $testimonial->typeLabel() }}
                            </span>
                        </dd>
                        <dt class="col-sm-3">{{ $testimonial->type === 'restaurant' ? 'Dining Venue' : ($testimonial->type === 'event' ? 'Event Name' : 'Stay Type') }}</dt>
                        <dd class="col-sm-9">{{ $testimonial->stay_type ?? $testimonial->dining_venue ?? $testimonial->event_name ?? 'N/A' }}</dd>
                        <dt class="col-sm-3">Status</dt>
                        <dd class="col-sm-9">
                            @if ($testimonial->approved)
                                <span class="badge bg-success px-3 py-2 rounded-pill fs-6 fw-normal">
                                    <i class="fas fa-check-circle me-1"></i> Approved
                                </span>
                            @else
                                <span class="badge bg-warning text-dark px-3 py-2 rounded-pill fs-6 fw-normal">
                                    <i class="fas fa-clock me-1"></i> Pending
                                </span>
                            @endif
                        </dd>
                    </dl>
                    <div class="row">
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-2"><i class="fas fa-clipboard-list me-2 text-muted"></i>Ratings Breakdown</h6>
                            <div class="p-3 bg-light rounded mb-3">
                                @foreach ($testimonial->categoryRatings() as $label => $value)
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <small class="fw-semibold">{{ $label }}</small>
                                        @if ($value)
                                            <span>
                                                @for ($i = 0; $i < 5; $i++)
                                                    <i class="fa{{ $i < $value ? 's' : 'r' }} fa-star text-warning" style="font-size:0.8rem"></i>
                                                @endfor
                                            </span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-semibold mb-2"><i class="fas fa-wifi me-2 text-muted"></i>Wi-Fi / Client Session</h6>
                            <div class="p-3 bg-light rounded mb-3">
                                <dl class="row mb-0 small">
                                    <dt class="col-sm-5">Location / Branch</dt>
                                    <dd class="col-sm-7">{{ $testimonial->location ?? 'N/A' }}</dd>
                                    <dt class="col-sm-5">Access Point</dt>
                                    <dd class="col-sm-7">{{ $testimonial->ap_name ?: 'N/A' }} <small class="text-muted">{{ $testimonial->ap_mac }}</small></dd>
                                    <dt class="col-sm-5">SSID</dt>
                                    <dd class="col-sm-7">{{ $testimonial->ssid ?: 'N/A' }}</dd>
                                    <dt class="col-sm-5">Client MAC</dt>
                                    <dd class="col-sm-7">{{ $testimonial->client_mac ?: 'N/A' }}</dd>
                                    <dt class="col-sm-5">Client IP</dt>
                                    <dd class="col-sm-7">{{ $testimonial->client_ip ?: 'N/A' }}</dd>
                                    <dt class="col-sm-5">Connected At</dt>
                                    <dd class="col-sm-7">{{ optional($testimonial->created_at)->format('d M Y, H:i') }}</dd>
                                    <dt class="col-sm-5">Portal Session</dt>
                                    <dd class="col-sm-7 text-break">{{ $testimonial->portal_session ?: 'N/A' }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 mt-3">
                    <h5 class="fw-semibold mb-2">Review</h5>
                    <div class="p-3 bg-light rounded">
                        <p class="mb-0">"{{ $testimonial->text }}"</p>
                    </div>
                </div>
            </div>
            <div class="mt-4">
                <a href="{{ route('website.admin.testimonials.index') }}" class="btn btn-outline-secondary">
                    <i class="fas fa-arrow-left me-1"></i> Back to List
                </a>
            </div>
        </div>
    </div>
@endsection
