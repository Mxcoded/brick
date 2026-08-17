/**
 * Brickspoint Website — Booking form behaviour.
 *
 * Extracted from the inline <script> of Modules/Website/resources/views/booking.blade.php
 * into the Vite bundle. Server-supplied config (routes + CSRF token) is injected
 * via window.bookingFormConfig (see Modules/Website/resources/views/booking/partials/config.blade.php).
 */

// Authoritative total bridge. The Livewire BookingSummary component prices
// server-side (WebsiteRateService) and pushes the computed totals back through
// the window-level `booking-summary-updated` event. Registered at module top
// level so it is in place before Livewire applies the initial-render dispatch.
const formatMoney = (amount) => '₦' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');

window.addEventListener('booking-summary-updated', (event) => {
    const detail = event.detail || {};
    if (detail.total == null) return;
    const reviewTotal = document.getElementById('reviewTotal');
    if (reviewTotal) {
        reviewTotal.textContent = '₦' + parseFloat(detail.total).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    const rvBase = document.getElementById('rvBaseTotal');
    if (rvBase && detail.baseTotal != null) rvBase.textContent = formatMoney(detail.baseTotal);
    const rvFee = document.getElementById('rvGuestFee');
    const rvFeeRow = document.getElementById('rvGuestFeeRow');
    if (rvFee) {
        const fee = parseFloat(detail.guestFeeTotal || 0);
        rvFee.textContent = fee > 0 ? formatMoney(fee) : 'Included';
        if (rvFeeRow) rvFeeRow.style.display = fee > 0 ? '' : 'none';
    }
    const rvGrand = document.getElementById('rvGrandTotal');
    if (rvGrand) rvGrand.textContent = formatMoney(detail.total);
    const rvAddons = document.getElementById('rvAddons');
    if (rvAddons) {
        const addonTotal = parseFloat(detail.addonTotal || 0);
        rvAddons.textContent = addonTotal > 0 ? formatMoney(addonTotal) : 'None';
    }
    const stickyTotal = document.getElementById('stickyTotal');
    if (stickyTotal) stickyTotal.textContent = formatMoney(detail.total);
});

document.addEventListener('DOMContentLoaded', () => {
    const bookingForm = document.getElementById('bookingForm');
    if (!bookingForm) return;

    const cfg = window.bookingFormConfig || {};

    // ── Stepper + draft auto-save state ──
    let currentStep = 1;
    let maxVisitedStep = 1;
    let _draftTimer = null;
    let _draftSaving = false;
    let _draftSaved = '';
    const roomSelect = document.getElementById('room_type_id');
    const checkInInput = document.getElementById('check_in_date');
    const checkOutInput = document.getElementById('check_out_date');
    const emailInput = document.getElementById('guest_email');
    const emailFeedback = document.getElementById('emailFeedback');
    const accountToggle = document.getElementById('createAccountToggle');

    const summaryName = document.getElementById('summary-name');
    const summaryRate = document.getElementById('summary-rate');
    const summaryTotal = document.getElementById('summary-total');
    const summaryImage = document.getElementById('summary-image');
    const summaryCapacity = document.getElementById('summary-capacity');
    const summaryNights = document.getElementById('summary-nights');
    const summaryCheckIn = document.getElementById('summary-checkin');
    const summaryCheckOut = document.getElementById('summary-checkout');
    const summaryTotalLive = document.getElementById('summaryTotalLive');

    const availabilityAlert = document.getElementById('availabilityAlert');
    const submitBtn = document.getElementById('submitBtn');
    const btnText = document.getElementById('btnText');
    const btnSpinner = document.getElementById('btnSpinner');

    const formatDate = (dateString) => {
        if (!dateString) return '...';
        const date = new Date(dateString);
        return date.toLocaleDateString('en-US', {
            month: 'short',
            day: 'numeric',
            year: 'numeric'
        });
    };

    function setMoneyAnimated(el, value) {
        const to = parseFloat(value);
        const from = parseFloat(el.dataset.value || '0');
        el.dataset.value = to;
        if (from === to || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            el.textContent = formatMoney(to);
            return;
        }
        el.classList.remove('amount-pulse');
        void el.offsetWidth;
        el.classList.add('amount-pulse');
        const duration = 450,
            start = performance.now();

        function tick(now) {
            const p = Math.min(1, (now - start) / duration);
            const eased = 1 - Math.pow(1 - p, 3);
            el.textContent = formatMoney(from + (to - from) * eased);
            if (p < 1) requestAnimationFrame(tick);
            else el.textContent = formatMoney(to);
        }
        requestAnimationFrame(tick);
    }

    // Announced to assistive tech only (the visible amount animates every frame)
    function announceTotal(value) {
        if (summaryTotalLive && !isNaN(parseFloat(value))) {
            summaryTotalLive.textContent = 'Total: ' + formatMoney(value);
        }
    }

    // Transparent price breakdown: how the guest fee is computed.
    function buildFeeBreakdown(extraAdults, extraChildren, extraAdultFee, extraChildFee, nights) {
        const parts = [];
        if (extraAdults > 0) {
            parts.push(extraAdults + ' extra adult' + (extraAdults !== 1 ? 's' : '') + ' × ' +
                formatMoney(extraAdultFee) + ' × ' + nights + ' night' + (nights !== 1 ? 's' : ''));
        }
        if (extraChildren > 0) {
            parts.push(extraChildren + ' child' + (extraChildren !== 1 ? 'ren' : '') + ' × ' +
                formatMoney(extraChildFee) + ' × ' + nights + ' night' + (nights !== 1 ? 's' : ''));
        }
        return parts.join('<br>');
    }

    function updateGuestFeeDisplay(guestFeeTotal, extraAdults, extraChildren, nights, extraAdultFee, extraChildFee) {
        const guestFeeRow = document.getElementById('guest-fee-row');
        const guestFeeEl = document.getElementById('summary-guest-fee');
        const breakdownEl = document.getElementById('guest-fee-breakdown');
        if (!guestFeeRow || !guestFeeEl || !breakdownEl) return;
        if (guestFeeTotal > 0) {
            guestFeeRow.classList.remove('d-none');
            guestFeeEl.textContent = formatMoney(guestFeeTotal);
            breakdownEl.innerHTML = buildFeeBreakdown(extraAdults, extraChildren, extraAdultFee, extraChildFee, nights);
        } else {
            guestFeeRow.classList.add('d-none');
            breakdownEl.innerHTML = '';
        }
    }

    function updateBaseTotal(baseTotal) {
        const baseTotalEl = document.getElementById('summary-base-total');
        if (baseTotalEl && !isNaN(parseFloat(baseTotal))) {
            baseTotalEl.textContent = formatMoney(baseTotal);
        }
    }

    const isoDate = (d) => d.toISOString().split('T')[0];

    function updateProgressBar() {
        const fill = document.getElementById('bookingProgressFill');
        const caption = document.getElementById('bookingProgressCaption');
        if (!fill || !caption) return;
        const candidateFields = [
            'guest_name', 'guest_email', 'guest_phone', 'guest_gender', 'guest_address',
            'guest_nationality', 'guest_id_type', 'guest_id_number',
            'check_in_date', 'check_out_date', 'room_type_id'
        ];
        const existing = candidateFields.filter(name => bookingForm.querySelector('[name="' + name + '"]'));
        let filled = 0;
        existing.forEach(name => {
            const el = bookingForm.querySelector('[name="' + name + '"]');
            if (el && el.value && el.value.trim() !== '') filled++;
        });
        const pct = existing.length ? Math.round((filled / existing.length) * 100) : 0;
        fill.style.width = pct + '%';
        if (pct === 100) caption.innerHTML =
            '<i class="fas fa-party-horn me-1"></i> You\'re all set — tap Complete Booking!';
        else if (pct >= 60) caption.innerHTML = 'Almost there — just a few more details 🌟';
        else if (pct > 0) caption.innerHTML = 'Great start! Keep going...';
        else caption.textContent = '';
    }

    if (emailInput && emailFeedback) {
        emailInput.addEventListener('blur', function() {
            const email = this.value;
            if (email && email.includes('@')) {
                fetch(cfg.checkEmailUrl, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': cfg.csrfToken,
                        },
                        body: JSON.stringify({
                            email: email
                        })
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.exists) {
                            emailInput.classList.add('is-invalid');
                            emailFeedback.style.display = 'block';
                            emailFeedback.innerHTML =
                                `<strong>Account found!</strong> <a href="/login">Login here</a> to book faster.`;
                            if (accountToggle) {
                                accountToggle.checked = false;
                                accountToggle.disabled = true;
                                document.getElementById('accountFields').classList.remove(
                                    'show');
                            }
                        } else {
                            emailInput.classList.remove('is-invalid');
                            emailFeedback.style.display = 'none';
                            if (accountToggle) accountToggle.disabled = false;
                        }
                    })
                    .catch(() => {});
            }
        });
    }

    function calculateNights() {
        if (checkInInput?.value && checkOutInput?.value) {
            const start = new Date(checkInInput.value);
            const end = new Date(checkOutInput.value);
            if (end > start) {
                const diffTime = Math.abs(end - start);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                return diffDays > 0 ? diffDays : 1;
            }
            return 0;
        }
        return 1;
    }

    // Livewire summary bridge: the summary column is a server-rendered
    // Livewire component. Every price-relevant change is dispatched to it so
    // WebsiteRateService (rate codes, calendar overrides, guest fees) is the
    // single authoritative source. No-op when Livewire isn't mounted.
    let _summaryDispatchTimer = null;

    // ── Add-ons / upsells ──
    function getAddonCheckboxes() {
        return Array.from(document.querySelectorAll('.addon-checkbox'));
    }

    function getSelectedAddonIds() {
        return getAddonCheckboxes()
            .filter(cb => cb.checked)
            .map(cb => parseInt(cb.value, 10))
            .filter(id => !isNaN(id));
    }

    function computeAddonTotal() {
        let total = 0;
        const nights = Math.max(1, calculateNights());
        getAddonCheckboxes().forEach(cb => {
            if (!cb.checked) return;
            const price = parseFloat(cb.dataset.price || 0);
            const perNight = cb.dataset.perNight === '1';
            total += perNight ? price * nights : price;
        });
        return total;
    }

    function syncAddonUI() {
        const selected = getSelectedAddonIds();
        getAddonCheckboxes().forEach(cb => {
            const card = cb.closest('.addon-card');
            if (card) card.classList.toggle('selected', cb.checked);
        });
        const countEl = document.getElementById('addonCount');
        if (countEl) countEl.textContent = selected.length;
    }

    function dispatchSummaryUpdate() {
        if (typeof window.Livewire === 'undefined' || !window.Livewire.dispatch) return;
        clearTimeout(_summaryDispatchTimer);
        _summaryDispatchTimer = setTimeout(() => {
            const payload = {
                roomTypeId: roomSelect ? roomSelect.value : null,
                checkIn: checkInInput ? checkInInput.value : '',
                checkOut: checkOutInput ? checkOutInput.value : '',
                adults: parseInt(document.getElementById('adults')?.value || 1),
                children: parseInt(document.getElementById('children')?.value || 0),
                addons: getSelectedAddonIds(),
            };
            window.Livewire.dispatch('summaryUpdated', payload);
        }, 200);
    }

    function updateSummary() {
        if (!roomSelect) return;
        const selectedOption = roomSelect.options[roomSelect.selectedIndex];

        if (summaryCheckIn && checkInInput?.value) summaryCheckIn.textContent = formatDate(checkInInput.value);
        if (summaryCheckOut && checkOutInput?.value) summaryCheckOut.textContent = formatDate(checkOutInput.value);

        const nights = calculateNights();
        if (summaryNights) summaryNights.textContent = nights || '—';

        if (checkInInput?.value && checkOutInput) {
            const nextDay = new Date(checkInInput.value);
            nextDay.setDate(nextDay.getDate() + 1);
            checkOutInput.min = isoDate(nextDay);
        }

        if (nights === 0 && checkInInput?.value && checkOutInput?.value) {
            if (summaryTotal) summaryTotal.textContent = '—';
            if (summaryNights) summaryNights.textContent = '—';
            const baseTotalEl = document.getElementById('summary-base-total');
            if (baseTotalEl) baseTotalEl.textContent = '—';
            const guestFeeRowEl = document.getElementById('guest-fee-row');
            if (guestFeeRowEl) guestFeeRowEl.classList.add('d-none');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.classList.remove('btn-brand');
                submitBtn.classList.add('btn-secondary');
            }
            if (btnText) btnText.textContent = 'Invalid Dates';
            const availabilityStatus = document.getElementById('availabilityStatus');
            if (availabilityStatus) availabilityStatus.classList.add('d-none');
            dispatchSummaryUpdate();
            return;
        }

        if (selectedOption.value) {
            const price = parseFloat(selectedOption.dataset.price);
            const capacity = parseInt(selectedOption.dataset.capacity || 2);
            const baseOccupancy = Math.min(parseInt(selectedOption.dataset.baseOccupancy || 2), capacity);
            const extraAdultFee = parseFloat(selectedOption.dataset.extraAdultFee || 0);
            const extraChildFee = parseFloat(selectedOption.dataset.extraChildFee || 0);

            if (summaryName) summaryName.textContent = selectedOption.dataset.name;
            if (summaryCapacity) summaryCapacity.textContent = capacity;
            if (summaryRate) summaryRate.textContent = formatMoney(price);

            // Update stepper max based on capacity
            const adultsInput = document.getElementById('adults');
            const childrenInput = document.getElementById('children');
            const capacityPill = document.getElementById('capacityPill');
            const capacityPillText = document.getElementById('capacityPillText');

            // Update capacity pill
            if (capacityPill && capacityPillText) {
                capacityPillText.innerHTML = '<i class="fas fa-users me-1"></i>Max ' + capacity + ' guests';
                capacityPill.classList.add('has-room');
            }

            // Set adults max to full room capacity
            if (adultsInput) {
                adultsInput.max = capacity;
                // Clamp current value if it exceeds capacity
                if (parseInt(adultsInput.value) > capacity) {
                    adultsInput.value = capacity;
                }
            }
            // Set children max to remaining capacity after adults
            if (childrenInput) {
                const currentAdults = parseInt(adultsInput?.value || 1);
                const maxChildren = Math.max(0, capacity - currentAdults);
                childrenInput.max = maxChildren;
                // Clamp current value if it exceeds max
                if (parseInt(childrenInput.value) > maxChildren) {
                    childrenInput.value = maxChildren;
                }
            }
            // Sync stepper button states after updating max values
            syncSteppers();
            updateGuestSummary();

            // ── Occupancy bar ──
            const adults = parseInt(adultsInput?.value || 1);
            const children = parseInt(childrenInput?.value || 0);
            const totalGuests = adults + children;
            const pct = Math.min(100, Math.round((totalGuests / capacity) * 100));
            const occBar = document.getElementById('occupancyBarFill');
            const occLabel = document.getElementById('occupancyLabel');
            const occCount = document.getElementById('occupancyCount');
            const feeHint = document.getElementById('guestFeeHint');
            if (occBar) {
                occBar.style.width = pct + '%';
                occBar.classList.toggle('occupancy-warning', pct >= 75 && pct < 100);
                occBar.classList.toggle('occupancy-full', pct >= 100);
            }
            if (occLabel) {
                const parts = [];
                if (adults > 0) parts.push(adults + ' Adult' + (adults !== 1 ? 's' : ''));
                if (children > 0) parts.push(children + ' Child' + (children !== 1 ? 'ren' : ''));
                occLabel.innerHTML = '<i class="fas fa-bed me-1"></i>' + parts.join(' + ');
            }
            if (occCount) {
                const remaining = capacity - totalGuests;
                if (remaining > 0) {
                    occCount.textContent = remaining + ' spot' + (remaining !== 1 ? 's' : '') + ' left';
                    occCount.style.color = '';
                } else if (remaining === 0) {
                    occCount.textContent = 'Full';
                    occCount.style.color = '#ef4444';
                } else {
                    occCount.textContent = 'Over capacity!';
                    occCount.style.color = '#ef4444';
                }
            }

            // Update capacity pill based on occupancy
            if (capacityPill) {
                capacityPill.classList.remove('is-full', 'is-warning');
                if (pct >= 100) {
                    capacityPill.classList.add('is-full');
                } else if (pct >= 75) {
                    capacityPill.classList.add('is-warning');
                }
            }

            // Guest fee hint — base occupancy covers adults + children; only
            // guests beyond it incur fees (matches the server-side model).
            const extraGuests = Math.max(0, totalGuests - baseOccupancy);
            const extraAdults = Math.max(0, adults - baseOccupancy);
            const extraChildren = Math.max(0, extraGuests - extraAdults);
            const guestFeePerNight = (extraAdults * extraAdultFee) + (extraChildren * extraChildFee);
            if (feeHint) {
                if (guestFeePerNight > 0) {
                    feeHint.classList.remove('d-none');
                    feeHint.innerHTML = '<i class="fas fa-exclamation-triangle me-1"></i>Extra ₦' + guestFeePerNight.toLocaleString() + '/night for extra guest' + ((extraAdults + extraChildren) !== 1 ? 's' : '');
                } else {
                    feeHint.classList.add('d-none');
                }
            }

            // Update capacity hint with room info
            const capacityHint = document.getElementById('capacityHint');
            if (capacityHint) {
                if (baseOccupancy < capacity && (extraAdultFee > 0 || extraChildFee > 0)) {
                    capacityHint.innerHTML = '<i class="fas fa-info-circle me-1"></i>Base occupancy: ' + baseOccupancy + ' guests. Extra fees apply for additional guests.';
                } else {
                    capacityHint.innerHTML = '<i class="fas fa-info-circle me-1"></i>Room capacity: ' + capacity + ' guests max.';
                }
            }

            const guestFeeTotal = guestFeePerNight * nights;
            const baseTotal = price * nights;
            const total = baseTotal + guestFeeTotal;

            // Update base total + guest fee row with transparent breakdown
            updateBaseTotal(baseTotal);
            updateGuestFeeDisplay(guestFeeTotal, extraAdults, extraChildren, nights, extraAdultFee, extraChildFee);

            const isCartFlow = bookingForm.hasAttribute('data-cart-flow');

            // Optimistic instant pricing for the review strip; the Livewire
            // summary re-renders server-side and pushes the authoritative
            // total back via `booking-summary-updated` (rate codes, calendar
            // overrides), which then corrects #reviewTotal.
            if (!isCartFlow) {
                if (summaryTotal) {
                    setMoneyAnimated(summaryTotal, total);
                    announceTotal(total);
                }
                // Update review strip total immediately
                const reviewTotal = document.getElementById('reviewTotal');
                if (reviewTotal) {
                    reviewTotal.textContent = '₦' + parseFloat(total).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                }
            }

            if (summaryImage && selectedOption.dataset.image) {
                summaryImage.src = selectedOption.dataset.image;
                summaryImage.classList.remove('d-none');
            }

            scheduleAvailabilityCheck();
        } else {
            // No room selected — clear occupancy bar and reset capacity pill
            const occBar = document.getElementById('occupancyBarFill');
            const occLabel = document.getElementById('occupancyLabel');
            const occCount = document.getElementById('occupancyCount');
            const capacityPill = document.getElementById('capacityPill');
            const capacityPillText = document.getElementById('capacityPillText');
            if (occBar) { occBar.style.width = '0%'; occBar.classList.remove('occupancy-warning', 'occupancy-full'); }
            if (occLabel) occLabel.innerHTML = '<i class="fas fa-bed me-1"></i>Select a room';
            if (occCount) { occCount.textContent = ''; occCount.style.color = ''; }
            if (capacityPill) {
                capacityPill.classList.remove('has-room', 'is-full', 'is-warning');
            }
            if (capacityPillText) {
                capacityPillText.innerHTML = '<i class="fas fa-users me-1"></i>Select a room';
            }
            const feeHint = document.getElementById('guestFeeHint');
            if (feeHint) feeHint.classList.add('d-none');
            const capacityHint = document.getElementById('capacityHint');
            if (capacityHint) capacityHint.innerHTML = '<i class="fas fa-info-circle me-1"></i>Choose a room to see occupancy';
            const baseTotalEl = document.getElementById('summary-base-total');
            if (baseTotalEl) baseTotalEl.textContent = '—';
            const guestFeeRowEl = document.getElementById('guest-fee-row');
            if (guestFeeRowEl) guestFeeRowEl.classList.add('d-none');
            const guestFeeBreakdownEl = document.getElementById('guest-fee-breakdown');
            if (guestFeeBreakdownEl) guestFeeBreakdownEl.innerHTML = '';
        }

        dispatchSummaryUpdate();
        scheduleDraftSave();
    }

    // Debounced availability/unit fetches so typing or rapid stepper clicks
    // never fire a network call per keystroke.
    let _availabilityTimer = null;
    function scheduleAvailabilityCheck() {
        clearTimeout(_availabilityTimer);
        _availabilityTimer = setTimeout(() => {
            checkAvailability();
            loadAvailableUnits();
        }, 300);
    }

    function loadAvailableUnits() {
        const roomTypeId = roomSelect ? roomSelect.value : null;
        const checkIn = checkInInput ? checkInInput.value : null;
        const checkOut = checkOutInput ? checkOutInput.value : null;
        const unitSection = document.getElementById('roomUnitSection');
        const unitSelect = document.getElementById('room_unit_id');
        const unitSpinner = document.getElementById('unitLoadingSpinner');

        if (!roomTypeId || !checkIn || !checkOut || !unitSection) {
            if (unitSection) unitSection.style.display = 'none';
            return;
        }

        unitSection.style.display = 'block';
        unitSpinner.classList.remove('d-none');
        unitSelect.disabled = true;

        const queryParams = new URLSearchParams({
            room_type_id: roomTypeId,
            check_in_date: checkIn,
            check_out_date: checkOut
        });

        fetch(cfg.availableUnitsUrl + '?' + queryParams.toString(), {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                unitSpinner.classList.add('d-none');
                unitSelect.disabled = false;
                unitSelect.innerHTML = '<option value="">-- Auto-assign at check-in --</option>';
                if (data.units && data.units.length > 0) {
                    data.units.forEach(unit => {
                        const option = document.createElement('option');
                        option.value = unit.id;
                        option.textContent = `Room ${unit.room_number}` + (unit.floor ?
                            ` (Floor ${unit.floor})` : '');
                        unitSelect.appendChild(option);
                    });
                } else {
                    const option = document.createElement('option');
                    option.value = '';
                    option.textContent = 'No rooms available for these dates';
                    option.disabled = true;
                    unitSelect.appendChild(option);
                }
            })
            .catch(err => {
                console.error('Failed to load units:', err);
                unitSpinner.classList.add('d-none');
                unitSelect.disabled = false;
            });
    }

    function checkAvailability() {
        if (!roomSelect) return;
        const roomTypeId = roomSelect.value;
        const checkIn = checkInInput.value;
        const checkOut = checkOutInput.value;
        const availabilityStatus = document.getElementById('availabilityStatus');

        if (roomTypeId && checkIn && checkOut && calculateNights() > 0) {
            btnText.textContent = 'Checking Availability...';
            submitBtn.disabled = true;
            if (availabilityStatus) availabilityStatus.classList.remove('d-none');

            const queryParams = new URLSearchParams({
                room_type_id: roomTypeId,
                check_in_date: checkIn,
                check_out_date: checkOut
            });

            fetch(cfg.checkAvailabilityUrl + '?' + queryParams.toString(), {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => {
                    if (availabilityStatus) availabilityStatus.classList.add('d-none');
                    if (data.available === false) {
                        availabilityAlert.classList.remove('d-none', 'alert-success', 'alert-warning');
                        availabilityAlert.classList.add('alert-danger');
                        availabilityAlert.innerHTML =
                            `<i class="fas fa-times-circle me-2"></i> ${data.message}`;

                        if (data.suggestion) {
                            const suggestBtn = document.createElement('button');
                            suggestBtn.type = 'button';
                            suggestBtn.className = 'btn btn-sm btn-outline-brand mt-2 d-block';
                            suggestBtn.innerHTML =
                                `Use Available: ${formatDate(data.suggestion.check_in)} - ${formatDate(data.suggestion.check_out)}`;
                            suggestBtn.onclick = function() {
                                checkInInput.value = data.suggestion.check_in;
                                checkOutInput.value = data.suggestion.check_out;
                                updateSummary();
                            };
                            availabilityAlert.appendChild(suggestBtn);
                        }

                        submitBtn.classList.add('btn-secondary');
                        submitBtn.classList.remove('btn-brand');
                        submitBtn.disabled = true;
                        btnText.textContent = 'Room Unavailable';
                    } else {
                        availabilityAlert.classList.add('d-none');
                        submitBtn.disabled = false;
                        submitBtn.classList.add('btn-brand');
                        submitBtn.classList.remove('btn-secondary');
                        updateSubmitBtnText();
                    }
                })
                .catch(err => {
                    console.error('Check failed:', err);
                    if (availabilityStatus) availabilityStatus.classList.add('d-none');
                    availabilityAlert.classList.remove('d-none', 'alert-success');
                    availabilityAlert.classList.add('alert-warning');
                    availabilityAlert.innerHTML = '<i class="fas fa-exclamation-triangle me-2"></i>Unable to check availability. Please try again.';
                    submitBtn.disabled = false;
                    updateSubmitBtnText();
                });
        }
    }

    if (roomSelect) {
        roomSelect.addEventListener('change', updateSummary);
    }
    if (checkInInput) {
        checkInInput.addEventListener('change', updateSummary);
    }
    if (checkOutInput) {
        checkOutInput.addEventListener('change', updateSummary);
    }

    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            // Validate every step; jump to the first one that is incomplete.
            const stepSectionsArr = Array.from(bookingForm.querySelectorAll('.form-section[data-step]'));
            let firstInvalidStep = 0;
            for (let s = 1; s <= stepSectionsArr.length; s++) {
                if (!stepIsValid(s)) {
                    firstInvalidStep = s;
                    break;
                }
            }
            if (firstInvalidStep > 0) {
                e.preventDefault();
                if (typeof showStep === 'function') showStep(firstInvalidStep);
                return;
            }
            if (!submitBtn.disabled) {
                burstConfetti();
                submitBtn.disabled = true;
                submitBtn.classList.add('loading');
                btnText.innerHTML = '<i class="fas fa-circle-notch fa-spin me-2"></i>Processing...';
                btnSpinner.classList.remove('d-none');
            }
        });
    }

    if (accountToggle) {
        accountToggle.addEventListener('change', function() {
            document.getElementById('accountFields').classList.toggle('show', this.checked);
            scheduleDraftSave();
        });
    }

    // Initialize summary and steppers - IMPORTANT: updateSummary must run first
    // to set correct max values on inputs before steppers are initialized
    if (roomSelect && roomSelect.value) {
        updateSummary();
    }
    // Sync steppers after summary update to reflect correct max values
    syncSteppers();

    // Re-push the current form state to Livewire once it is ready so the
    // authoritative total always reaches the review strip on first paint
    // (belt-and-suspenders: the module-top-level listener above also catches
    // Livewire's own initial-render dispatch).
    document.addEventListener('livewire:init', () => {
        if (roomSelect?.value) dispatchSummaryUpdate();
    });

    // ── Fun UX wiring ──
    document.documentElement.classList.add('js');

    // Reveal sections on scroll (fallback: show all if unsupported)
    const reveals = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window) {
        const io = new IntersectionObserver((entries) => {
            entries.forEach(e => {
                if (e.isIntersecting) {
                    e.target.classList.add('visible');
                    io.unobserve(e.target);
                }
            });
        }, {
            threshold: 0.12
        });
        reveals.forEach(r => io.observe(r));
    } else {
        reveals.forEach(r => r.classList.add('visible'));
    }

    // Quick date chips
    const dateChips = document.getElementById('dateChips');
    if (dateChips && checkInInput && checkOutInput) {
        dateChips.querySelectorAll('.date-chip').forEach(chip => {
            chip.addEventListener('click', function() {
                const offset = parseInt(this.dataset.offset, 10);
                const nights = parseInt(this.dataset.nights, 10) || 1;
                const base = new Date();
                base.setDate(base.getDate() + offset);
                const ci = new Date(base);
                const co = new Date(base);
                co.setDate(co.getDate() + nights);
                checkInInput.value = isoDate(ci);
                checkOutInput.value = isoDate(co);
                dateChips.querySelectorAll('.date-chip').forEach(c => c.classList.remove(
                    'active'));
                this.classList.add('active');
                updateSummary();
                updateProgressBar();
            });
        });
    }

    // Guest steppers - sync button disabled states based on current input values
    function syncSteppers() {
        document.querySelectorAll('.guest-stepper').forEach(stepper => {
            const input = stepper.querySelector('input[type="number"]');
            // Always read fresh max value from the DOM (may have been updated by updateSummary)
            const min = parseInt(input.min, 10) || 0;
            const max = parseInt(input.max, 10);
            const effectiveMax = isNaN(max) || max <= 0 ? 20 : max;
            const v = parseInt(input.value, 10) || min;
            
            const decBtn = stepper.querySelector('.step-dec');
            const incBtn = stepper.querySelector('.step-inc');
            
            if (decBtn) decBtn.disabled = v <= min;
            if (incBtn) incBtn.disabled = v >= effectiveMax;
        });
    }

    document.querySelectorAll('.guest-stepper').forEach(stepper => {
        const input = stepper.querySelector('input[type="number"]');
        // Always read fresh min/max from DOM since they can change when room is selected
        const getMin = () => parseInt(input.min, 10) || 0;
        const getMax = () => { 
            const m = parseInt(input.max, 10); 
            return isNaN(m) ? 20 : m; 
        };
        
        const sync = () => {
            // First update summary which will set the correct max values based on room capacity
            updateSummary();
            
            // Now read the potentially updated max values
            const min = getMin();
            const max = getMax();
            const v = parseInt(input.value, 10) || min;
            
            // Clamp value to valid range
            if (v > max) input.value = max;
            if (v < min) input.value = min;
            
            // Update button states
            const decBtn = stepper.querySelector('.step-dec');
            const incBtn = stepper.querySelector('.step-inc');
            if (decBtn) decBtn.disabled = parseInt(input.value, 10) <= min;
            if (incBtn) incBtn.disabled = parseInt(input.value, 10) >= max;
            
            updateProgressBar();
            updateGuestSummary();
            updateReviewStrip();
            
            // Update children max based on remaining capacity after adults
            const adultsInput = document.getElementById('adults');
            const childrenInput = document.getElementById('children');
            if (adultsInput && childrenInput && roomSelect?.options[roomSelect.selectedIndex]?.value) {
                const capacity = parseInt(roomSelect.options[roomSelect.selectedIndex].dataset.capacity || 20);
                const maxChildren = Math.max(0, capacity - parseInt(adultsInput.value || 1));
                childrenInput.max = maxChildren;
                if (parseInt(childrenInput.value) > maxChildren) {
                    childrenInput.value = maxChildren;
                }
                // Validate total capacity
                const totalGuests = parseInt(adultsInput.value || 1) + parseInt(childrenInput.value || 0);
                const capacityError = document.getElementById('capacityError');
                if (totalGuests > capacity) {
                    if (capacityError) {
                        capacityError.textContent = `Total guests (${totalGuests}) exceed room capacity (${capacity})`;
                        capacityError.classList.remove('d-none');
                    }
                    if (submitBtn) submitBtn.disabled = true;
                } else {
                    if (capacityError) capacityError.classList.add('d-none');
                }
            }
            
            // Sync all steppers to update button states
            syncSteppers();
        };
        
        stepper.querySelector('.step-dec')?.addEventListener('click', () => {
            const min = getMin();
            const currentVal = parseInt(input.value, 10) || min;
            if (currentVal > min) {
                input.value = currentVal - 1;
                sync();
            }
        });
        
        stepper.querySelector('.step-inc')?.addEventListener('click', () => {
            const max = getMax();
            const min = getMin();
            const currentVal = parseInt(input.value, 10) || min;
            if (currentVal < max) {
                input.value = currentVal + 1;
                sync();
            }
        });
        
        // Initial sync on page load
        syncSteppers();
    });

    // Live progress as the guest types — only pricing-relevant fields
    // trigger a full summary refresh (which would hit the network).
    const priceRelevantFields = ['check_in_date', 'check_out_date', 'room_type_id', 'adults', 'children'];
    function refreshUI(sourceEl) {
        updateProgressBar();
        updateStepIndicator();
        updateReviewStrip();
        updateReviewPanel();
        updateGuestSummary();
        scheduleDraftSave();
        if (sourceEl && priceRelevantFields.includes(sourceEl.name)) {
            updateSummary();
        }
    }
    if (bookingForm) {
        bookingForm.querySelectorAll('input, select, textarea').forEach(el => {
            el.addEventListener('input', () => refreshUI(el));
            el.addEventListener('change', () => refreshUI(el));
        });
    }
    refreshUI();

    // ── Add-on toggle ──
    // Non-cart flow: re-price via the Livewire BookingSummary bridge (single
    // authoritative source). Cart flow: persist to the session cart API, then
    // nudge CartSummary to re-render (it re-pushes authoritative totals via
    // the booking-summary-updated bridge).
    getAddonCheckboxes().forEach(cb => {
        cb.addEventListener('change', () => {
            const card = cb.closest('.addon-card');
            if (card) card.classList.toggle('selected', cb.checked);

            if (!bookingForm.hasAttribute('data-cart-flow')) {
                syncAddonUI();
                dispatchSummaryUpdate();
                return;
            }

            if (!cfg.cartAddonUrl) return;
            const id = parseInt(cb.value, 10);
            const url = cb.checked
                ? cfg.cartAddonUrl
                : cfg.cartAddonRemoveUrl.replace('__ID__', id);
            const opts = {
                method: cb.checked ? 'POST' : 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': cfg.csrfToken,
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            };
            if (cb.checked) opts.body = new URLSearchParams({ addon_id: id });

            fetch(url, opts)
                .then(res => res.json())
                .then(data => {
                    if (!data.success) {
                        cb.checked = !cb.checked;
                        if (card) card.classList.toggle('selected', cb.checked);
                        if (data.error) console.warn('[addon]', data.error);
                        return;
                    }
                    syncAddonUI();
                    if (data.cart) {
                        const addonTotal = parseFloat(data.cart.addon_total || 0);
                        const rvAddons = document.getElementById('rvAddons');
                        if (rvAddons) rvAddons.textContent = addonTotal > 0 ? formatMoney(addonTotal) : 'None';
                        const rvGrand = document.getElementById('rvGrandTotal');
                        if (rvGrand) rvGrand.textContent = formatMoney(data.cart.total || 0);
                        const ctaTotal = document.querySelector('#bookingCta .total-amount');
                        if (ctaTotal && data.cart.formatted_total) ctaTotal.textContent = data.cart.formatted_total;
                        const stickyTotal = document.getElementById('stickyTotal');
                        if (stickyTotal && data.cart.formatted_total) stickyTotal.textContent = data.cart.formatted_total;
                    }
                    if (window.Livewire?.dispatch) window.Livewire.dispatch('cart-updated');
                })
                .catch(() => {
                    cb.checked = !cb.checked;
                    if (card) card.classList.toggle('selected', cb.checked);
                });
        });
    });

    // Confetti burst on a valid submit
    function burstConfetti() {
        const canvas = document.getElementById('confettiCanvas');
        if (!canvas || window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        const ctx = canvas.getContext('2d');
        canvas.width = window.innerWidth;
        canvas.height = window.innerHeight;
        const colors = ['#C8A165', '#1a1a2e', '#e8cfa6', '#f4a261', '#2a9d8f'];
        const pieces = Array.from({
            length: 120
        }, () => ({
            x: canvas.width / 2,
            y: canvas.height / 2,
            vx: (Math.random() - 0.5) * 14,
            vy: (Math.random() - 0.5) * 14 - 4,
            size: Math.random() * 8 + 4,
            color: colors[Math.floor(Math.random() * colors.length)],
            rot: Math.random() * Math.PI,
            vr: (Math.random() - 0.5) * 0.3,
            life: 1
        }));
        let frames = 0;
        (function draw() {
            ctx.clearRect(0, 0, canvas.width, canvas.height);
            let alive = false;
            pieces.forEach(p => {
                p.x += p.vx;
                p.y += p.vy;
                p.vy += 0.35;
                p.rot += p.vr;
                p.life -= 0.012;
                if (p.life > 0 && p.y < canvas.height + 20) {
                    alive = true;
                    ctx.save();
                    ctx.globalAlpha = Math.max(0, p.life);
                    ctx.translate(p.x, p.y);
                    ctx.rotate(p.rot);
                    ctx.fillStyle = p.color;
                    ctx.fillRect(-p.size / 2, -p.size / 2, p.size, p.size * 0.6);
                    ctx.restore();
                }
            });
            frames++;
            if (alive && frames < 140) requestAnimationFrame(draw);
            else ctx.clearRect(0, 0, canvas.width, canvas.height);
        })();
    }

    // ── Payment method visual fallback (Safari :has() support) ──
    document.querySelectorAll('.payment-option').forEach(opt => {
        opt.addEventListener('click', function() {
            document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
            this.classList.add('selected');
            updateSubmitBtnText();
        });
        if (opt.querySelector('input[type="radio"]:checked')) {
            opt.classList.add('selected');
        }
    });

    function updateSubmitBtnText() {
        const selected = document.querySelector('input[name="payment_method"]:checked');
        const btnTextEl = document.getElementById('btnText');
        const stickyBtnTextEl = document.getElementById('stickyBtnText');
        const label = selected && selected.value === 'pay_on_arrival'
            ? '<i class="fas fa-hotel me-2"></i>Reserve — Pay at Hotel'
            : '<i class="fas fa-lock me-2 lock-icon"></i>Complete Booking';
        if (btnTextEl) btnTextEl.innerHTML = label;
        if (stickyBtnTextEl) stickyBtnTextEl.innerHTML = label;
    }
    document.querySelectorAll('input[name="payment_method"]').forEach(r => {
        r.addEventListener('change', updateSubmitBtnText);
    });

    // ── Smooth scroll to first error ──
    if (bookingForm) {
        bookingForm.addEventListener('submit', function(e) {
            const firstInvalid = this.querySelector('.is-invalid, :invalid');
            if (firstInvalid) {
                setTimeout(() => {
                    firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    firstInvalid.focus({ preventScroll: true });
                }, 150);
            }
        });
    }

    // ── Smart date chip labels (show actual dates) ──
    if (dateChips) {
        dateChips.querySelectorAll('.date-chip').forEach(chip => {
            const offset = parseInt(chip.dataset.offset, 10);
            if (!isNaN(offset)) {
                const d = new Date();
                d.setDate(d.getDate() + offset);
                const month = d.toLocaleDateString('en-US', { month: 'short' });
                chip.textContent = chip.textContent + ' (' + month + ' ' + d.getDate() + ')';
            }
        });
    }

    // ── Live guest count in summary ──
    function updateGuestSummary() {
        const el = document.getElementById('summary-guests');
        if (!el) return;
        const adults = parseInt(document.getElementById('adults')?.value || 1);
        const children = parseInt(document.getElementById('children')?.value || 0);
        const parts = [];
        if (adults > 0) parts.push(adults + ' ' + (adults === 1 ? 'Adult' : 'Adults'));
        if (children > 0) parts.push(children + ' ' + (children === 1 ? 'Child' : 'Children'));
        el.textContent = parts.join(', ') || '1 Adult';
    }
    ['adults', 'children'].forEach(id => {
        const el = document.getElementById(id);
        if (el) {
            el.addEventListener('change', updateGuestSummary);
            el.addEventListener('input', updateGuestSummary);
        }
    });
    updateGuestSummary();

    // ── Special request chips ──
    document.querySelectorAll('.request-chip').forEach(chip => {
        chip.addEventListener('click', function() {
            const textarea = document.querySelector('textarea[name="special_requests"]');
            if (!textarea) return;
            const text = this.dataset.text;
            const current = textarea.value.trim();
            this.classList.toggle('active');
            if (this.classList.contains('active')) {
                textarea.value = current ? current + ', ' + text : text;
            } else {
                textarea.value = current.replace(new RegExp('(^|,\\s*)' + text.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '(,\\s*|$)', 'g'), '').replace(/,\s*,/g, ', ').replace(/^,\s*|,\s*$/g, '').trim();
            }
        });
    });

    // ── Room card selection ──
    const roomCards = document.querySelectorAll('.room-card');
    roomCards.forEach(card => {
        card.addEventListener('click', function() {
            roomCards.forEach(c => c.classList.remove('selected'));
            this.classList.add('selected');
            const id = this.dataset.roomTypeId;
            if (roomSelect) {
                roomSelect.value = id;
                roomSelect.dispatchEvent(new Event('change'));
            }
        });
        card.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });

    // ── Step indicator update ──
    // The indicator mirrors the stepper: `currentStep` is active, earlier
    // steps are marked completed. `maxVisitedStep` keeps the furthest step
    // reached so returning guests see their progress preserved.
    function updateStepIndicator() {
        const steps = document.querySelectorAll('.step-item');
        const connectors = document.querySelectorAll('.step-connector');
        const activeIdx = currentStep - 1;
        steps.forEach((s, i) => {
            s.classList.toggle('active', i === activeIdx);
            s.classList.toggle('completed', i < activeIdx);
            s.classList.toggle('visited', i < maxVisitedStep);
        });
        connectors.forEach((c, i) => {
            c.classList.toggle('completed', i < maxVisitedStep - 1);
        });
    }

    // ── Review strip update ──
    function updateReviewStrip() {
        const roomName = document.getElementById('reviewRoom');
        const dates = document.getElementById('reviewDates');
        const nights = document.getElementById('reviewNights');
        const guests = document.getElementById('reviewGuests');
        const total = document.getElementById('reviewTotal');
        if (!roomName) return;

        const opt = roomSelect?.options[roomSelect.selectedIndex];
        if (opt && opt.value) {
            roomName.textContent = opt.dataset.name || 'Room';
            if (total) {
                const price = parseFloat(opt.dataset.price || 0);
                const n = calculateNights();
                const capacity = parseInt(opt.dataset.capacity || 2);
                const baseOccupancy = Math.min(parseInt(opt.dataset.baseOccupancy || 2), capacity);
                const extraAdultFee = parseFloat(opt.dataset.extraAdultFee || 0);
                const extraChildFee = parseFloat(opt.dataset.extraChildFee || 0);
                const adults = parseInt(document.getElementById('adults')?.value || 1);
                const children = parseInt(document.getElementById('children')?.value || 0);
                const extraGuests = Math.max(0, (adults + children) - baseOccupancy);
                const extraAdults = Math.max(0, adults - baseOccupancy);
                const extraChildren = Math.max(0, extraGuests - extraAdults);
                const guestFeePerNight = (extraAdults * extraAdultFee) + (extraChildren * extraChildFee);
                const totalVal = (price * n) + (guestFeePerNight * n);
                total.textContent = '₦' + totalVal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }
        } else {
            roomName.textContent = 'Select a room';
            if (total) total.textContent = '₦0.00';
        }

        if (dates && checkInInput?.value) {
            const ci = new Date(checkInInput.value);
            const co = checkOutInput?.value ? new Date(checkOutInput.value) : new Date(ci.getTime() + 86400000);
            dates.textContent = ci.toLocaleDateString('en-US', { month: 'short', day: 'numeric' }) + ' - ' + co.toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
        } else if (dates) {
            dates.textContent = '—';
        }

        const n = calculateNights();
        if (nights) nights.textContent = n;

        const a = parseInt(document.getElementById('adults')?.value || 1);
        const c = parseInt(document.getElementById('children')?.value || 0);
        if (guests) {
            const parts = [];
            if (a > 0) parts.push(a + (a === 1 ? ' Adult' : ' Adults'));
            if (c > 0) parts.push(c + (c === 1 ? ' Child' : ' Children'));
            guests.textContent = parts.join(', ') || '1 Adult';
        }
    }

    // ── Review step panel ──
    // Mirrors the live form state into the read-only "Review Your Booking"
    // section. Server-rendered values (cart flow, where the date/room inputs
    // are not present) are left untouched. The authoritative price breakdown
    // is pushed by the `booking-summary-updated` Livewire bridge.
    function updateReviewPanel() {
        const get = (id) => document.getElementById(id);
        const isCartFlow = bookingForm.hasAttribute('data-cart-flow');

        if (!isCartFlow) {
            const opt = roomSelect?.options[roomSelect.selectedIndex];
            const roomNameEl = get('rvRoom');
            if (roomNameEl) {
                roomNameEl.textContent = opt && opt.value ? (opt.dataset.name || 'Room') : 'Select a room';
            }
        }

        if (get('rvCheckIn') && checkInInput?.value) get('rvCheckIn').textContent = formatDate(checkInInput.value);
        if (get('rvCheckOut') && checkOutInput?.value) get('rvCheckOut').textContent = formatDate(checkOutInput.value);

        if (checkInInput?.value && checkOutInput?.value) {
            const nightsEl = get('rvNights');
            if (nightsEl) nightsEl.textContent = calculateNights();
        }

        const guestNameEl = get('rvGuestName');
        const guestName = bookingForm.querySelector('[name="guest_name"]');
        if (guestNameEl && guestName?.value) guestNameEl.textContent = guestName.value;

        const guestsEl = get('rvGuests');
        if (guestsEl) {
            const a = parseInt(document.getElementById('adults')?.value || 1);
            const c = parseInt(document.getElementById('children')?.value || 0);
            const parts = [];
            if (a > 0) parts.push(a + (a === 1 ? ' Adult' : ' Adults'));
            if (c > 0) parts.push(c + (c === 1 ? ' Child' : ' Children'));
            guestsEl.textContent = parts.join(', ') || '1 Adult';
        }

        const idType = bookingForm.querySelector('[name="guest_id_type"]');
        const idNumber = bookingForm.querySelector('[name="guest_id_number"]');
        const idEl = get('rvId');
        if (idEl) {
            if (idType?.value && idNumber?.value) idEl.textContent = idType.value + ' · ' + idNumber.value;
            else if (idType?.value) idEl.textContent = idType.value;
            else if (idNumber?.value) idEl.textContent = idNumber.value;
            else idEl.textContent = '—';
        }

        const requestsEl = get('rvRequests');
        const requests = bookingForm.querySelector('[name="special_requests"]');
        if (requestsEl) requestsEl.textContent = requests?.value?.trim() || 'None';

        const paymentEl = get('rvPayment');
        const paymentMethod = bookingForm.querySelector('[name="payment_method"]:checked');
        if (paymentEl) {
            paymentEl.textContent = paymentMethod ? (paymentMethod.value === 'pay_on_arrival' ? 'Pay at Hotel' : 'Pay Now') : '—';
        }

        // Optimistic price breakdown for the review panel (non-cart only).
        // Livewire's booking-summary-updated bridge corrects #rvGrandTotal
        // server-side (rate codes, calendar overrides).
        const opt = roomSelect?.options[roomSelect.selectedIndex];
        if (!isCartFlow && opt && opt.value) {
            const price = parseFloat(opt.dataset.price || 0);
            const capacity = parseInt(opt.dataset.capacity || 2);
            const baseOccupancy = Math.min(parseInt(opt.dataset.baseOccupancy || 2), capacity);
            const extraAdultFee = parseFloat(opt.dataset.extraAdultFee || 0);
            const extraChildFee = parseFloat(opt.dataset.extraChildFee || 0);
            const a = parseInt(document.getElementById('adults')?.value || 1);
            const c = parseInt(document.getElementById('children')?.value || 0);
            const n = Math.max(1, calculateNights());
            const extraGuests = Math.max(0, (a + c) - baseOccupancy);
            const extraAdults = Math.max(0, a - baseOccupancy);
            const extraChildren = Math.max(0, extraGuests - extraAdults);
            const guestFeePerNight = (extraAdults * extraAdultFee) + (extraChildren * extraChildFee);
            const baseTotal = price * n;
            const guestFeeTotal = guestFeePerNight * n;
            const addonTotal = computeAddonTotal();
            if (get('rvBaseTotal')) get('rvBaseTotal').textContent = formatMoney(baseTotal);
            const feeEl = get('rvGuestFee');
            const feeRow = get('rvGuestFeeRow');
            if (feeEl) {
                feeEl.textContent = guestFeeTotal > 0 ? formatMoney(guestFeeTotal) : 'Included';
                if (feeRow) feeRow.style.display = guestFeeTotal > 0 ? '' : 'none';
            }
            const addonsEl = get('rvAddons');
            if (addonsEl) addonsEl.textContent = addonTotal > 0 ? formatMoney(addonTotal) : 'None';
            if (get('rvGrandTotal')) get('rvGrandTotal').textContent = formatMoney(baseTotal + guestFeeTotal + addonTotal);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // Stepper navigation (progressive one-page wizard)
    // ─────────────────────────────────────────────────────────────
    const isCartFlow = bookingForm.hasAttribute('data-cart-flow');
    const stepSections = Array.from(bookingForm.querySelectorAll('.form-section[data-step]'));
    const stepperNav = document.getElementById('stepperNav');
    const stepperBack = document.getElementById('stepperBack');
    const stepperNext = document.getElementById('stepperNext');
    const reviewStripEl = document.getElementById('reviewStrip');
    const bookingCtaEl = document.getElementById('bookingCta');
    const draftStatusEl = document.getElementById('draftStatus');

    // Fields that make a step "complete" for resume positioning. The review
    // step has no editable fields, so it is always complete and can never be
    // the resume landing point ahead of a genuinely incomplete step.
    const stepFieldMap = isCartFlow ? [
        { fields: ['guest_name', 'guest_email', 'guest_phone', 'guest_gender', 'guest_address', 'guest_nationality'] },
        { fields: ['guest_id_type', 'guest_id_number'] },
        { fields: [] },
        { fields: [] },
        { fields: [] },
    ] : [
        { fields: ['check_in_date', 'check_out_date', 'room_type_id'] },
        { fields: ['guest_name', 'guest_email', 'guest_phone', 'guest_gender', 'guest_address', 'guest_nationality'] },
        { fields: ['guest_id_type', 'guest_id_number'] },
        { fields: [] },
        { fields: [] },
        { fields: [] },
    ];

    function getFurthestFilledStep() {
        if (stepSections.length === 0) return 0;
        let idx = 0;
        for (let i = 0; i < stepFieldMap.length; i++) {
            const allFilled = stepFieldMap[i].fields.every(name => {
                const el = bookingForm.querySelector('[name="' + name + '"]');
                return el && el.value && el.value.trim() !== '';
            });
            if (!allFilled) { idx = i; break; }
            idx = i + 1;
        }
        return Math.min(idx, stepSections.length - 1);
    }

    function stepIsValid(n) {
        const section = stepSections[n - 1];
        if (!section) return true;

        // Step 1 (non-cart): a room must be selected.
        if (n === 1 && !isCartFlow) {
            const selectedCard = document.querySelector('.room-card.selected');
            if (!roomSelect?.value && !selectedCard) {
                const alertEl = document.getElementById('availabilityAlert');
                if (alertEl) {
                    alertEl.classList.remove('d-none', 'alert-success');
                    alertEl.classList.add('alert-danger');
                    alertEl.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>Please select a room type to continue.';
                }
                return false;
            }
        }

        const invalidEl = Array.from(section.querySelectorAll('input, select, textarea'))
            .find(el => !el.disabled && el.hasAttribute('required') && !el.checkValidity());
        if (invalidEl) {
            invalidEl.reportValidity();
            invalidEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
            return false;
        }
        return true;
    }

    function showStep(n) {
        if (stepSections.length === 0) return;
        const last = stepSections.length;
        currentStep = Math.max(1, Math.min(n, last));
        maxVisitedStep = Math.max(maxVisitedStep, currentStep);

        stepSections.forEach((section, i) => {
            const isCurrent = (i + 1) === currentStep;
            section.style.display = isCurrent ? '' : 'none';
            section.setAttribute('aria-hidden', isCurrent ? 'false' : 'true');
            if (isCurrent) {
                section.classList.add('visible');
                section.querySelectorAll('.reveal').forEach(r => r.classList.add('visible'));
            }
        });

        // Nav buttons.
        if (stepperNav) {
            stepperBack.style.visibility = currentStep > 1 ? 'visible' : 'hidden';
            stepperBack.style.display = currentStep > 1 ? '' : 'none';
            if (currentStep < last) {
                stepperNext.style.display = '';
                if (currentStep === last - 1) {
                    // The review step — prompt the final confirmation.
                    stepperNext.innerHTML = 'Continue to Payment<i class="fas fa-arrow-right ms-2"></i>';
                } else {
                    stepperNext.innerHTML = 'Next<i class="fas fa-arrow-right ms-2"></i>';
                }
            } else {
                stepperNext.style.display = 'none';
            }
        }

        // Review strip + CTA only on the final step.
        const isLast = currentStep === last;
        if (!isCartFlow && reviewStripEl) {
            reviewStripEl.style.display = isLast ? '' : 'none';
        }
        if (bookingCtaEl) {
            bookingCtaEl.style.display = isLast ? '' : 'none';
        }
        // Mobile sticky Complete Booking CTA — reveal only on the final step.
        const stickyCta = document.getElementById('bookingCtaSticky');
        if (stickyCta) {
            stickyCta.classList.toggle('visible', isLast);
            stickyCta.setAttribute('aria-hidden', isLast ? 'false' : 'true');
        }

        updateStepIndicator();
        updateProgressBar();

        // Scroll back to the top of the form on step change (mobile).
        if (window.innerWidth < 768) {
            const formTop = document.querySelector('.booking-progress-bar') || bookingForm;
            if (formTop) {
                formTop.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        }
    }

    if (stepperNav && stepSections.length > 1) {
        stepperBack.addEventListener('click', () => showStep(currentStep - 1));
        stepperNext.addEventListener('click', () => {
            if (!stepIsValid(currentStep)) return;
            showStep(currentStep + 1);
        });
        // Landing position: resume at the first incomplete step (draft-aware).
        showStep(getFurthestFilledStep() + 1);
    }

    // Step indicator: completed/visited steps can be revisited by tapping them.
    const stepIndicatorEl = document.getElementById('stepIndicator');
    if (stepIndicatorEl) {
        stepIndicatorEl.addEventListener('click', function(e) {
            const item = e.target.closest('.step-item');
            if (!item) return;
            const step = parseInt(item.dataset.step, 10);
            if (!step || step >= currentStep) return; // Only allow going back
            if (step < maxVisitedStep) showStep(step);
        });
    }

    // Keyboard navigation: Enter in an input advances the step instead of
    // submitting the whole form (unless in a textarea/select).
    document.addEventListener('keydown', function(e) {
        if (e.key !== 'Enter' || e.target.tagName === 'TEXTAREA' || e.target.closest('.form-select')) return;
        const active = document.activeElement;
        if (active && active.tagName === 'INPUT' && active.type !== 'submit' && stepperNext && stepperNext.style.display !== 'none') {
            e.preventDefault();
            stepperNext.click();
        }
    });

    // Review step "Edit" links jump straight back to the relevant step.
    document.querySelectorAll('.review-edit-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const target = parseInt(this.dataset.jumpStep, 10);
            if (!target || target < 1 || target > stepSections.length) return;
            showStep(target);
            const heading = stepSections[target - 1]?.querySelector('.form-section-header');
            if (heading) heading.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });
    });

    // ─────────────────────────────────────────────────────────────
    // Auto-save draft to the session-backed store
    // ─────────────────────────────────────────────────────────────
    const DRAFT_FIELDS = [
        'guest_name', 'guest_email', 'guest_phone', 'guest_gender', 'guest_address',
        'guest_nationality', 'guest_dob', 'guest_id_type', 'guest_id_number',
        'adults', 'children', 'special_requests', 'payment_method',
        'room_type_id', 'room_unit_id', 'check_in_date', 'check_out_date',
    ];

    function collectDraft() {
        const data = {};
        DRAFT_FIELDS.forEach(name => {
            const el = bookingForm.querySelector('[name="' + name + '"]');
            if (!el) return;
            data[name] = el.value ?? '';
        });
        const accountEl = document.getElementById('createAccountToggle');
        if (accountEl) data.create_account = accountEl.checked ? 1 : 0;
        const selectedAddons = getSelectedAddonIds();
        data.addons = selectedAddons;
        return data;
    }

    function setDraftStatus(msg, isSaving) {
        if (!draftStatusEl) return;
        draftStatusEl.textContent = msg;
        draftStatusEl.classList.toggle('is-saving', !!isSaving);
        if (!isSaving) {
            clearTimeout(draftStatusEl._hideTimer);
            draftStatusEl._hideTimer = setTimeout(() => {
                draftStatusEl.textContent = '';
            }, 3000);
        }
    }

    function saveDraft() {
        if (_draftSaving) return;
        const serialized = JSON.stringify(collectDraft());
        if (serialized === _draftSaved) return;

        _draftSaving = true;
        setDraftStatus('Saving\u2026', true);

        fetch(cfg.saveDraftUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': cfg.csrfToken,
                    'Accept': 'application/json',
                },
                body: serialized,
            })
            .then(r => r.json())
            .then(data => {
                if (data && data.saved) {
                    _draftSaved = serialized;
                    setDraftStatus('\u2713 Progress saved', false);
                }
            })
            .catch(() => {})
            .finally(() => {
                _draftSaving = false;
                if (JSON.stringify(collectDraft()) !== _draftSaved) scheduleDraftSave();
            });
    }

    function scheduleDraftSave() {
        if (!cfg.saveDraftUrl) return;
        clearTimeout(_draftTimer);
        _draftTimer = setTimeout(saveDraft, 800);
    }
});
