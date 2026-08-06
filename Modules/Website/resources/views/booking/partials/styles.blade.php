@push('styles')
    <style>
        .booking-hero {
            background: linear-gradient(135deg, var(--brand-dark) 0%, #16213e 100%);
            padding: 2.5rem 0 1.5rem;
            margin-bottom: 2rem;
        }

        .booking-hero h1 {
            font-family: 'Playfair Display', serif;
            font-weight: 700;
            color: #fff;
            font-size: 1.75rem;
        }

        .booking-hero p {
            color: rgba(255, 255, 255, 0.65);
            font-size: 0.9rem;
        }

        .form-section {
            background: #fff;
            border-radius: 16px;
            border: 1px solid #eee;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .form-section-header {
            padding: 1.25rem 1.5rem;
            background: var(--brand-cream);
            border-bottom: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .form-section-header .section-icon {
            width: 36px;
            height: 36px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--brand-gold), var(--brand-gold-dark));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .form-section-header h5 {
            font-weight: 700;
            font-size: 0.95rem;
            color: var(--brand-dark);
            margin: 0;
        }

        .form-section-header .step-badge {
            margin-left: auto;
            background: var(--brand-gold);
            color: #fff;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.2rem 0.65rem;
            border-radius: 20px;
            letter-spacing: 0.5px;
        }

        .form-section-body {
            padding: 1.5rem;
        }

        .form-label {
            font-weight: 600;
            font-size: 0.85rem;
            color: #444;
            margin-bottom: 0.35rem;
        }

        .form-control,
        .form-select {
            border-radius: 10px;
            border: 1.5px solid #e5e7eb;
            padding: 0.65rem 1rem;
            font-size: 0.9rem;
            transition: all 0.25s ease;
            background: #fff;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: var(--brand-gold);
            box-shadow: 0 0 0 4px rgba(200, 161, 101, 0.12);
        }

        .form-control-lg {
            padding: 0.8rem 1.1rem;
            font-size: 0.95rem;
        }

        .form-control.bg-light.text-muted[readonly] {
            opacity: 0.8;
            cursor: not-allowed;
        }

        .card-summary {
            border: none;
            border-radius: 16px;
            overflow: hidden;
            position: sticky;
            top: 2rem;
            z-index: 10;
        }

        .card-summary .card-header {
            background: linear-gradient(135deg, var(--brand-dark), #16213e);
            padding: 1.25rem 1.5rem;
            border: none;
        }

        .card-summary .card-header h5 {
            color: #fff;
            font-weight: 700;
            font-size: 0.95rem;
            margin: 0;
        }

        .card-summary .card-body {
            background: #fff;
            padding: 1.5rem;
        }

        .summary-room-item {
            background: var(--brand-cream);
            border-radius: 12px;
            padding: 1rem;
            margin-bottom: 0.75rem;
            border: 1px solid #eee;
        }

        .summary-room-item:last-child {
            margin-bottom: 0;
        }

        .summary-room-item .room-icon {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            background: linear-gradient(135deg, var(--brand-gold), var(--brand-gold-dark));
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.1rem;
            flex-shrink: 0;
        }

        .summary-total-row {
            background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            border: 1px solid #bbf7d0;
        }

        .summary-total-row .amount {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            font-weight: 800;
            color: #16a34a;
            font-variant-numeric: tabular-nums;
            white-space: nowrap;
            display: inline-block;
            min-width: 8ch;
            text-align: right;
        }

        .booking-cta {
            background: var(--brand-cream);
            border: 1px solid #eee;
            border-radius: 16px;
            padding: 1.25rem;
            margin-top: 1.5rem;
        }

        .booking-cta .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px dashed #ddd;
        }

        .booking-cta .total-label {
            font-size: 0.85rem;
            color: #888;
            font-weight: 500;
        }

        .booking-cta .total-amount {
            font-size: 1.3rem;
            font-weight: 800;
            color: var(--brand-dark);
        }

        .booking-cta .total-amount .currency {
            font-size: 0.85rem;
            font-weight: 600;
            color: var(--brand-gold);
            margin-right: 0.15rem;
        }

        .btn-brand {
            background: linear-gradient(135deg, var(--brand-gold), var(--brand-gold-dark));
            color: #fff;
            border: none;
            border-radius: 12px;
            padding: 1rem 1.5rem;
            font-weight: 700;
            font-size: 1rem;
            letter-spacing: 0.3px;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .btn-brand::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent, rgba(255, 255, 255, 0.1));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .btn-brand:hover::before {
            opacity: 1;
        }

        .btn-brand:hover {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 8px 30px rgba(200, 161, 101, 0.4);
        }

        .btn-brand:active {
            transform: translateY(0);
            box-shadow: 0 4px 12px rgba(200, 161, 101, 0.3);
        }

        .btn-brand:disabled {
            opacity: 0.7;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .btn-brand:disabled::before {
            display: none;
        }

        .btn-brand .lock-icon {
            animation: lockPulse 2s ease-in-out infinite;
        }

        @keyframes lockPulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.6;
            }
        }

        .btn-brand.loading {
            pointer-events: none;
        }

        .secure-badge {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            margin-top: 0.75rem;
            margin-bottom: 0;
            font-size: 0.78rem;
            color: #999;
        }

        .secure-badge i {
            color: var(--brand-gold);
            font-size: 0.7rem;
        }

        .btn-outline-brand {
            border: 2px solid var(--brand-gold);
            color: var(--brand-gold);
            background: transparent;
            border-radius: 10px;
            font-weight: 600;
            font-size: 0.85rem;
            padding: 0.5rem 1rem;
            transition: all 0.25s ease;
        }

        .btn-outline-brand:hover {
            background: rgb(94, 95, 84);
            color: #000;
            text-decoration: none;
        }

        .payment-option {
            border: 2px solid #e5e7eb;
            border-radius: 14px;
            padding: 1.25rem;
            cursor: pointer;
            transition: all 0.25s ease;
            position: relative;
        }

        .payment-option:hover {
            border-color: var(--brand-gold-light);
            background: var(--brand-cream);
        }

        .payment-option input[type="radio"] {
            position: absolute;
            opacity: 0;
            /* parent will be styled via JS */
        }

        .payment-option:has(input[type="radio"]:checked) {
            border-color: var(--brand-gold);
            background: rgba(200, 161, 101, 0.06);
            box-shadow: 0 0 0 4px rgba(200, 161, 101, 0.12);
        }

        .payment-option.selected {
            border-color: var(--brand-gold);
            background: rgba(200, 161, 101, 0.06);
            box-shadow: 0 0 0 4px rgba(200, 161, 101, 0.12);
        }

        .payment-option.selected .check-indicator {
            border-color: var(--brand-gold);
            background: var(--brand-gold);
        }

        .payment-option.selected .check-indicator i {
            color: #fff;
            font-size: 0.65rem;
        }

        .payment-option .payment-icon {
            width: 44px;
            height: 44px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.15rem;
            flex-shrink: 0;
        }

        .payment-option .payment-icon.pay-now {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: #fff;
        }

        .payment-option .payment-icon.pay-later {
            background: linear-gradient(135deg, #6b7280, #4b5563);
            color: #fff;
        }

        .payment-option .check-indicator {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 2px solid #d1d5db;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            transition: all 0.25s ease;
        }

        .payment-option:has(input[type="radio"]:checked) .check-indicator {
            border-color: var(--brand-gold);
            background: var(--brand-gold);
        }

        .payment-option:has(input[type="radio"]:checked) .check-indicator i {
            color: #fff;
            font-size: 0.65rem;
        }

        .special-requests-box {
            background: var(--brand-cream);
            border-radius: 12px;
            border: 1px solid #eee;
            padding: 1.25rem;
        }

        .special-requests-box textarea {
            border-radius: 10px;
            border: 1.5px solid #e5e7eb;
            font-size: 0.9rem;
            resize: vertical;
            min-height: 80px;
        }

        .request-chip {
            background: #fff;
            border: 1.5px solid #e5e7eb;
            border-radius: 20px;
            padding: 0.3rem 0.75rem;
            font-size: 0.78rem;
            color: #555;
            cursor: pointer;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .request-chip:hover {
            border-color: var(--brand-gold-light);
            color: var(--brand-gold-dark);
        }

        .request-chip.active {
            background: var(--brand-gold);
            border-color: var(--brand-gold);
            color: #fff;
        }

        .special-requests-box textarea:focus {
            border-color: var(--brand-gold);
            box-shadow: 0 0 0 4px rgba(200, 161, 101, 0.12);
        }

        .create-account-box {
            background: linear-gradient(135deg, #f0f9ff, #e0f2fe);
            border: 1.5px dashed #93c5fd;
            border-radius: 12px;
            padding: 1.25rem;
        }

        .create-account-box .form-switch .form-check-input:checked {
            background-color: var(--brand-gold);
            border-color: var(--brand-gold);
        }

        .availability-banner {
            border-radius: 12px;
            font-size: 0.9rem;
        }

        .alert-danger {
            border-left: 4px solid #dc2626;
            border-radius: 12px;
        }

        .alert-info {
            background: rgba(200, 161, 101, 0.08);
            border: 1px solid var(--brand-gold-light);
            color: #5c4a2e;
            border-radius: 12px;
        }

        .alert-info a {
            color: var(--brand-gold-dark);
            font-weight: 700;
        }

        .alert-info a:hover {
            color: var(--brand-gold);
        }

        .guest-count-box {
            background: var(--brand-cream);
            border-radius: 12px;
            padding: 1rem 1.25rem;
            border: 1px solid #eee;
        }

        .occupancy-bar {
            height: 6px;
            background: #e5e7eb;
            border-radius: 3px;
            overflow: hidden;
        }

        .occupancy-bar-fill {
            height: 100%;
            border-radius: 3px;
            background: linear-gradient(90deg, var(--brand-gold), var(--brand-gold-dark));
            transition: width 0.4s ease, background 0.3s ease;
        }

        .occupancy-bar-fill.occupancy-warning {
            background: linear-gradient(90deg, #f59e0b, #d97706);
        }

        .occupancy-bar-fill.occupancy-full {
            background: linear-gradient(90deg, #ef4444, #dc2626);
        }

        .occupancy-label {
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--brand-dark);
        }

        .occupancy-count {
            font-size: 0.75rem;
            font-weight: 700;
            color: var(--brand-gold-dark);
        }

        .occupancy-fee-hint {
            font-size: 0.75rem;
            color: #d97706;
            font-weight: 600;
        }

        .capacity-pill {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            background: var(--brand-cream);
            border: 1.5px solid var(--brand-gold-light);
            color: var(--brand-gold-dark);
            padding: 0.35rem 0.75rem;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 600;
            white-space: nowrap;
            transition: all 0.3s ease;
        }

        .capacity-pill.has-room {
            background: linear-gradient(135deg, var(--brand-gold), var(--brand-gold-dark));
            color: #fff;
            border-color: transparent;
        }

        .capacity-pill.is-full {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: #fff;
            border-color: transparent;
        }

        .capacity-pill.is-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: #fff;
            border-color: transparent;
        }

        @media (max-width: 991px) {
            .booking-hero {
                padding: 1.5rem 0 1rem;
            }

            .booking-hero h1 {
                font-size: 1.35rem;
            }

            .form-section-body {
                padding: 1.25rem;
            }

            .payment-option {
                padding: 1rem;
            }
        }

        /* ── Fun UX: live progress, chips, steppers, reveal, confetti ── */
        .booking-progress-bar {
            height: 8px;
            border-radius: 20px;
            background: #eef2f6;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }

        .booking-progress-bar .fill {
            height: 100%;
            width: 0%;
            border-radius: 20px;
            background: linear-gradient(90deg, var(--brand-gold), var(--brand-gold-dark));
            transition: width 0.45s cubic-bezier(.22, 1, .36, 1);
        }

        .progress-caption {
            font-size: 0.8rem;
            color: var(--brand-gold-dark);
            font-weight: 600;
            margin-bottom: 0.5rem;
            min-height: 1.1rem;
        }

        .date-chips {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-bottom: 0.85rem;
        }

        .date-chip {
            border: 1.5px solid var(--brand-gold-light);
            background: #fff;
            color: var(--brand-dark);
            border-radius: 30px;
            padding: 0.4rem 0.85rem;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .date-chip:hover {
            background: var(--brand-cream);
            transform: translateY(-1px);
        }

        .date-chip.active {
            background: linear-gradient(135deg, var(--brand-gold), var(--brand-gold-dark));
            color: #fff;
            border-color: transparent;
            box-shadow: 0 6px 18px rgba(200, 161, 101, 0.35);
        }

        .guest-stepper {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            border: 1.5px solid #e5e7eb;
            border-radius: 10px;
            padding: 0.25rem 0.4rem;
        }

        .guest-stepper button {
            width: 34px;
            height: 34px;
            border-radius: 8px;
            border: none;
            background: var(--brand-cream);
            color: var(--brand-gold-dark);
            font-size: 1.1rem;
            font-weight: 800;
            line-height: 1;
            cursor: pointer;
            transition: all 0.18s ease;
        }

        .guest-stepper button:hover {
            background: var(--brand-gold-light);
            color: #fff;
        }

        .guest-stepper button:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        .guest-stepper input {
            width: 46px;
            border: none !important;
            text-align: center;
            font-weight: 800;
            font-size: 0.95rem;
            background: transparent;
            padding: 0;
        }

        .js .reveal {
            opacity: 0;
            transform: translateY(14px);
            transition: opacity 0.5s ease, transform 0.5s cubic-bezier(.22, 1, .36, 1);
        }

        .js .reveal.visible {
            opacity: 1;
            transform: none;
        }

        .summary-total-row .amount {
            transition: color 0.2s ease;
        }

        .amount-pulse {
            animation: amountPulse 0.5s ease;
        }

        @keyframes amountPulse {
            0% {
                transform: scale(1);
            }

            40% {
                transform: scale(1.12);
                color: var(--brand-gold-dark);
            }

            100% {
                transform: scale(1);
            }
        }

        .payment-hint {
            font-size: 0.74rem;
            color: #999;
            margin-top: 0.5rem;
        }

        #confettiCanvas {
            position: fixed;
            inset: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 9999;
        }

        @media (prefers-reduced-motion: reduce) {

            .reveal,
            .booking-progress-bar .fill,
            .amount-pulse {
                transition: none !important;
                animation: none !important;
            }
        }

        /* ── Step indicator ── */
        .step-indicator {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0;
            margin-bottom: 1.75rem;
            padding: 0 0.5rem;
        }

        .step-indicator .step-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.3rem;
            position: relative;
            flex: 1;
            max-width: 90px;
        }

        .step-indicator .step-dot {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.75rem;
            font-weight: 800;
            border: 2px solid #d1d5db;
            background: #fff;
            color: #999;
            transition: all 0.3s ease;
            flex-shrink: 0;
        }

        .step-indicator .step-item.active .step-dot {
            border-color: var(--brand-gold);
            background: linear-gradient(135deg, var(--brand-gold), var(--brand-gold-dark));
            color: #fff;
            box-shadow: 0 4px 12px rgba(200, 161, 101, 0.35);
        }

        .step-indicator .step-item.completed .step-dot {
            border-color: #16a34a;
            background: #16a34a;
            color: #fff;
        }

        .step-indicator .step-item .step-label {
            font-size: 0.6rem;
            color: #999;
            text-align: center;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            line-height: 1.2;
        }

        .step-indicator .step-item.active .step-label {
            color: var(--brand-dark);
        }

        .step-indicator .step-item.completed .step-label {
            color: #16a34a;
        }

        .step-connector {
            flex: 1;
            height: 2px;
            background: #e5e7eb;
            min-width: 12px;
            margin: 0 2px;
            margin-bottom: 1.55rem;
            transition: background 0.3s ease;
        }

        .step-connector.completed {
            background: #16a34a;
        }

        /* ── Room type cards ── */
        .room-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 1rem;
        }

        .room-card {
            border: 2px solid #e5e7eb;
            border-radius: 14px;
            overflow: hidden;
            cursor: pointer;
            transition: all 0.25s ease;
            background: #fff;
            position: relative;
        }

        .room-card:hover,
        .room-card:focus-visible {
            border-color: var(--brand-gold-light);
            transform: translateY(-2px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.07);
            outline: none;
        }

        .room-card.selected {
            border-color: var(--brand-gold);
            box-shadow: 0 0 0 3px rgba(200, 161, 101, 0.2);
        }

        .room-card .card-img-top {
            height: 120px;
            object-fit: cover;
            background: var(--brand-cream);
        }

        .room-card .card-body {
            padding: 0.75rem 1rem 0.9rem;
        }

        .room-card .card-body .card-title {
            font-weight: 700;
            font-size: 0.92rem;
            color: var(--brand-dark);
        }

        .room-card .card-body .card-price {
            font-size: 1rem;
            font-weight: 800;
            color: var(--brand-gold-dark);
        }

        .room-card .card-body .card-meta {
            font-size: 0.75rem;
            color: #999;
        }

        .room-card .selected-badge {
            position: absolute;
            top: 0.5rem;
            right: 0.5rem;
            background: var(--brand-gold);
            color: #fff;
            font-size: 0.6rem;
            font-weight: 700;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            opacity: 0;
            transition: opacity 0.25s ease;
        }

        .room-card.selected .selected-badge {
            opacity: 1;
        }

        .room-card .capacity-badge {
            position: absolute;
            top: 0.5rem;
            left: 0.5rem;
            background: rgba(0, 0, 0, 0.6);
            color: #fff;
            font-size: 0.65rem;
            font-weight: 700;
            padding: 0.2rem 0.6rem;
            border-radius: 20px;
            backdrop-filter: blur(4px);
        }

        /* ── Review strip ── */
        .review-strip {
            background: var(--brand-cream);
            border-radius: 14px;
            border: 1px solid #eee;
            padding: 0.9rem 1.2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 0.4rem 1.2rem;
            margin-bottom: 1rem;
        }

        .review-strip .rv-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.8rem;
            color: #666;
        }

        .review-strip .rv-item i {
            color: var(--brand-gold);
            width: 15px;
            text-align: center;
            font-size: 0.75rem;
        }

        .review-strip .rv-item .rv-value {
            font-weight: 700;
            color: var(--brand-dark);
        }

        .review-strip .rv-divider {
            width: 1px;
            height: 28px;
            background: #e5e7eb;
            flex-shrink: 0;
        }

        /* ── Review step panel ── */
        .review-note {
            font-size: 0.8rem;
            color: #666;
            background: var(--brand-cream);
            border: 1px solid #eee;
            border-radius: 10px;
            padding: 0.6rem 0.9rem;
            margin-bottom: 1.25rem;
        }

        .review-panel {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .review-group {
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 0.9rem 1.1rem;
        }

        .review-group-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
            margin-bottom: 0.6rem;
            padding-bottom: 0.6rem;
            border-bottom: 1px dashed #e5e7eb;
        }

        .review-group-title {
            font-weight: 700;
            font-size: 0.82rem;
            color: var(--brand-dark);
            text-transform: uppercase;
            letter-spacing: 0.4px;
        }

        .review-group-title i {
            color: var(--brand-gold);
        }

        .review-edit-btn {
            background: transparent;
            border: 1.5px solid var(--brand-gold-light);
            color: var(--brand-gold-dark);
            border-radius: 20px;
            font-size: 0.72rem;
            font-weight: 700;
            padding: 0.25rem 0.7rem;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            white-space: nowrap;
        }

        .review-edit-btn:hover {
            background: var(--brand-gold);
            color: #fff;
            border-color: var(--brand-gold);
        }

        .review-row {
            display: flex;
            justify-content: space-between;
            align-items: baseline;
            gap: 1rem;
            padding: 0.3rem 0;
            font-size: 0.85rem;
        }

        .review-row > span {
            color: #777;
            font-weight: 500;
            flex-shrink: 0;
        }

        .review-row > strong {
            color: var(--brand-dark);
            text-align: right;
            font-weight: 600;
            word-break: break-word;
        }

        .review-price {
            background: linear-gradient(135deg, #f0fdf4, #ecfdf5);
            border: 1px solid #bbf7d0;
            border-radius: 12px;
            padding: 0.8rem 1.1rem;
        }

        .review-price .review-total {
            border-top: 1px dashed #a7f3d0;
            margin-top: 0.35rem;
            padding-top: 0.7rem;
            font-size: 1rem;
        }

        .review-price .review-total > span {
            font-weight: 700;
            color: var(--brand-dark);
        }

        .review-price .review-total > strong {
            font-family: 'Playfair Display', serif;
            font-weight: 800;
            color: #16a34a;
            font-size: 1.15rem;
            white-space: nowrap;
        }

        /* ── Stepper navigation ── */
        .stepper-nav {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1.25rem;
        }

        .stepper-nav .btn {
            border-radius: 12px;
            font-weight: 700;
            padding: 0.7rem 1.75rem;
        }

        /* ── Auto-save indicator ── */
        .draft-saved-indicator {
            position: fixed;
            bottom: 1.25rem;
            right: 1.25rem;
            z-index: 1050;
            background: #fff;
            border: 1px solid #bbf7d0;
            color: #15803d;
            font-size: 0.78rem;
            font-weight: 600;
            padding: 0.5rem 0.9rem;
            border-radius: 30px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
            opacity: 0;
            transform: translateY(8px);
            transition: opacity 0.3s ease, transform 0.3s ease;
            pointer-events: none;
        }

        .draft-saved-indicator:not(:empty) {
            opacity: 1;
            transform: none;
        }

        .draft-saved-indicator.is-saving {
            color: #9a6a1f;
            border-color: #fde68a;
        }

        @media (max-width: 576px) {
            .room-cards {
                grid-template-columns: 1fr;
            }

            .step-indicator {
                gap: 0;
                overflow-x: auto;
                padding-bottom: 0.25rem;
                justify-content: flex-start;
            }

            .step-indicator .step-item {
                flex: 0 0 auto;
                min-width: 48px;
                max-width: 60px;
            }

            .step-indicator .step-dot {
                width: 26px;
                height: 26px;
                font-size: 0.65rem;
            }

            .step-indicator .step-item .step-label {
                font-size: 0.6rem;
                letter-spacing: 0;
                overflow: hidden;
                text-overflow: ellipsis;
                white-space: nowrap;
            }

            .step-connector {
                flex: 0 0 8px;
                min-width: 6px;
                margin-bottom: 1.3rem;
            }

            .review-strip {
                flex-direction: column;
                align-items: stretch;
                gap: 0.4rem;
            }

            .review-strip .rv-divider {
                display: none;
            }

            .review-strip .rv-item {
                font-size: 0.75rem;
            }
        }

        /* ── Add-on / upsell cards ── */
        .addons-box {
            background: var(--brand-cream);
            border: 1px solid #eee;
            border-radius: 12px;
            padding: 1.25rem;
        }

        .addon-card {
            position: relative;
            display: block;
            background: #fff;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            padding: 0.85rem 1rem 0.85rem 2.75rem;
            cursor: pointer;
            transition: all 0.22s ease;
            height: 100%;
            overflow: hidden;
        }

        .addon-card:hover,
        .addon-card:focus-within {
            border-color: var(--brand-gold-light);
            transform: translateY(-2px);
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06);
        }

        .addon-card.selected {
            border-color: var(--brand-gold);
            box-shadow: 0 0 0 3px rgba(200, 161, 101, 0.18);
        }

        .addon-card .addon-checkbox {
            position: absolute;
            opacity: 0;
            pointer-events: none;
        }

        .addon-card .addon-check {
            position: absolute;
            top: 0.85rem;
            left: 0.85rem;
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 2px solid #d1d5db;
            background: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 0.65rem;
            transition: all 0.2s ease;
        }

        .addon-card.selected .addon-check {
            border-color: var(--brand-gold);
            background: var(--brand-gold);
        }

        .addon-card .addon-body {
            display: flex;
            flex-direction: column;
            gap: 0.2rem;
            cursor: pointer;
        }

        .addon-card .addon-icon {
            color: var(--brand-gold);
            font-size: 1.1rem;
        }

        .addon-card .addon-name {
            font-weight: 700;
            font-size: 0.85rem;
            color: var(--brand-dark);
        }

        .addon-card .addon-desc {
            font-size: 0.72rem;
            color: #888;
            line-height: 1.35;
        }

        .addon-card .addon-price {
            font-weight: 800;
            font-size: 0.9rem;
            color: #16a34a;
            font-variant-numeric: tabular-nums;
        }

        .addon-card .addon-price-note {
            font-size: 0.68rem;
            font-weight: 600;
            color: #999;
        }
    </style>
@endpush
