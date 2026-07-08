@extends('layouts.master')

@section('title', 'Pre-Arrival Details')

@section('page-content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-4">
        <div class="mb-3 mb-md-0">
            <div class="d-flex align-items-center">
                <div class="rounded-circle bg-light p-2 me-3">
                    <i class="fas fa-plane-arrival fa-lg text-gold"></i>
                </div>
                <div>
                    <h3 class="mb-1 text-dark fw-bold">Pre-Arrival: {{ $registration->guest?->full_name ?? $registration->full_name }}</h3>
                    <p class="text-muted mb-0">
                        Reservation: <strong>{{ $registration->reservation_code }}</strong>
                        @if ($registration->pre_arrival_completed_at)
                            <span class="badge bg-success ms-2">Pre-Arrival Completed</span>
                        @else
                            <span class="badge bg-warning text-dark ms-2">Pending</span>
                        @endif
                    </p>
                </div>
            </div>
        </div>
        <a href="{{ route('frontdesk.pre-arrivals.index') }}" class="btn btn-outline-dark">
            <i class="fas fa-arrow-left me-1"></i> Back to List
        </a>
    </div>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="alert alert-success border-0 bg-success bg-opacity-10 border-start border-3 border-success rounded-2 mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-check-circle text-success me-2"></i>
                {{ session('success') }}
            </div>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger border-0 bg-danger bg-opacity-10 border-start border-3 border-danger rounded-2 mb-4">
            <div class="d-flex align-items-center">
                <i class="fas fa-exclamation-circle text-danger me-2"></i>
                {{ session('error') }}
            </div>
        </div>
    @endif

    <div class="row">
        {{-- Left Column --}}
        <div class="col-lg-5 mb-4">
            {{-- Guest Details --}}
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header border-0 bg-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-light p-2 me-3">
                            <i class="fas fa-id-card text-gold"></i>
                        </div>
                        <h5 class="mb-0 text-dark fw-bold">Guest Details</h5>
                    </div>
                </div>
                <div class="card-body">
                    @php $guest = $registration->guest; @endphp
                    <div class="mb-3">
                        <small class="text-muted text-uppercase fw-bold">Contact</small>
                        <ul class="list-unstyled mb-0 mt-2">
                            <li class="mb-2 d-flex">
                                <i class="fas fa-user text-muted mt-1 me-3" style="width: 16px;"></i>
                                <span class="fw-bold">{{ $guest?->full_name ?? $registration->full_name }}</span>
                            </li>
                            <li class="mb-2 d-flex">
                                <i class="fas fa-envelope text-muted mt-1 me-3" style="width: 16px;"></i>
                                <span>{{ $guest?->email ?? 'N/A' }}</span>
                            </li>
                            <li class="mb-2 d-flex">
                                <i class="fas fa-phone text-muted mt-1 me-3" style="width: 16px;"></i>
                                <span>{{ $guest?->contact_number ?? 'N/A' }}</span>
                            </li>
                        </ul>
                    </div>
                    <div>
                        <small class="text-muted text-uppercase fw-bold">Emergency Contact</small>
                        <ul class="list-unstyled mb-0 mt-2">
                            <li class="mb-2 d-flex">
                                <i class="fas fa-user-shield text-muted mt-1 me-3" style="width: 16px;"></i>
                                <span>{{ $guest?->emergency_name ?? 'N/A' }}</span>
                            </li>
                            <li class="d-flex">
                                <i class="fas fa-phone-alt text-muted mt-1 me-3" style="width: 16px;"></i>
                                <span>{{ $guest?->emergency_contact ?? 'N/A' }}</span>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>

            {{-- Reservation Info --}}
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header border-0 bg-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-light p-2 me-3">
                            <i class="fas fa-calendar-check text-gold"></i>
                        </div>
                        <h5 class="mb-0 text-dark fw-bold">Reservation Info</h5>
                    </div>
                </div>
                <div class="card-body">
                    <ul class="list-unstyled mb-0">
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Reservation Code:</span>
                            <span class="fw-bold">{{ $registration->reservation_code }}</span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Room Type:</span>
                            <span>{{ $registration->roomType?->name ?? 'N/A' }}</span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Check-in:</span>
                            <span>{{ $registration->check_in?->format('M d, Y') ?? 'N/A' }}</span>
                        </li>
                        <li class="mb-2 d-flex justify-content-between">
                            <span class="text-muted">Check-out:</span>
                            <span>{{ $registration->check_out?->format('M d, Y') ?? 'N/A' }}</span>
                        </li>
                        <li class="d-flex justify-content-between">
                            <span class="text-muted">Est. Arrival:</span>
                            <span>{{ $registration->estimated_arrival_at ? \Carbon\Carbon::parse($registration->estimated_arrival_at)->format('h:i A') : '—' }}</span>
                        </li>
                    </ul>
                </div>
            </div>

            {{-- Special Requests --}}
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header border-0 bg-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-light p-2 me-3">
                            <i class="fas fa-comment-dots text-gold"></i>
                        </div>
                        <h5 class="mb-0 text-dark fw-bold">Special Requests</h5>
                    </div>
                </div>
                <div class="card-body">
                    @if ($registration->special_requests)
                        <p class="mb-0">{{ $registration->special_requests }}</p>
                    @else
                        <p class="text-muted fst-italic mb-0">No special requests.</p>
                    @endif
                    @if ($registration->opt_in_marketing)
                        <div class="mt-2">
                            <span class="badge bg-info"><i class="fas fa-bullhorn me-1"></i>Opted in to marketing</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="col-lg-7 mb-4">
            {{-- Documents --}}
            <div class="card border-0 shadow-sm rounded-3 mb-4">
                <div class="card-header border-0 bg-white py-3">
                    <div class="d-flex align-items-center">
                        <div class="rounded-circle bg-light p-2 me-3">
                            <i class="fas fa-file-upload text-gold"></i>
                        </div>
                        <h5 class="mb-0 text-dark fw-bold">Guest Documents</h5>
                    </div>
                </div>
                <div class="card-body p-0">
                    @php $documents = $registration->documents; @endphp
                    @if ($documents->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="border-0 ps-4">Type</th>
                                        <th class="border-0">File</th>
                                        <th class="border-0">Uploaded</th>
                                        <th class="border-0">Status</th>
                                        <th class="border-0 text-end pe-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($documents as $doc)
                                        @php
                                            $status = match(true) {
                                                $doc->verified_at !== null => 'verified',
                                                $doc->rejected_at !== null => 'rejected',
                                                default => 'pending'
                                            };
                                        @endphp
                                        <tr>
                                            <td class="ps-4 text-capitalize">{{ str_replace('_', ' ', $doc->type) }}</td>
                                            <td>
                                                @if ($doc->file_path)
                                                    <a href="{{ Storage::disk('public')->exists($doc->file_path) ? asset('storage/' . $doc->file_path) : '#' }}"
                                                       target="_blank" class="btn btn-sm btn-outline-secondary">
                                                        <i class="fas fa-file me-1"></i> {{ $doc->original_name ?? 'View' }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">No file</span>
                                                @endif
                                            </td>
                                            <td>{{ $doc->created_at->format('M d, Y h:i A') }}</td>
                                            <td>
                                                @if ($status === 'verified')
                                                    <span class="badge bg-success">Verified</span>
                                                @elseif($status === 'rejected')
                                                    <span class="badge bg-danger"
                                                          title="{{ $doc->rejection_reason }}">Rejected</span>
                                                @else
                                                    <span class="badge bg-warning text-dark">Pending</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-4">
                                                @if ($status === 'pending')
                                                    <form action="{{ route('frontdesk.pre-arrivals.documents.verify', [$registration, $doc]) }}"
                                                          method="POST" class="d-inline">
                                                        @csrf
                                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Verify">
                                                            <i class="fas fa-check"></i>
                                                        </button>
                                                    </form>
                                                    <button type="button" class="btn btn-sm btn-outline-danger"
                                                            title="Reject"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#rejectModal-{{ $doc->id }}">
                                                        <i class="fas fa-times"></i>
                                                    </button>
                                                @elseif($status === 'rejected')
                                                    <span class="small text-danger" title="{{ $doc->rejection_reason }}">
                                                        <i class="fas fa-info-circle me-1"></i>{{ \Illuminate\Support\Str::limit($doc->rejection_reason, 20) }}
                                                    </span>
                                                @else
                                                    <span class="small text-success"><i class="fas fa-check-circle me-1"></i>Verified</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-5">
                            <i class="fas fa-file-upload fa-3x text-muted mb-3"></i>
                            <p class="text-muted mb-0">No documents uploaded yet.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="d-flex flex-wrap gap-2">
                <form action="{{ route('frontdesk.pre-arrivals.approve', $registration) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-check-circle me-1"></i> Approve Pre-Arrival
                    </button>
                </form>
                <form action="{{ route('frontdesk.pre-arrivals.send-reminder', $registration) }}" method="POST" class="d-inline"
                      onsubmit="return confirm('Send a pre-arrival reminder to this guest?');">
                    @csrf
                    <button type="submit" class="btn btn-warning text-dark">
                        <i class="fas fa-bell me-1"></i> Send Reminder
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Reject Modals --}}
@foreach ($registration->documents as $doc)
    @if (!$doc->verified_at && !$doc->rejected_at)
        <div class="modal fade" id="rejectModal-{{ $doc->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header bg-danger text-white">
                        <h5 class="modal-title"><i class="fas fa-times-circle me-2"></i>Reject Document</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form action="{{ route('frontdesk.pre-arrivals.documents.reject', [$registration, $doc]) }}" method="POST">
                        @csrf
                        <div class="modal-body">
                            <p class="mb-3">Reject this <strong>{{ str_replace('_', ' ', $doc->type) }}</strong> document?</p>
                            <div class="mb-3">
                                <label for="rejection_reason_{{ $doc->id }}" class="form-label fw-bold">Reason for Rejection <span class="text-danger">*</span></label>
                                <textarea name="rejection_reason" id="rejection_reason_{{ $doc->id }}" class="form-control" rows="3"
                                          placeholder="e.g. Blurry image, expired ID, incorrect document..."
                                          required></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-danger"><i class="fas fa-times me-1"></i>Reject Document</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
@endforeach
@endsection
