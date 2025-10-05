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
        // TAILWIND CONFIG WITH COLORHUNT PALETTE
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
        /* GLASS EFFECT STYLES */
        :root {
            --glass-effect: rgba(217, 234, 253, 0.1);
            --glass-border: rgba(154, 166, 178, 0.2);
        }
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
                isGuestDraft: isGuestDraftMode,
                selectedGuestId: oldData.guest_id || null,
                formData: oldData || {},
                groupMembers: oldData.group_members || [],
                isSubmitting: false,
                signaturePad: null,
                signatureData: [],  // Store raw data array for reliable restore
                showReSign: false,  // For finalize re-signature toggle
                reSignaturePad: null,

                init() {
                    // Initialize formData structure
                    this.formData = {
                        ...{
                            title: '',
                            full_name: '',
                            gender: '',
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

                    // Defer setup; signature inits on Step 3
                    this.$nextTick(() => {
                        if (this.formData.check_in && !this.formData.check_out) {
                            const tomorrow = new Date(this.formData.check_in);
                            tomorrow.setDate(tomorrow.getDate() + 1);
                            this.formData.check_out = tomorrow.toISOString().split('T')[0];
                        }
                        this.$watch('formData.check_in', (value) => {
                            if (value && !this.formData.check_out) {
                                const tomorrow = new Date(value);
                                tomorrow.setDate(tomorrow.getDate() + 1);
                                this.formData.check_out = tomorrow.toISOString().split('T')[0];
                            }
                        });
                    });
                },

                // Search logic
                async performSearch() {
                    if (!this.searchQuery.trim()) return;
                    this.loading = true;
                    try {
                        const response = await fetch(`/frontdesk/registrations/search?query=${encodeURIComponent(this.searchQuery)}`);
                        if (!response.ok) throw new Error('Search failed');
                        this.searchResults = await response.json();
                    } catch (error) {
                        console.error('Search failed:', error);
                        this.searchResults = [];
                    }
                    this.loading = false;
                },

                selectGuest(guest) {
                    this.selectedGuestId = guest.id;
                    const guestData = {
                        title: guest.title || '',
                        full_name: guest.full_name || '',
                        gender: guest.gender || '',
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
                        room_type: guest.preferred_room_type || '',
                        bed_breakfast: !!guest.bb_included,
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
                        const bbCb = document.getElementById('bed_breakfast');
                        if (bbCb) bbCb.checked = !!guest.bb_included;
                        if (!this.isGuestDraft) {
                            this.nextStep({ target: { closest: () => document.querySelector('form') } });
                        }
                    });
                },

                // Step navigation
                nextStep(e) {
                    const form = e.target ? e.target.closest('form') : document.querySelector('form');
                    const stepElement = form.querySelector(`div[x-show="currentStep === ${this.currentStep}"]`);

                    if (!stepElement) {
                        this.currentStep++;
                        return;
                    }

                    const requiredFields = stepElement.querySelectorAll('[required]');
                    for (let i = 0; i < requiredFields.length; i++) {
                        const field = requiredFields[i];
                        if (this.isGuestDraft && ['room_type', 'room_rate', 'payment_method', 'booking_source_id', 'guest_type_id'].includes(field.name)) {
                            continue;
                        }
                        if ((field.type === 'checkbox' && !field.checked) || (!field.value.trim() && field.value !== '0')) {
                            alert('Please fill in all required fields to proceed.');
                            field.focus();
                            return;
                        }
                    }

                    this.currentStep++;
                    if (this.currentStep === 3) {
                        this.$nextTick(() => this.setupSignaturePad());
                    }
                },

                prevStep() {
                    this.currentStep--;
                },

                // Signature setup
                setupSignaturePad() {
                    const canvas = document.getElementById('signature-pad');
                    if (!canvas) return;

                    if (this.signaturePad) {
                        this.signatureData = this.signaturePad.toData();
                        this.signaturePad.off();
                        delete this.signaturePad;
                    }

                    this.signaturePad = new SignaturePad(canvas, {
                        backgroundColor: 'rgb(255, 255, 255)',
                        penColor: 'rgb(0, 0, 0)',
                        minWidth: 0.5,
                        maxWidth: 2.5
                    });

                    const resizeCanvas = () => {
                        if (!canvas || !this.signaturePad) return;
                        this.signatureData = this.signaturePad.toData();

                        const ratio = Math.max(window.devicePixelRatio || 1, 1);
                        const rect = canvas.getBoundingClientRect();
                        canvas.width = rect.width * ratio;
                        canvas.height = rect.height * ratio;
                        canvas.style.width = rect.width + 'px';
                        canvas.style.height = rect.height + 'px';
                        canvas.getContext('2d').scale(ratio, ratio);

                        this.signaturePad.fromData(this.signatureData);
                    };

                    this.signaturePad.addEventListener('endStroke', () => {
                        this.signatureData = this.signaturePad.toData();
                        const input = document.getElementById('signature-input');
                        if (input) input.value = this.signaturePad.toDataURL('image/png');
                    });

                    resizeCanvas();
                    window.addEventListener('resize', resizeCanvas);

                    if (this.signatureData && this.signatureData.length > 1) {
                        this.signaturePad.fromData(this.signatureData);
                    }
                },

                clearSignature() {
                    if (this.signaturePad) {
                        this.signaturePad.clear();
                        this.signatureData = [];
                        const input = document.getElementById('signature-input');
                        if (input) input.value = '';
                    }
                },

                // Re-signature for finalize
                setupReSignaturePad() {
                    const canvas = document.getElementById('re-signature-pad');
                    if (!canvas) return;
                    this.reSignaturePad = new SignaturePad(canvas, {
                        backgroundColor: 'rgb(255, 255, 255)',
                        penColor: 'rgb(0, 0, 0)',
                        minWidth: 0.5,
                        maxWidth: 2.5
                    });
                    // Add resize/endStroke similar to above
                },

                clearReSignature() {
                    if (this.reSignaturePad) {
                        this.reSignaturePad.clear();
                        document.getElementById('re-signature-input').value = '';
                    }
                    this.showReSign = false;
                },

                // Finalize submit handler
                handleFinalizeSubmit(event) {
                    if (this.isSubmitting) return;
                    const form = event.target.closest('form');
                    if (!form.checkValidity()) {
                        alert('Please review required fields.');
                        return;
                    }
                    this.isSubmitting = true;
                    // Update signature if re-signed
                    if (this.reSignaturePad && !this.reSignaturePad.isEmpty()) {
                        const input = document.querySelector('input[name="guest_signature"]');
                        if (input) input.value = this.reSignaturePad.toDataURL();
                    }
                    setTimeout(() => form.submit(), 100);
                },

                // Group members
                addGroupMember() {
                    this.groupMembers.push({ full_name: '', contact_number: '', room_assignment: '' });
                },

                removeGroupMember(index) {
                    this.groupMembers.splice(index, 1);
                },
            }
        }
    </script>
</body>

</html>