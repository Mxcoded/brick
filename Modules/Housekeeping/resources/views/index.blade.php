@extends('layouts.master')

@section('page-content')
<div class="container-fluid px-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">Housekeeping</h3>
        <div>
            <a href="{{ route('housekeeping.logs') }}" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-history"></i> Cleaning Logs
            </a>
        </div>
    </div>

    {{-- Status Summary Cards --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-danger bg-opacity-10 h-100">
                <div class="card-body text-center">
                    <h5 class="card-title text-danger mb-0">
                        <i class="fas fa-times-circle"></i>
                        <span class="ms-1">{{ $counts['dirty'] }}</span>
                    </h5>
                    <small class="text-muted">Dirty</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-warning bg-opacity-10 h-100">
                <div class="card-body text-center">
                    <h5 class="card-title text-warning mb-0">
                        <i class="fas fa-broom"></i>
                        <span class="ms-1">{{ $counts['cleaning'] }}</span>
                    </h5>
                    <small class="text-muted">Cleaning</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-success bg-opacity-10 h-100">
                <div class="card-body text-center">
                    <h5 class="card-title text-success mb-0">
                        <i class="fas fa-check-circle"></i>
                        <span class="ms-1">{{ $counts['clean'] }}</span>
                    </h5>
                    <small class="text-muted">Clean</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="card border-0 shadow-sm bg-info bg-opacity-10 h-100">
                <div class="card-body text-center">
                    <h5 class="card-title text-info mb-0">
                        <i class="fas fa-clipboard-check"></i>
                        <span class="ms-1">{{ $counts['inspected'] }}</span>
                    </h5>
                    <small class="text-muted">Inspected</small>
                </div>
            </div>
        </div>
    </div>

    {{-- Bulk Actions --}}
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body py-2">
            <div class="row align-items-center g-2">
                <div class="col-auto">
                    <strong class="text-muted small">Bulk Action:</strong>
                </div>
                <div class="col-auto">
                    <select id="bulk-status" class="form-select form-select-sm">
                        <option value="">Select status...</option>
                        <option value="dirty">Mark Dirty</option>
                        <option value="cleaning">Mark Cleaning</option>
                        <option value="clean">Mark Clean</option>
                        <option value="inspected">Mark Inspected</option>
                    </select>
                </div>
                <div class="col-auto">
                    <button id="bulk-apply" class="btn btn-sm btn-gold" disabled>
                        <i class="fas fa-check"></i> Apply
                    </button>
                </div>
                <div class="col-auto ms-auto">
                    <span class="text-muted small" id="selected-count">0 selected</span>
                </div>
            </div>
        </div>
    </div>

    {{-- Rooms by Floor --}}
    @foreach ($rooms as $floor => $floorRooms)
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                <strong><i class="fas fa-layer-group me-1"></i> Floor {{ $floor ?: 'Unassigned' }}</strong>
                <small class="text-muted">{{ $floorRooms->count() }} rooms</small>
            </div>
            <div class="card-body p-3">
                <div class="row g-2">
                    @foreach ($floorRooms as $room)
                        @php
                            $occupant = $room->currentOccupant;
                            $hkColor = $room->cleaning_status_color;
                        @endphp
                        <div class="col-6 col-md-3 col-lg-2">
                            <div class="room-card card border-0 h-100 position-relative
                                @if ($room->status === 'maintenance') bg-secondary bg-opacity-10
                                @elseif ($room->status === 'blocked') bg-dark bg-opacity-10
                                @endif
                            " data-room-id="{{ $room->id }}" style="cursor: pointer;">
                                {{-- Cleaning Status Indicator Bar --}}
                                <div class="cleaning-bar rounded-top" style="height: 4px; background-color: var(--bs-{{ $hkColor }});"></div>
                                <div class="card-body p-2 text-center">
                                    <div class="fw-bold small">{{ $room->room_number }}</div>
                                    <small class="text-muted d-block" style="font-size: 0.65rem;">
                                        {{ $room->roomType->name ?? '' }}
                                    </small>

                                    {{-- Cleaning Status Badge --}}
                                    <span class="badge bg-{{ $hkColor }} bg-opacity-75 mt-1" style="font-size: 0.6rem;">
                                        {{ ucfirst($room->cleaning_status ?? 'clean') }}
                                    </span>

                                    {{-- Occupant --}}
                                    @if ($occupant)
                                        <div class="mt-1 small text-truncate" style="font-size: 0.65rem;">
                                            <i class="fas fa-user text-muted"></i>
                                            {{ $occupant->full_name }}
                                        </div>
                                        @if ($occupant->check_out)
                                            <small class="text-muted" style="font-size: 0.6rem;">
                                                {{ \Carbon\Carbon::parse($occupant->check_out)->format('d M') }}
                                            </small>
                                        @endif
                                    @else
                                        <div class="mt-1" style="font-size: 0.65rem; color: #adb5bd;">Vacant</div>
                                    @endif

                                    {{-- Checkbox for bulk --}}
                                    <div class="form-check position-absolute top-0 end-0 m-1">
                                        <input class="form-check-input room-checkbox" type="checkbox" value="{{ $room->id }}" style="width: 14px; height: 14px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Quick Status Change Modal --}}
<div class="modal fade" id="statusModal" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header border-0 bg-light">
                <h6 class="modal-title fw-bold">Update Cleaning Status</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2"><strong id="modal-room-number"></strong></p>
                <input type="hidden" id="modal-room-id">
                <div class="d-grid gap-2">
                    <button class="btn btn-outline-danger btn-sm status-option" data-status="dirty">
                        <i class="fas fa-times-circle"></i> Dirty
                    </button>
                    <button class="btn btn-outline-warning btn-sm status-option" data-status="cleaning">
                        <i class="fas fa-broom"></i> Cleaning
                    </button>
                    <button class="btn btn-outline-success btn-sm status-option" data-status="clean">
                        <i class="fas fa-check-circle"></i> Clean
                    </button>
                    <button class="btn btn-outline-info btn-sm status-option" data-status="inspected">
                        <i class="fas fa-clipboard-check"></i> Inspected
                    </button>
                </div>
                <textarea id="modal-notes" class="form-control form-control-sm mt-2" rows="2" placeholder="Optional notes..."></textarea>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-scripts')
<script>
$(document).ready(function () {
    var selectedRoomId = null;
    var csrfToken = $('meta[name="csrf-token"]').attr('content');

    // Click room card -> open modal
    $(document).on('click', '.room-card', function (e) {
        if ($(e.target).closest('.form-check, .room-checkbox, .cleaning-bar').length) return;
        selectedRoomId = $(this).data('room-id');
        var roomNumber = $(this).find('.fw-bold').text().trim();
        $('#modal-room-id').val(selectedRoomId);
        $('#modal-room-number').text('Room ' + roomNumber);
        $('#modal-notes').val('');
        $('#statusModal').modal('show');
    });

    // Status option click
    $(document).on('click', '.status-option', function () {
        var status = $(this).data('status');
        var id = $('#modal-room-id').val();
        var notes = $('#modal-notes').val();

        $.ajax({
            url: '{{ route("housekeeping.update-status") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { room_unit_id: id, cleaning_status: status, notes: notes },
            success: function (res) {
                $('#statusModal').modal('hide');
                location.reload();
            },
            error: function () {
                alert('Failed to update status.');
            }
        });
    });

    // Bulk selection
    $(document).on('change', '.room-checkbox', function () {
        updateBulkCount();
    });

    function updateBulkCount() {
        var count = $('.room-checkbox:checked').length;
        $('#selected-count').text(count + ' selected');
        $('#bulk-apply').prop('disabled', count === 0 || $('#bulk-status').val() === '');
    }

    $('#bulk-status').on('change', updateBulkCount);

    $('#bulk-apply').on('click', function () {
        var status = $('#bulk-status').val();
        var ids = $('.room-checkbox:checked').map(function () { return $(this).val(); }).get();

        if (!status || !ids.length) return;

        if (!confirm('Update ' + ids.length + ' room(s) to "' + ucfirst(status) + '"?')) return;

        $.ajax({
            url: '{{ route("housekeeping.bulk-update") }}',
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': csrfToken },
            data: { room_ids: ids, cleaning_status: status },
            success: function (res) {
                location.reload();
            },
            error: function () {
                alert('Bulk update failed.');
            }
        });
    });

    function ucfirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }
});
</script>

<style>
.room-card {
    transition: transform 0.15s, box-shadow 0.15s;
    border: 1px solid #dee2e6 !important;
    border-radius: 8px !important;
    overflow: hidden;
}
.room-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08);
}
.room-card .cleaning-bar {
    flex-shrink: 0;
}
.room-checkbox {
    opacity: 0.4;
    cursor: pointer;
}
.room-checkbox:checked { opacity: 1; }
</style>
@endSection
