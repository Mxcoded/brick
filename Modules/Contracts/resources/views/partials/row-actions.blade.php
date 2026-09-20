<div class="d-flex justify-content-end gap-1">
    <a href="{{ route('contracts.agreements.show', $agreement) }}" class="btn btn-sm btn-outline-secondary" title="View">
        <i class="fas fa-eye"></i>
    </a>
    @can('contracts.read')
        <a href="{{ route('contracts.agreements.pdf', $agreement) }}" class="btn btn-sm btn-outline-danger" title="Download PDF">
            <i class="fas fa-file-pdf"></i>
        </a>
    @endcan
    @if (! in_array($agreement->status, ['executed', 'active', 'expired', 'cancelled']))
        @can('contracts.update')
            <a href="{{ route('contracts.agreements.edit', $agreement) }}" class="btn btn-sm btn-outline-primary" title="Edit">
                <i class="fas fa-edit"></i>
            </a>
        @endcan
        @can('contracts.delete')
            @if ($agreement->status === 'draft')
                <form method="POST" action="{{ route('contracts.agreements.destroy', $agreement) }}" class="d-inline" onsubmit="return confirm('Delete this draft agreement?');">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
                </form>
            @endif
        @endcan
    @endif
</div>