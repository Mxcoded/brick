{{-- Modules/Frontdeskcrm/Resources/views/registrations/partials/_notifications.blade.php --}}

<div class="notifications-wrapper mb-4">
    {{-- 1. SUCCESS: Booking found or Returning Guest identified --}}
    @if (session('success'))
        <div class="alert alert-success border-0 shadow-sm d-flex align-items-center animate__animated animate__fadeIn">
            <i class="fas fa-check-circle me-3 fs-4 text-success"></i>
            <div>
                <h6 class="mb-0 fw-bold">Success</h6>
                <p class="mb-0 small">{{ session('success') }}</p>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 2. ERROR: Invalid BK-Ref format, Ref already used (Fraud), or System Error --}}
    @if (session('error'))
        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center animate__animated animate__shakeX">
            <i class="fas fa-exclamation-triangle me-3 fs-4 text-danger"></i>
            <div>
                <h6 class="mb-0 fw-bold">Action Required</h6>
                <p class="mb-0 small">{{ session('error') }}</p>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- 3. INFO/STATUS: New Guest found via valid Email/Phone format --}}
    @if (session('status'))
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center animate__animated animate__fadeIn">
            <i class="fas fa-user-plus me-3 fs-4 text-info"></i>
            <div>
                <h6 class="mb-0 fw-bold">Welcome!</h6>
                <p class="mb-0 small">{{ session('status') }}</p>
            </div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
</div>