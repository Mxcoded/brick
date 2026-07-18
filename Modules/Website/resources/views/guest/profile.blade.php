@extends('website::layouts.guest')

@section('title', 'My Profile')

@section('content')
<div class="container py-5">
    <div class="row g-4">

        @include('website::guest.partials.sidebar', ['active' => 'profile'])

        <div class="col-lg-9">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show rounded-4 shadow-sm border-0" role="alert">
                    <div class="d-flex align-items-center">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                            <i class="fas fa-check-circle fa-lg"></i>
                        </div>
                        <div>
                            <strong class="d-block">Saved</strong>
                            {{ session('success') }}
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-sm border-0" role="alert">
                    <div class="d-flex align-items-center mb-2">
                        <div class="bg-white bg-opacity-25 rounded-circle p-2 me-3">
                            <i class="fas fa-exclamation-triangle fa-lg"></i>
                        </div>
                        <strong>Please fix the following errors</strong>
                    </div>
                    <ul class="mb-0 ps-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            <div class="card border-0 shadow-sm rounded-4" id="profileCard">
                <div class="card-header bg-white py-4 px-4 border-bottom d-flex justify-content-between align-items-center rounded-top-4">
                    <div>
                        <h5 class="mb-0 fw-bold text-dark">
                            <i class="fas fa-user-circle me-2" style="color: var(--color-gold);"></i>My Profile
                        </h5>
                        <p class="text-muted small mb-0 mt-1" id="profileSubtitle">View your profile details</p>
                    </div>
                    <button type="button" id="editButton"
                        class="btn btn-outline-dark rounded-pill px-4 py-2 fw-semibold shadow-sm">
                        <i class="fas fa-pen me-2"></i>Edit
                    </button>
                    <button type="button" id="cancelButton"
                        class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold shadow-sm d-none">
                        <i class="fas fa-times me-2"></i>Cancel
                    </button>
                </div>

                <form action="{{ route('guest.profile.update') }}" method="POST" id="profileForm">
                    @csrf
                    @method('PUT')

                    <div class="card-body p-4" id="profileBody">
                        @php
                            $p = $profile;
                            $sections = [
                                'personal' => [
                                    'icon' => 'fa-user',
                                    'title' => 'Personal Details',
                                    'fields' => [
                                        'title' => ['label' => 'Title', 'type' => 'select', 'options' => ['Mr.', 'Mrs.', 'Ms.', 'Dr.', 'Prof.'], 'value' => $p->title ?? '', 'col' => 2],
                                        'full_name' => ['label' => 'Full Name', 'type' => 'text', 'value' => $user->name, 'col' => 5, 'required' => true],
                                        'email' => ['label' => 'Email Address', 'type' => 'text', 'value' => $user->email, 'col' => 5, 'readonly' => true],
                                        'gender' => ['label' => 'Gender', 'type' => 'select', 'options' => ['Male', 'Female'], 'value' => $p->gender ?? '', 'col' => 4],
                                        'birthday' => ['label' => 'Date of Birth', 'type' => 'date', 'value' => optional($p->birthday ?? null)->format('Y-m-d'), 'col' => 4],
                                        'nationality' => ['label' => 'Nationality', 'type' => 'select', 'options' => ['Nigeria', 'Ghana', 'USA', 'UK', 'Other'], 'value' => $p->nationality ?? '', 'col' => 4],
                                    ]
                                ],
                                'employment' => [
                                    'icon' => 'fa-briefcase',
                                    'title' => 'Employment',
                                    'fields' => [
                                        'occupation' => ['label' => 'Occupation', 'type' => 'text', 'value' => $p->occupation ?? '', 'col' => 6],
                                        'company_name' => ['label' => 'Company Name', 'type' => 'text', 'value' => $p->company_name ?? '', 'col' => 6],
                                    ]
                                ],
                                'contact' => [
                                    'icon' => 'fa-address-card',
                                    'title' => 'Contact & Address',
                                    'fields' => [
                                        'contact_number' => ['label' => 'Phone Number', 'type' => 'tel', 'value' => $p->contact_number ?? '', 'col' => 6],
                                        'home_address' => ['label' => 'Home Address', 'type' => 'text', 'value' => $p->home_address ?? '', 'col' => 6],
                                        'city' => ['label' => 'City', 'type' => 'text', 'value' => $p->city ?? '', 'col' => 4],
                                        'state' => ['label' => 'State', 'type' => 'text', 'value' => $p->state ?? '', 'col' => 4],
                                        'zip_code' => ['label' => 'Zip Code', 'type' => 'text', 'value' => $p->zip_code ?? '', 'col' => 4],
                                    ]
                                ],
                                'emergency' => [
                                    'icon' => 'fa-phone-alt',
                                    'title' => 'Emergency Contact',
                                    'fields' => [
                                        'emergency_name' => ['label' => 'Contact Name', 'type' => 'text', 'value' => $p->emergency_name ?? '', 'col' => 4],
                                        'emergency_relationship' => ['label' => 'Relationship', 'type' => 'text', 'value' => $p->emergency_relationship ?? '', 'col' => 4, 'placeholder' => 'e.g. Spouse'],
                                        'emergency_contact' => ['label' => 'Emergency Phone', 'type' => 'tel', 'value' => $p->emergency_contact ?? '', 'col' => 4],
                                    ]
                                ],
                                'identification' => [
                                    'icon' => 'fa-id-card',
                                    'title' => 'Identification',
                                    'fields' => [
                                        'identification_type' => ['label' => 'ID Type', 'type' => 'select', 'options' => ['International Passport', 'National ID (NIN)', 'Drivers License'], 'value' => $p->identification_type ?? '', 'col' => 6],
                                        'identification_number' => ['label' => 'ID Number', 'type' => 'text', 'value' => $p->identification_number ?? '', 'col' => 6],
                                    ]
                                ],
                            ];
                        @endphp

                        @foreach($sections as $key => $section)
                            <div class="profile-section mb-4">
                                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                                    <div class="d-flex align-items-center justify-content-center rounded-circle"
                                        style="width: 32px; height: 32px; background: rgba(200,161,101,0.12); color: var(--color-gold);">
                                        <i class="fas {{ $section['icon'] }} fa-sm"></i>
                                    </div>
                                    <h6 class="mb-0 fw-bold text-dark">{{ $section['title'] }}</h6>
                                </div>
                                <div class="row g-3">
                                    @foreach($section['fields'] as $name => $field)
                                        @php
                                            $fieldValue = old($name, $field['value']);
                                            $isEmpty = empty($fieldValue);
                                            $readonly = !empty($field['readonly']);
                                        @endphp
                                        <div class="col-md-{{ $field['col'] }} field-wrapper" data-field="{{ $name }}">
                                            <label class="form-label fw-semibold small text-muted mb-1" for="field_{{ $name }}">
                                                {{ $field['label'] }}
                                                @if(!empty($field['required']))
                                                    <span class="text-danger edit-mode-only d-none">*</span>
                                                @endif
                                                @if($readonly)
                                                    <span class="text-muted fw-normal edit-mode-only d-none">(Cannot change)</span>
                                                @endif
                                            </label>

                                            @if($readonly)
                                                <div class="view-value">
                                                    @if($isEmpty)
                                                        <span class="text-muted fst-italic small">Not provided</span>
                                                    @else
                                                        <span class="fw-medium text-dark">{{ $fieldValue }}</span>
                                                    @endif
                                                </div>
                                                <input type="hidden" name="{{ $name }}" value="{{ $fieldValue }}">
                                            @elseif($field['type'] === 'select')
                                                <div class="view-value">
                                                    @if($isEmpty)
                                                        <span class="text-muted fst-italic small">Not provided</span>
                                                    @else
                                                        <span class="fw-medium text-dark">{{ $fieldValue }}</span>
                                                    @endif
                                                </div>
                                                <select name="{{ $name }}" id="field_{{ $name }}"
                                                    class="form-control profile-input d-none"
                                                    @if(!empty($field['required'])) required @endif>
                                                    <option value="">-- Select --</option>
                                                    @foreach($field['options'] as $opt)
                                                        <option value="{{ $opt }}" {{ $fieldValue === $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <div class="view-value">
                                                    @if($isEmpty)
                                                        <span class="text-muted fst-italic small">Not provided</span>
                                                    @else
                                                        <span class="fw-medium text-dark">{{ $fieldValue }}</span>
                                                    @endif
                                                </div>
                                                <input type="{{ $field['type'] }}" name="{{ $name }}" id="field_{{ $name }}"
                                                    class="form-control profile-input d-none"
                                                    value="{{ $fieldValue }}"
                                                    placeholder="{{ $field['placeholder'] ?? 'Enter ' . lcfirst($field['label']) }}"
                                                    @if(!empty($field['required'])) required @endif>
                                            @endif

                                            <div class="invalid-feedback"></div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <div class="card-footer bg-white px-4 py-3 border-top rounded-bottom-4 d-none" id="saveFooter">
                        <div class="d-flex justify-content-end align-items-center gap-2">
                            <button type="button" id="cancelButton2"
                                class="btn btn-outline-secondary rounded-pill px-4">Cancel</button>
                            <button type="submit" id="saveBtn"
                                class="btn btn-dark rounded-pill px-5 py-2 fw-bold shadow-sm">
                                <i class="fas fa-check me-2"></i>Save Changes
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@push('styles')
<style>
    .profile-section:last-child {
        margin-bottom: 0 !important;
    }

    .view-value {
        padding: 0.375rem 0;
        min-height: 2.25rem;
        display: flex;
        align-items: center;
    }

    .profile-input {
        transition: all 0.25s ease;
        border-radius: 0.5rem;
    }

    .profile-input:focus {
        border-color: var(--color-gold);
        box-shadow: 0 0 0 0.2rem rgba(200, 161, 101, 0.25);
    }

    .profile-input.d-none + .view-value {
        display: flex !important;
    }

    .profile-input:not(.d-none) + .view-value {
        display: none !important;
    }

    #profileCard.is-editing .card-header {
        border-bottom-color: var(--color-gold) !important;
    }

    #profileCard.is-editing .profile-section > .border-bottom {
        border-bottom-color: rgba(200,161,101,0.3) !important;
    }

    .alert-success {
        background: linear-gradient(135deg, #198754, #157347);
        color: #fff;
    }

    .alert-danger {
        background: linear-gradient(135deg, #dc3545, #b02a37);
        color: #fff;
    }

    .alert-danger ul {
        color: #fff;
    }

    .alert-danger .btn-close {
        filter: brightness(0) invert(1);
    }

    .alert-success .btn-close {
        filter: brightness(0) invert(1);
    }

    .btn-outline-dark:hover {
        background: var(--color-dark-gray);
        color: #fff;
    }

    .btn-dark {
        background: var(--color-dark-gray);
        border-color: var(--color-dark-gray);
    }

    .btn-dark:hover {
        background: #1a1a1a;
        border-color: #1a1a1a;
    }
</style>
@endpush

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const editBtn = document.getElementById('editButton');
        const cancelBtn = document.getElementById('cancelButton');
        const cancelBtn2 = document.getElementById('cancelButton2');
        const saveFooter = document.getElementById('saveFooter');
        const profileCard = document.getElementById('profileCard');
        const subtitle = document.getElementById('profileSubtitle');
        const inputs = document.querySelectorAll('.profile-input');
        const viewValues = document.querySelectorAll('.view-value');
        const editIndicators = document.querySelectorAll('.edit-mode-only');

        function enterEditMode() {
            editBtn.classList.add('d-none');
            cancelBtn.classList.remove('d-none');
            saveFooter.classList.remove('d-none');
            profileCard.classList.add('is-editing');
            subtitle.textContent = 'Edit your profile details below';

            inputs.forEach(input => {
                input.classList.remove('d-none');
            });

            editIndicators.forEach(el => el.classList.remove('d-none'));
        }

        function exitEditMode(reset = true) {
            editBtn.classList.remove('d-none');
            cancelBtn.classList.add('d-none');
            saveFooter.classList.add('d-none');
            profileCard.classList.remove('is-editing');
            subtitle.textContent = 'View your profile details';

            inputs.forEach(input => {
                input.classList.add('d-none');
            });

            editIndicators.forEach(el => el.classList.add('d-none'));

            if (reset) {
                const form = document.getElementById('profileForm');
                form.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            }
        }

        editBtn.addEventListener('click', enterEditMode);

        function cancelEdit() {
            exitEditMode(true);
            const form = document.getElementById('profileForm');
            form.reset();
            Object.keys(originalValues).forEach(name => {
                const input = document.querySelector(`.profile-input[name="${name}"]`);
                const view = input?.closest('.field-wrapper')?.querySelector('.view-value span:not(.text-muted)');
                if (input && view) {
                    if (input.tagName === 'SELECT') {
                        input.value = originalValues[name];
                        view.textContent = originalValues[name] || 'Not provided';
                    } else {
                        input.value = originalValues[name];
                        view.textContent = originalValues[name] || 'Not provided';
                    }
                }
            });
        }

        cancelBtn.addEventListener('click', cancelEdit);
        cancelBtn2.addEventListener('click', cancelEdit);

        const originalValues = {};
        document.querySelectorAll('.profile-input').forEach(input => {
            originalValues[input.name] = input.value;
        });
    });
</script>
@endpush
@endsection
