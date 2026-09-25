<div class="d-flex justify-content-end gap-1">
    <a href="{{ route('contracts.templates.show', $template) }}" class="btn btn-sm btn-outline-secondary" title="View"><i class="fas fa-eye"></i></a>
    <a href="{{ route('contracts.templates.edit', $template) }}" class="btn btn-sm btn-outline-primary" title="Edit"><i class="fas fa-edit"></i></a>
    <form method="POST" action="{{ route('contracts.templates.destroy', $template) }}" class="d-inline" onsubmit="return confirm('Delete this template? Its clauses are lost. Cannot delete templates already used by agreements.');">
        @csrf @method('DELETE')
        <button type="submit" class="btn btn-sm btn-outline-danger" title="Delete"><i class="fas fa-trash"></i></button>
    </form>
</div>