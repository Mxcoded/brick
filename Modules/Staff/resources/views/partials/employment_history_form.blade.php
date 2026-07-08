@php
    $index = $index ?? 0;
    $history = $history ?? null;
@endphp

<div class="employment-history-form mb-4">
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="employer_name">Employer Name</label>
                <input 
                    type="text" 
                    name="employment_history[{{ $index }}][employer_name]" 
                    class="form-control" 
                    value="{{ old("employment_history.$index.employer_name", $history->employer_name ?? '') }}"
                >
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="employer_contact">Employer Contact</label>
                <input 
                    type="text" 
                    name="employment_history[{{ $index }}][employer_contact]" 
                    class="form-control" 
                    value="{{ old("employment_history.$index.employer_contact", $history->employer_contact ?? '') }}"
                >
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                <label for="position_held">Position Held</label>
                <input 
                    type="text" 
                    name="employment_history[{{ $index }}][position_held]" 
                    class="form-control" 
                    value="{{ old("employment_history.$index.position_held", $history->position_held ?? '') }}"
                >
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                <label for="duration">Duration</label>
                <input 
                    type="text" 
                    name="employment_history[{{ $index }}][duration]" 
                    class="form-control" 
                    value="{{ old("employment_history.$index.duration", $history->duration ?? '') }}"
                >
            </div>
        </div>
    </div>
</div>