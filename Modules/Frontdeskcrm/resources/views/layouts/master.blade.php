<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">


    <title>Frontdesk CRM - @yield('title', 'Dashboard')</title>

    <meta name="description" content="{{ $description ?? '' }}">
    <meta name="keywords" content="{{ $keywords ?? '' }}">
    <meta name="author" content="{{ $author ?? '' }}">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        // NEW TAILWIND CONFIG WITH COLORHUNT PALETTE
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'primary-light': '#D9EAFD',
                        'primary-medium': '#BCCCDC',
                        'primary-dark': '#9AA6B2',
                        'base-white': '#F8FAFC',
                        'glass-bg': 'var(--glass-effect)',
                        'glass-border': 'var(--glass-border)'
                    }
                }
            }
        }
    </script>
    <style>
        /* NEW STYLES for the glass effect using the palette */
        :root {
            --glass-effect: rgba(217, 234, 253, 0.1);
            /* D9EAFD with transparency */
            --glass-border: rgba(154, 166, 178, 0.2);
            /* 9AA6B2 with transparency */
        }

        /* Body background using the subtle gradient of the palette */
        body {
            background: linear-gradient(135deg, #F8FAFC 0%, #D9EAFD 100%);
        }
    </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-base-white to-primary-light">
    <nav class="bg-base-white shadow-lg p-4">
        <div class="container mx-auto flex justify-between">
            <h1 class="text-2xl font-bold text-primary-dark">Brickspoint Front Desk</h1>
            <div class="space-x-4">
                @auth
                    <span>Welcome, {{ Auth::user()->name }}</span>
                    <a href="{{ route('logout') }}" class="text-red-500 hover:underline">Logout</a>
                @endauth
            </div>
        </div>
    </nav>
    <main class="container mx-auto py-8 px-4">
        @yield('page-content')
    </main>
    @stack('page-scripts')
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.0.0/dist/signature_pad.umd.min.js"></script>
    <script>
        function formHandler(oldData, isGuestDraftMode) {
            return {
                searchQuery: '',
                searchResults: [],
                loading: false,
                currentStep: 1,
                isGuestDraft: isGuestDraftMode, // NEW: Flag to differentiate Guest/Staff view
                selectedGuestId: oldData.guest_id || null,
                formData: oldData || {},
                groupMembers: oldData.group_members || [],
                isSubmitting: false,
                signaturePad: null,

                init() {
                    // Initialize formData structure
                    this.formData = {
                        ...{
                            title: '',
                            full_name: '',
                            email: '',
                            contact_number: '',
                            nationality: '',
                            birthday: '',
                            occupation: '',
                            company_name: '',
                            home_address: '',
                            emergency_name: '',
                            emergency_relationship: '',
                            emergency_contact: '',
                            room_type: '',
                            room_rate: '',
                            check_in: '{{ now()->format('Y-m-d') }}',
                            check_out: '',
                            no_of_guests: 1,
                            bed_breakfast: false,
                            payment_method: '',
                            booking_source_id: '',
                            guest_type_id: '',
                            is_group_lead: false,
                            agreed_to_policies: false,
                            opt_in_data_save: true,
                        },
                        ...this.formData
                    };
                    this.setupSignaturePad();

                    this.$nextTick(() => {
                        // Initialize checkbox/input values from old data
                        document.querySelectorAll('[x-model^="formData."]').forEach(el => {
                            if (el.type === 'checkbox' || el.type === 'radio') {
                                el.checked = !!this.formData[el.name];
                            } else if (this.formData[el.name]) {
                                el.value = this.formData[el.name];
                            }
                        });
                    });

                    if (this.formData.is_group_lead && this.groupMembers.length === 0 && !this.isGuestDraft) {
                        this.addGroupMember();
                    }
                },

                // --- NEW: Search & Pre-fill Logic ---
                async performSearch() {
                    if (!this.searchQuery.trim() || this.isGuestDraft) return;
                    this.loading = true;
                    this.searchResults = [];
                    try {
                        const url =
                            `{{ route('frontdesk.registrations.search') }}?query=${encodeURIComponent(this.searchQuery)}`;
                        const res = await fetch(url);
                        if (!res.ok) throw new Error('Search failed');
                        this.searchResults = await res.json();
                    } catch (e) {
                        console.error('Search error:', e);
                    }
                    this.loading = false;
                },

                selectGuest(guest) {
                    this.selectedGuestId = guest.id;
                    // Pre-fill fields from guest data
                    const guestData = {
                        title: guest.title || '',
                        full_name: guest.full_name || '',
                        email: guest.email || '',
                        contact_number: guest.contact_number || '',
                        nationality: guest.nationality || '',
                        birthday: guest.birthday || '',
                        occupation: guest.occupation || '',
                        company_name: guest.company_name || '',
                        home_address: guest.home_address || '',
                        emergency_name: guest.emergency_name || '',
                        emergency_relationship: guest.emergency_relationship || '',
                        emergency_contact: guest.emergency_contact || '',

                        // Pre-fill preferred booking data (staff-only fields)
                        room_type: guest.preferred_room_type || '',
                        bed_breakfast: !!guest.bb_included,

                        // Keep check-in/out and rates empty for new booking
                        check_in: '{{ now()->format('Y-m-d') }}',
                        check_out: '',
                        no_of_guests: 1,
                        room_rate: '',
                        payment_method: '',
                        booking_source_id: '',
                        guest_type_id: '',
                    };
                    Object.assign(this.formData, guestData);
                    this.searchResults = [];
                    this.searchQuery = '';
                    this.$nextTick(() => {
                        // Force update for checkboxes
                        const bbCb = document.getElementById('bed_breakfast');
                        if (bbCb) bbCb.checked = !!guest.bb_included;
                    });
                },
                // --- End Search & Pre-fill Logic ---

                // Step navigation logic
                nextStep(e) {
                    // ... (Validation logic remains the same for Step 1 and 2 required fields)
                    const form = e.target.closest('form');
                    const stepElement = form.querySelector(`div[x-show="currentStep === ${this.currentStep}"]`);

                    if (!stepElement) {
                        this.currentStep++;
                        return;
                    }

                    // Check required fields within the current step
                    const requiredFields = stepElement.querySelectorAll('[required]');
                    for (let i = 0; i < requiredFields.length; i++) {
                        const field = requiredFields[i];
                        // Skip front desk fields if it's a guest draft
                        if (this.isGuestDraft && ['room_type', 'room_rate', 'payment_method', 'booking_source_id',
                                'guest_type_id'
                            ].includes(field.name)) {
                            continue;
                        }
                        if ((field.type === 'checkbox' && !field.checked) || (field.value === '')) {
                            alert('Please fill in all required fields to proceed.');
                            field.focus();
                            return;
                        }
                    }

                    this.currentStep++;
                },

                prevStep() {
                    this.currentStep--;
                },

                setupSignaturePad() {
                    const canvas = document.getElementById('signature-pad');
                    if (!canvas) return;

                    this.signaturePad = new SignaturePad(canvas, {
                        backgroundColor: 'rgb(255, 255, 255)'
                    });
                    this.signaturePad.addEventListener('endStroke', () => {
                        document.getElementById('signature-input').value = this.signaturePad.toDataURL();
                    });
                },

                clearSignature() {
                    this.signaturePad.clear();
                    document.getElementById('signature-input').value = '';
                },

                // Group Member functionality (staff only)
                addGroupMember() {
                    this.groupMembers.push({
                        full_name: '',
                        contact_number: '',
                        room_assignment: ''
                    });
                },

                removeGroupMember(index) {
                    this.groupMembers.splice(index, 1);
                },
            }
        }
    </script>
</body>

</html>
