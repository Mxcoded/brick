@php
    $editing = isset($template) && $template ? true : false;
    $template = $template ?? null;
    $clauses = old('clauses', $editing ? $template->clauses->map(fn ($c) => ['title' => $c->title, 'content' => $c->content])->values()->all() : []);
@endphp

@php($action = $editing ? route('contracts.templates.update', $template) : route('contracts.templates.store'))
@php($method = $editing ? 'PUT' : 'POST')

<form method="POST" action="{{ $action }}" autocomplete="off">
    @csrf
    @method($method)

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white fw-semibold">Template Details</div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $template?->name) }}" placeholder="e.g. Corporate Apartment Agreement" required>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                    <label class="form-label">Agreement Type <span class="text-danger">*</span></label>
                    <select name="type" class="form-select @error('type') is-invalid @enderror" required>
                        @foreach ($types as $type)
                            <option value="{{ $type->value }}" @selected(old('type', $template?->type) === $type->value)>{{ $type->label() }}</option>
                        @endforeach
                    </select>
                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-12">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-control" rows="2">{{ old('description', $template?->description) }}</textarea>
                </div>
                <div class="col-12">
                    <div class="form-check form-switch">
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActive" @checked((bool) old('is_active', $template?->is_active ?? true))>
                        <label class="form-check-label" for="isActive">Active — available when creating new agreements</label>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white d-flex justify-content-between align-items-center">
            <span class="fw-semibold">Standard Clauses</span>
            <button type="button" class="btn btn-sm btn-outline-primary" id="tplAddClause"><i class="fas fa-plus me-1"></i>Add Clause</button>
        </div>
        <div class="card-body">
            <div class="small text-muted mb-3">These become editable starting points in every agreement created from this template (e.g. Accommodation, Payment, Cancellation, No-show, Liability, Force Majeure).</div>
            <div id="tplClausesWrapper">
                @foreach ($clauses as $index => $clause)
                    <div class="tpl-clause-row border rounded-3 p-3 mb-3 bg-light">
                        <div class="row g-2 align-items-start">
                            <div class="col-md-6">
                                <input type="text" name="clauses[{{ $index }}][title]" class="form-control form-control-sm" placeholder="Clause title, e.g. Payment" value="{{ $clause['title'] ?? '' }}" required>
                            </div>
                            <div class="col-md-6 d-flex justify-content-end">
                                <button type="button" class="btn btn-sm btn-outline-danger remove-tpl-clause"><i class="fas fa-trash"></i></button>
                            </div>
                            <div class="col-12">
                                <textarea name="clauses[{{ $index }}][content]" rows="3" class="form-control" placeholder="Clause body text..." required>{{ $clause['content'] ?? '' }}</textarea>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if (empty($clauses))
                <div id="tplNoClauses" class="text-muted small py-2">No clauses yet.</div>
            @endif
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mb-5">
        <a href="{{ $editing ? route('contracts.templates.show', $template) : route('contracts.templates.index') }}" class="btn btn-outline-secondary">Cancel</a>
        <button type="submit" class="btn btn-gold"><i class="fas fa-save me-1"></i>{{ $editing ? 'Save Template' : 'Create Template' }}</button>
    </div>
</form>

<script>
    let tplClauseIndex = {{ count($clauses) }};

    document.getElementById('tplAddClause').addEventListener('click', () => {
        const wrapper = document.getElementById('tplClausesWrapper');
        const no = document.getElementById('tplNoClauses');
        if (no) no.remove();
        const i = tplClauseIndex;
        const row = document.createElement('div');
        row.className = 'tpl-clause-row border rounded-3 p-3 mb-3 bg-light';
        row.innerHTML = `
            <div class="row g-2 align-items-start">
                <div class="col-md-6">
                    <input type="text" name="clauses[${i}][title]" class="form-control form-control-sm" placeholder="Clause title, e.g. Payment" required>
                </div>
                <div class="col-md-6 d-flex justify-content-end">
                    <button type="button" class="btn btn-sm btn-outline-danger remove-tpl-clause"><i class="fas fa-trash"></i></button>
                </div>
                <div class="col-12">
                    <textarea name="clauses[${i}][content]" rows="3" class="form-control" placeholder="Clause body text..." required></textarea>
                </div>
            </div>`;
        wrapper.appendChild(row);
        tplClauseIndex++;
    });

    document.addEventListener('click', (e) => {
        if (e.target.closest('.remove-tpl-clause')) e.target.closest('.tpl-clause-row').remove();
    });
</script>