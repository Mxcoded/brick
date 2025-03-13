@php
    $index = $index ?? 0; // Dynamic index for new/editable fields
    $education = $education ?? null; // Existing data (for edit mode)
@endphp

<div class="educational-background-form mb-4">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="school_name">School Name</label>
                <input 
                    type="text" 
                    name="educational_background[{{ $index }}][school_name]" 
                    class="form-control" 
                    value="{{ old("educational_background.$index.school_name", $education->school_name ?? '') }}"
                    required
                >
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="qualification">Qualification</label>
                <input 
                    type="text" 
                    name="educational_background[{{ $index }}][qualification]" 
                    class="form-control" 
                    value="{{ old("educational_background.$index.qualification", $education->qualification ?? '') }}"
                    required
                >
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="start_date">Start Date</label>
                <input 
                    type="date" 
                    name="educational_background[{{ $index }}][start_date]" 
                    class="form-control" 
                    value="{{ old("educational_background.$index.start_date", $education->start_date ?? '') }}"
                    required
                >
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="end_date">End Date</label>
                <input 
                    type="date" 
                    name="educational_background[{{ $index }}][end_date]" 
                    class="form-control" 
                    value="{{ old("educational_background.$index.end_date", $education->end_date ?? '') }}"
                    required
                >
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                <label for="certificate_path">Upload Certificate</label>
                <input 
                    type="file" 
                    name="educational_background[{{ $index }}][certificate_path]" 
                    class="form-control"
                >
                @if($education && $education->certificate_path)
                    <a 
                        href="{{ asset('storage/' . $education->certificate_path) }}" 
                        target="_blank" 
                        class="btn btn-sm btn-primary mt-2"
                    >
                        <i class="fas fa-download"></i> Download Current Certificate
                    </a>
                @endif
            </div>
        </div>
    </div>
</div>