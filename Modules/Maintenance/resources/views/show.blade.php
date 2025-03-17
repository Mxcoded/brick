@extends('maintenance::layouts.master')

@section('content')
    <div class="container-fluid px-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="fw-bold text-gradient">
                <i class="fas fa-clipboard-list me-2"></i>Maintenance Case #{{ $maintenanceLog->id }}
            </h1>
            <div class="btn-group">
                <a href="{{ route('maintenance.edit', $maintenanceLog->id) }}" class="btn btn-warning rounded-pill shadow-sm">
                    <i class="fas fa-edit me-2"></i>Edit Case
                </a>
                <a href="{{ route('maintenance.index') }}" class="btn btn-secondary rounded-pill shadow-sm ms-2">
                    <i class="fas fa-arrow-left me-2"></i>Back to Logs
                </a>
            </div>
        </div>

        <div class="card border-0 shadow-lg rounded-4">
            <div class="card-header bg-light py-3">
                <h5 class="card-title mb-0">
                    <i class="fas fa-info-circle me-2 text-primary"></i>
                    Case Details
                </h5>
            </div>

            <div class="card-body">
                <div class="row g-4">
                    <!-- Left Column -->
                    <div class="col-md-6">
                        <div class="detail-item">
                            <i class="fas fa-map-marker-alt text-primary"></i>
                            <div>
                                <label>Location</label>
                                <p class="value">{{ $maintenanceLog->location }}</p>
                            </div>
                        </div>

                        <div class="detail-item">
                            <i class="fas fa-calendar-day text-info"></i>
                            <div>
                                <label>Complaint Date & Time</label>
                                <p class="value">
                                    {{ $maintenanceLog->complaint_datetime->format('M d, Y \a\t H:i') }}
                                    <small
                                        class="text-muted">({{ $maintenanceLog->complaint_datetime->diffForHumans() }})</small>
                                </p>
                            </div>
                        </div>

                        <div class="detail-item">
                            <i class="fas fa-exclamation-triangle text-danger"></i>
                            <div>
                                <label>Nature of Complaint</label>
                                <p class="value">{{ $maintenanceLog->nature_of_complaint }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="col-md-6">
                        <div class="detail-item">
                            <i class="fas fa-user-clock text-success"></i>
                            <div>
                                <label>Lodged By</label>
                                <p class="value">{{ $maintenanceLog->lodged_by }}</p>
                            </div>
                        </div>

                        <div class="detail-item">
                            <i class="fas fa-user-check text-warning"></i>
                            <div>
                                <label>Received By</label>
                                <p class="value">{{ $maintenanceLog->received_by ?? 'N/A' }}</p>
                            </div>
                        </div>

                        <div class="detail-item">
                            <i class="fas fa-coins text-purple"></i>
                            <div>
                                <label>Cost of Fixing</label>
                                <p class="value">
                                    @if ($maintenanceLog->cost_of_fixing)
                                        <span class="badge bg-success-100 text-success rounded-pill p-2">
                                            ${{ number_format($maintenanceLog->cost_of_fixing, 2) }}
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-100 text-secondary rounded-pill p-2">
                                            N/A
                                        </span>
                                    @endif
                                </p>
                            </div>
                        </div>

                        <div class="detail-item">
                            <i class="fas fa-flag-checkered text-info"></i>
                            <div>
                                <label>Completion Date</label>
                                <p class="value">
                                    @if ($maintenanceLog->completion_date)
                                        {{ $maintenanceLog->completion_date->format('M d, Y') }}
                                        <small
                                            class="text-muted">({{ $maintenanceLog->completion_date->diffForHumans() }})</small>
                                    @else
                                        <span class="text-muted">N/A</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Timeline -->
                <div class="status-timeline mt-5">
                    <div class="timeline-header mb-3">
                        <h6 class="fw-bold text-muted">
                            <i class="fas fa-history me-2"></i>Case Progress
                        </h6>
                    </div>
                    <div class="timeline-steps">
                        <div class="step {{ $maintenanceLog->status === 'new' ? 'active' : '' }}">
                            <div class="step-icon bg-primary">
                                <i class="fas fa-plus"></i>
                            </div>
                            <span class="step-label">New Case</span>
                            <span class="step-date">
                                {{ $maintenanceLog->created_at->format('M d') }}
                            </span>
                        </div>
                        <div class="step {{ $maintenanceLog->status === 'in_progress' ? 'active' : '' }}">
                            <div class="step-icon bg-warning">
                                <i class="fas fa-tools"></i>
                            </div>
                            <span class="step-label">In Progress</span>
                            @if ($maintenanceLog->status === 'in_progress')
                                <span class="step-date">
                                    {{ $maintenanceLog->updated_at->format('M d') }}
                                </span>
                            @endif
                        </div>
                        <div class="step {{ $maintenanceLog->status === 'completed' ? 'active' : '' }}">
                            <div class="step-icon bg-success">
                                <i class="fas fa-check"></i>
                            </div>
                            <span class="step-label">Completed</span>
                            @if ($maintenanceLog->status === 'completed' && $maintenanceLog->completion_date)
                                <span class="step-date">
                                    {{ $maintenanceLog->completion_date->format('M d') }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('styles')
    <style>
        .detail-item {
            display: flex;
            align-items: flex-start;
            padding: 1.25rem;
            background: #f8f9fa;
            border-radius: 0.75rem;
            margin-bottom: 1rem;
            transition: transform 0.2s ease;
        }

        .detail-item:hover {
            transform: translateX(5px);
            background: #fff;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .detail-item i {
            font-size: 1.5rem;
            margin-right: 1rem;
            min-width: 40px;
            text-align: center;
        }

        .detail-item label {
            display: block;
            font-weight: 500;
            color: #6c757d;
            margin-bottom: 0.25rem;
            font-size: 0.875rem;
        }

        .detail-item .value {
            font-size: 1.1rem;
            margin-bottom: 0;
            color: #2a3042;
            font-weight: 600;
        }

        .status-timeline {
            border-top: 1px solid #e9ecef;
            padding-top: 2rem;
        }

        .timeline-steps {
            display: flex;
            justify-content: space-between;
            position: relative;
        }

        .timeline-steps::before {
            content: "";
            position: absolute;
            top: 45%;
            left: 0;
            right: 0;
            height: 2px;
            background: #e9ecef;
            z-index: 0;
        }

        .step {
            position: relative;
            z-index: 1;
            text-align: center;
            width: 33.33%;
        }

        .step-icon {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 0.5rem;
            font-size: 1.25rem;
            color: white;
        }

        .step-label {
            display: block;
            font-weight: 500;
            color: #6c757d;
        }

        .step-date {
            display: block;
            font-size: 0.875rem;
            color: #adb5bd;
        }

        .step.active .step-icon {
            box-shadow: 0 0 0 4px rgba(var(--bs-primary-rgb), 0.15);
        }

        .text-purple {
            color: #6f42c1 !important;
        }

        .bg-success-100 {
            background-color: #e7f5e9;
        }

        .bg-secondary-100 {
            background-color: #f8f9fa;
        }
    </style>
@endsection
