<form method="GET" class="row g-2 align-items-end mb-3">
    <div class="col-auto">
        <label class="form-label small mb-0">From</label>
        <input type="date" name="from" value="{{ request('from') }}" class="form-control form-control-sm">
    </div>
    <div class="col-auto">
        <label class="form-label small mb-0">To</label>
        <input type="date" name="to" value="{{ request('to') }}" class="form-control form-control-sm">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
        @if (request('from') || request('to'))
            <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-secondary">Clear</a>
        @endif
    </div>
</form>
