{{-- This partial receives a $guest variable (which can be the lead or a member) --}}
<div class="modal fade" id="adjustStayModal-{{ $guest->id }}" tabindex="-1" aria-labelledby="adjustStayModalLabel-{{ $guest->id }}" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="adjustStayModalLabel-{{ $guest->id }}">Adjust Stay for {{ $guest->full_name }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            {{-- THE ACTION URL IS SET HERE BY LARAVEL, NOT JAVASCRIPT --}}
            <form action="{{ route('frontdesk.registrations.adjust-stay', $guest) }}" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <p>Current Check-out: <strong>{{ $guest->check_out->format('Y-m-d') }}</strong></p>
                    <div class="mb-3">
                        <label for="new_check_out_{{ $guest->id }}" class="form-label">New Check-out Date*</label>
                        <input type="date" class="form-control" id="new_check_out_{{ $guest->id }}" name="new_check_out" required>
                    </div>

                    {{-- This section only appears if the guest is a group lead --}}
                    @if ($guest->is_group_lead && $guest->children->where('stay_status', 'checked_in')->count() > 0)
                        <div id="member-extension-section">
                            <hr>
                            <h6>Apply to Group Members:</h6>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" onchange="document.querySelectorAll('.member-check-{{$guest->id}}').forEach(c => c.checked = this.checked)">
                                <label class="form-check-label"><strong>Select All / Deselect All</strong></label>
                            </div>
                            <div class="mt-2 border rounded p-2" style="max-height: 150px; overflow-y: auto;">
                                @foreach ($guest->children->where('stay_status', 'checked_in') as $member)
                                    <div class="form-check">
                                        <input class="form-check-input member-check-{{$guest->id}}" type="checkbox" name="members_to_extend[]" value="{{ $member->id }}" id="member-{{ $member->id }}">
                                        <label class="form-check-label" for="member-{{ $member->id }}">{{ $member->full_name }} (Room: {{ $member->room_allocation ?? 'N/A' }})</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="alert alert-info small mt-3">
                        <i class="fas fa-info-circle me-1"></i> The bill will be automatically recalculated.
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save Changes</button>
                    <a href="ms-clock:" target="_blank">Open Alarm</a>
                </div>
            </form>
        </div>
    </div>
</div>