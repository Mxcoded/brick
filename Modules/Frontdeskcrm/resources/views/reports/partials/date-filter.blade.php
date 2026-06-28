<form method="GET" class="row g-2 align-items-end">
    <div class="col-auto">
        <label class="form-label small mb-1">From</label>
        <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $from->format('Y-m-d') }}">
    </div>
    <div class="col-auto">
        <label class="form-label small mb-1">To</label>
        <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $to->format('Y-m-d') }}">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter me-1"></i>Apply</button>
        <a href="{{ url()->current() }}" class="btn btn-sm btn-light">Reset</a>
    </div>
</form>
