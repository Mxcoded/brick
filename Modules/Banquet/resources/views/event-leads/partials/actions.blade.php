<div class="dropdown">
    <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
        Actions
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <a class="dropdown-item" href="{{ route('banquet.event-leads.show', $lead->id) }}">
                <i class="fas fa-eye me-2 text-primary"></i> View Details
            </a>
        </li>
        <li><hr class="dropdown-divider"></li>
        <li>
            <button class="dropdown-item text-danger delete-lead"
                    data-url="{{ route('banquet.event-leads.destroy', $lead->id) }}">
                <i class="fas fa-trash-alt me-2"></i> Delete
            </button>
        </li>
    </ul>
</div>
