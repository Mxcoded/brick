<div class="d-flex gap-1">
    <a href="{{ route('frontdesk.audit.show', $audit) }}" class="btn btn-sm btn-outline-primary" title="View Details">
        <i class="fas fa-eye"></i>
    </a>
    @if($audit->status === 'completed' && !$audit->audit_date->isToday())
    <form action="{{ route('frontdesk.audit.rollback', $audit) }}" method="POST" onsubmit="return confirm('Roll back this audit? This will remove auto-posted room charges.')" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-sm btn-outline-danger" title="Rollback">
            <i class="fas fa-undo"></i>
        </button>
    </form>
    @endif
</div>
