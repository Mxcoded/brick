<div class="dropdown">
    <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
        Actions
    </button>
    <ul class="dropdown-menu dropdown-menu-end">
        <li>
            <a class="dropdown-item" href="{{ route('banquet.enquiries.show', $enquiry->id) }}">
                <i class="fas fa-eye me-2 text-primary"></i> View Details
            </a>
        </li>
        @can('banquet.update')
        <li>
            <a class="dropdown-item" href="{{ route('banquet.enquiries.show', $enquiry->id) }}">
                <i class="fas fa-edit me-2 text-warning"></i> Update Status
            </a>
        </li>
        @endcan
        @can('banquet.delete')
        <li><hr class="dropdown-divider"></li>
        <li>
            <button class="dropdown-item text-danger delete-enquiry"
                    data-enquiry-id="{{ $enquiry->id }}"
                    data-url="{{ route('banquet.enquiries.destroy', $enquiry->id) }}">
                <i class="fas fa-trash-alt me-2"></i> Delete
            </button>
        </li>
        @endcan
    </ul>
</div>
