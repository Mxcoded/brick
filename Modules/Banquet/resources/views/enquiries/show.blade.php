@extends('layouts.master')

@section('current-breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('banquet.index') }}">Banquet</a></li>
    <li class="breadcrumb-item"><a href="{{ route('banquet.enquiries.index') }}">Enquiries</a></li>
    <li class="breadcrumb-item active">{{ $enquiry->name }}</li>
@endsection

@section('page-content')
<div class="container-fluid py-4 banquet-theme">
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 fw-bold text-charcoal">
                <i class="fas fa-question-circle me-2 text-gold"></i>Enquiry Details
            </h1>
            <p class="text-muted mb-0">From {{ $enquiry->name }} &middot; {{ $enquiry->created_at->format('F d, Y h:i A') }}</p>
        </div>
        <a href="{{ route('banquet.enquiries.index') }}" class="btn btn-outline-charcoal">
            <i class="fas fa-arrow-left me-2"></i>Back to Enquiries
        </a>
    </div>

    <div class="row g-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-user me-2 text-gold"></i>Contact Information</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">Name</label>
                            <p class="fw-bold mb-0">{{ $enquiry->name }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">Email</label>
                            <p class="fw-bold mb-0">
                                <a href="mailto:{{ $enquiry->email }}">{{ $enquiry->email }}</a>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">Phone</label>
                            <p class="fw-bold mb-0">
                                <a href="tel:{{ $enquiry->phone }}">{{ $enquiry->phone }}</a>
                            </p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">Company</label>
                            <p class="fw-bold mb-0">{{ $enquiry->company ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-calendar-alt me-2 text-gold"></i>Event Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase">Event Type</label>
                            <p class="fw-bold mb-0">
                                <span class="badge bg-gold text-white rounded-pill px-3">{{ $enquiry->event_type }}</span>
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase">Event Date</label>
                            <p class="fw-bold mb-0">{{ $enquiry->event_date->format('l, F d, Y') }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase">Guest Count</label>
                            <p class="fw-bold mb-0">{{ $enquiry->guest_count }} guests</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase">Start Time</label>
                            <p class="fw-bold mb-0">{{ $enquiry->start_time ? date('g:i A', strtotime($enquiry->start_time)) : 'N/A' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase">End Time</label>
                            <p class="fw-bold mb-0">{{ $enquiry->end_time ? date('g:i A', strtotime($enquiry->end_time)) : 'N/A' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase">Setup Style</label>
                            <p class="fw-bold mb-0">{{ $enquiry->setup_style ?? 'N/A' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">Venue Interest</label>
                            <p class="fw-bold mb-0">{{ $enquiry->venue_interest ?? 'Not specified' }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">Catering Preference</label>
                            <p class="fw-bold mb-0">{{ $enquiry->catering_option }}</p>
                        </div>
                        <div class="col-md-6">
                            <label class="text-muted small text-uppercase">Accommodation Required</label>
                            <p class="fw-bold mb-0">
                                @if ($enquiry->accommodation_required)
                                    <span class="badge bg-success rounded-pill">Yes</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill">No</span>
                                @endif
                            </p>
                        </div>
                        @if ($enquiry->accommodation_required)
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase">Rooms Required</label>
                            <p class="fw-bold mb-0">{{ $enquiry->rooms_required }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase">Arrival Date</label>
                            <p class="fw-bold mb-0">{{ $enquiry->arrival_date ? $enquiry->arrival_date->format('M d, Y') : 'N/A' }}</p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase">Departure Date</label>
                            <p class="fw-bold mb-0">{{ $enquiry->departure_date ? $enquiry->departure_date->format('M d, Y') : 'N/A' }}</p>
                        </div>
                        @endif
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase">Parking Required</label>
                            <p class="fw-bold mb-0">
                                @if ($enquiry->parking_required)
                                    <span class="badge bg-success rounded-pill">Yes</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill">No</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase">Site Inspection</label>
                            <p class="fw-bold mb-0">
                                @if ($enquiry->site_inspection_required)
                                    <span class="badge bg-success rounded-pill">Yes</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill">No</span>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4">
                            <label class="text-muted small text-uppercase">Heard About Us</label>
                            <p class="fw-bold mb-0">{{ $enquiry->hear_about_us ?? 'N/A' }}</p>
                        </div>

                        @if ($enquiry->special_requirements)
                        <div class="col-12">
                            <label class="text-muted small text-uppercase">Special Requirements</label>
                            <p class="mb-0">{{ $enquiry->special_requirements }}</p>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            @can('banquet.update')
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-sticky-note me-2 text-gold"></i>Admin Notes</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('banquet.enquiries.update-notes', $enquiry->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <textarea name="admin_notes" class="form-control" rows="4" placeholder="Add internal notes about this enquiry...">{{ old('admin_notes', $enquiry->admin_notes) }}</textarea>
                        </div>
                        <button type="submit" class="btn btn-gold btn-sm">
                            <i class="fas fa-save me-1"></i> Save Notes
                        </button>
                    </form>
                </div>
            </div>
            @endcan
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold"><i class="fas fa-tag me-2 text-gold"></i>Status</h5>
                </div>
                <div class="card-body" id="enquiryStatusCard">
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @php $statuses = ['Pending', 'Contacted', 'Converted', 'Closed']; @endphp
                        @php $colors = ['Pending' => 'warning', 'Contacted' => 'info', 'Converted' => 'success', 'Closed' => 'secondary']; @endphp
                        @php $icons = ['Pending' => 'fa-clock', 'Contacted' => 'fa-phone', 'Converted' => 'fa-check-circle', 'Closed' => 'fa-ban']; @endphp
                        @foreach ($statuses as $s)
                            @php $active = $enquiry->status === $s; @endphp
                            <button type="button"
                                class="btn status-btn @if($active) btn-{{ $colors[$s] }} @else btn-outline-{{ $colors[$s] }} @endif d-inline-flex align-items-center gap-1"
                                data-status="{{ $s }}"
                                data-url="{{ route('banquet.enquiries.update-status', $enquiry->id) }}"
                                @if($active) disabled @endif>
                                <i class="fas {{ $icons[$s] }}"></i> {{ $s }}
                            </button>
                        @endforeach
                    </div>
                    <small class="text-muted mt-2 d-block">Click a status to update instantly</small>

                    <hr>

                    <div class="text-center">
                        <label class="text-muted small text-uppercase d-block mb-2">Received</label>
                        <p class="fw-bold mb-0">{{ $enquiry->created_at->format('M d, Y') }}</p>
                        <small class="text-muted">{{ $enquiry->created_at->format('h:i A') }}</small>
                    </div>
                </div>
            </div>

            @if ($enquiry->convertedOrder)
            <div class="card border-0 shadow-sm mb-4 bg-success text-white">
                <div class="card-body text-center">
                    <i class="fas fa-check-circle fa-3x mb-2 opacity-75"></i>
                    <h6 class="fw-bold">Converted to Order</h6>
                    <a href="{{ route('banquet.orders.show', $enquiry->convertedOrder->order_id) }}" class="btn btn-light btn-sm mt-2">
                        <i class="fas fa-eye me-1"></i> View Order #{{ $enquiry->convertedOrder->order_id }}
                    </a>
                </div>
            </div>
            @else
            @can('banquet.create')
            <div class="card border-0 shadow-sm mb-4 border-success">
                <div class="card-header bg-white py-3">
                    <h5 class="mb-0 fw-bold text-success"><i class="fas fa-exchange-alt me-2"></i>Convert to Order</h5>
                </div>
                <div class="card-body text-center">
                    <p class="text-muted small">Turn this enquiry into a banquet order with pre-filled details.</p>
                    <button type="button" class="btn btn-success w-100 btn-sm" data-bs-toggle="modal" data-bs-target="#convertModal">
                        <i class="fas fa-exchange-alt me-1"></i> Convert to Order
                    </button>
                </div>
            </div>
            @endcan
            @endif

            @can('banquet.delete')
            <div class="card border-0 shadow-sm border-danger">
                <div class="card-body text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm" data-bs-toggle="modal" data-bs-target="#deleteModal">
                        <i class="fas fa-trash-alt me-1"></i> Delete Enquiry
                    </button>
                </div>
            </div>
            @endcan
        </div>
    </div>
</div>

@can('banquet.delete')
<div class="modal fade" id="deleteModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="fas fa-exclamation-triangle text-danger me-2"></i>Confirm Delete</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Are you sure you want to delete this enquiry from <strong>{{ $enquiry->name }}</strong>?</p>
                <p class="text-muted mb-0 small">This action cannot be undone.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <form action="{{ route('banquet.enquiries.destroy', $enquiry->id) }}" method="POST">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger"><i class="fas fa-trash-alt me-1"></i> Delete</button>
                </form>
            </div>
        </div>
    </div>
</div>
@endcan

@can('banquet.create')
<div class="modal fade" id="convertModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('banquet.enquiries.convert', $enquiry->id) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title text-success"><i class="fas fa-exchange-alt me-2"></i>Convert to Order</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">Pre-filled from <strong>{{ $enquiry->name }}</strong>'s enquiry.</p>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Preparation Date</label>
                        <input type="date" name="preparation_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Order Status</label>
                        <select name="status" class="form-select" required>
                            <option value="Pending">Pending</option>
                            <option value="Confirmed" selected>Confirmed</option>
                            <option value="Cancelled">Cancelled</option>
                            <option value="Completed">Completed</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Hall Rental Fees</label>
                        <input type="number" name="hall_rental_fees" class="form-control" step="0.01" min="0" placeholder="0.00">
                    </div>
                    <hr>
                    <p class="small text-muted mb-0">
                        <i class="fas fa-info-circle me-1"></i>
                        A customer record will be created for <strong>{{ $enquiry->email }}</strong> if one doesn't exist.
                        The enquiry status will be set to <strong>Converted</strong>.
                    </p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-exchange-alt me-1"></i> Create Order
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endcan

<style>
.banquet-theme .text-gold { color: #C8A165 !important; }
.banquet-theme .bg-gold { background-color: #C8A165 !important; }
.banquet-theme .btn-gold { background-color: #C8A165; border-color: #C8A165; color: #FFFFFF; }
.banquet-theme .btn-gold:hover { background-color: #b08d55; border-color: #b08d55; }
.banquet-theme .btn-outline-charcoal { color: #333333; border-color: #333333; }
.banquet-theme .btn-outline-charcoal:hover { background-color: #333333; color: #FFFFFF; }
</style>
@endsection

@section('scripts')
<script>
    $('#enquiryStatusCard').on('click', '.status-btn:not([disabled])', function () {
        const btn = $(this);
        const url = btn.data('url');
        const status = btn.data('status');

        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Updating...');

        $.ajax({
            url: url,
            method: 'PATCH',
            data: { status, _token: '{{ csrf_token() }}' },
            success: function () {
                const card = $('#enquiryStatusCard');
                const old = card.find('.status-btn[disabled]');
                old.prop('disabled', false);
                old.removeClass((i, cls) => cls.split(' ').filter(c => c.startsWith('btn-')).join(' '));
                old.addClass((i, cls) => cls.includes('btn-warning') ? 'btn-outline-warning' :
                    cls.includes('btn-info') ? 'btn-outline-info' :
                    cls.includes('btn-success') ? 'btn-outline-success' :
                    cls.includes('btn-secondary') ? 'btn-outline-secondary' : 'btn-outline-warning');

                const btnIcons = { Pending: 'fa-clock', Contacted: 'fa-phone', Converted: 'fa-check-circle', Closed: 'fa-ban' };
                btn.removeClass((i, cls) => cls.split(' ').filter(c => c.startsWith('btn-')).join(' '));
                btn.addClass('btn-' + (status === 'Pending' ? 'warning' : status === 'Contacted' ? 'info' : status === 'Converted' ? 'success' : 'secondary'));
                btn.prop('disabled', true).html('<i class="fas ' + btnIcons[status] + '"></i> ' + status);
            },
            error: function () {
                alert('Failed to update status. Please try again.');
                const btnIcons = { Pending: 'fa-clock', Contacted: 'fa-phone', Converted: 'fa-check-circle', Closed: 'fa-ban' };
                btn.prop('disabled', false).html('<i class="fas ' + btnIcons[status] + '"></i> ' + status);
            }
        });
    });
</script>
@endsection
