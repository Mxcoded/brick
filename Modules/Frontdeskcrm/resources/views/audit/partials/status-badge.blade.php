@if($audit->status === 'completed')
    <span class="badge bg-success"><i class="fas fa-check-circle me-1"></i>Completed</span>
@elseif($audit->status === 'rolled_back')
    <span class="badge bg-secondary"><i class="fas fa-undo me-1"></i>Rolled Back</span>
@elseif($audit->status === 'open')
    <span class="badge bg-warning text-dark"><i class="fas fa-clock me-1"></i>In Progress</span>
@else
    <span class="badge bg-light text-dark">{{ ucfirst($audit->status) }}</span>
@endif
