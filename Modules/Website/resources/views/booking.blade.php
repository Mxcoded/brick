@extends('website::layouts.master')

@section('title', 'Complete Your Reservation')

@push('styles')
    <style>
        :root {
            --brand-gold: #C8A165;
            --brand-gold-light: #d4b07a;
            --brand-gold-dark: #b08c54;
            --brand-dark: #1a1a2e;
            --brand-cream: #faf8f5;
        }

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
            background: var(--brand-gold);
            color: #fff;
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
        }

        .payment-option input[type="radio"]:checked+.payment-content {
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

        .max-capacity-badge {
            background: linear-gradient(135deg, var(--brand-gold), var(--brand-gold-dark));
            color: #fff;
            padding: 0.4rem 0.85rem;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 700;
            box-shadow: 0 4px 12px rgba(200, 161, 101, 0.3);
            animation: capacityBadgePulse 2s ease-in-out infinite;
        }

        @keyframes capacityBadgePulse {
            0%, 100% { box-shadow: 0 4px 12px rgba(200, 161, 101, 0.3); }
            50% { box-shadow: 0 4px 18px rgba(200, 161, 101, 0.5); }
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

            .room-card:hover {
                border-color: var(--brand-gold-light);
                transform: translateY(-2px);
                box-shadow: 0 8px 24px rgba(0, 0, 0, 0.07);
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
                    font-size: 0.48rem;
                    letter-spacing: 0;
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
    </style>
@endpush

@section('content')
    <canvas id="confettiCanvas"></canvas>
    <div class="booking-hero">
        <div class="container">
            <h1><i class="fas fa-pen-alt me-2" style="color: var(--brand-gold);"></i>Complete Your Reservation</h1>
            <p class="mb-0">Fill in your details below to secure your stay at Brickspoint Boutique Aparthotel</p>
        </div>
    </div>

    <div class="container pb-5">

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="booking-progress-bar" aria-hidden="true">
                    <div class="fill" id="bookingProgressFill"></div>
                </div>
                <div class="progress-caption" id="bookingProgressCaption"></div>

                <div class="step-indicator" id="stepIndicator">
                    <div class="step-item active" data-step="1">
                        <div class="step-dot">1</div>
                        <div class="step-label">Dates</div>
                    </div>
                    <div class="step-connector"></div>
                    <div class="step-item" data-step="2">
                        <div class="step-dot">2</div>
                        <div class="step-label">Guest</div>
                    </div>
                    <div class="step-connector"></div>
                    <div class="step-item" data-step="3">
                        <div class="step-dot">3</div>
                        <div class="step-label">ID</div>
                    </div>
                    <div class="step-connector"></div>
                    <div class="step-item" data-step="4">
                        <div class="step-dot">4</div>
                        <div class="step-label">Options</div>
                    </div>
                    <div class="step-connector"></div>
                    <div class="step-item" data-step="5">
                        <div class="step-dot">5</div>
                        <div class="step-label">Payment</div>
                    </div>
                </div>

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show availability-banner" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show availability-banner" role="alert">
                        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger alert-dismissible fade show availability-banner" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i> Please fix the following errors:
                        <ul class="mb-0 ps-3 mt-2">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                <div id="availabilityAlert" class="alert d-none availability-banner"></div>

                @php
                    $useCartFlow = isset($useCart) && $useCart && !empty($cart['items']);
                @endphp

                <form action="{{ route('website.booking.store') }}" method="POST" id="bookingForm" @if($useCartFlow) data-cart-flow="1" @endif>
                    @csrf

                    <div style="position: absolute; left: -9999px;" aria-hidden="true">
                        <input type="text" name="website" tabindex="-1" autocomplete="off" value="">
                    </div>
                    <input type="hidden" name="register_time" value="{{ time() }}">

                    @php
                        $reqRoomTypeId = old(
                            'room_type_id',
                            request('room_type_id', request('room_id', $selectedRoomType->id ?? '')),
                        );
                        $reqCheckIn = $useCartFlow
                            ? $cart['check_in'] ?? ''
                            : old('check_in_date', request('check_in_date', request('check_in')));
                        $reqCheckOut = $useCartFlow
                            ? $cart['check_out'] ?? ''
                            : old('check_out_date', request('check_out_date', request('check_out')));
                        $hasPhone = Auth::check() && $guest && !empty($guest->contact_number);
                        $hasGender = Auth::check() && $guest && !empty($guest->gender);
                        $hasAddress = Auth::check() && $guest && !empty($guest->home_address);
                        $hasIdType = Auth::check() && $guest && !empty($guest->identification_type);
                        $hasIdNumber = Auth::check() && $guest && !empty($guest->identification_number);
                        $hasNationality = Auth::check() && $guest && !empty($guest->nationality);
                        $hasDob = Auth::check() && $guest && !is_null($guest->birthday);
                    @endphp

                    @if ($useCartFlow)
                        <div class="alert alert-info availability-banner mb-4">
                            <div class="d-flex align-items-center">
                                <i class="fas fa-shopping-cart me-3 fa-lg"></i>
                                <div>
                                    <strong>{{ $cart['total_rooms'] }} room(s) selected</strong> for
                                    {{ \Carbon\Carbon::parse($cart['check_in'])->format('M d') }} -
                                    {{ \Carbon\Carbon::parse($cart['check_out'])->format('M d, Y') }}
                                    ({{ $cart['nights'] }} {{ Str::plural('night', $cart['nights']) }})
                                </div>
                                <a href="{{ route('website.book') }}"
                                    class="ms-auto text-nowrap btn-outline-brand btn-sm">Modify</a>
                            </div>
                        </div>
                    @else
                        <div class="form-section reveal">
                            <div class="form-section-header">
                                <div class="section-icon"><i class="fas fa-calendar-alt"></i></div>
                                <h5>Stay Dates &amp; Room</h5>
                                <span class="step-badge">Step 1</span>
                            </div>
                            <div class="form-section-body">
                                <div class="date-chips" id="dateChips">
                                    <span class="text-muted small me-1 align-self-center">Quick pick:</span>
                                    <button type="button" class="date-chip" data-offset="0">Tonight</button>
                                    <button type="button" class="date-chip" data-offset="1">Tomorrow</button>
                                    <button type="button" class="date-chip" data-offset="3">+3 Days</button>
                                    <button type="button" class="date-chip" data-offset="7">+1 Week</button>
                                </div>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Check-In Date <span class="text-danger">*</span></label>
                                        <input type="date" name="check_in_date" id="check_in_date"
                                            class="form-control form-control-lg" value="{{ $reqCheckIn }}"
                                            min="{{ date('Y-m-d') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label">Check-Out Date <span class="text-danger">*</span></label>
                                        <input type="date" name="check_out_date" id="check_out_date"
                                            class="form-control form-control-lg" value="{{ $reqCheckOut }}"
                                            min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Choose Your Room <span class="text-danger">*</span></label>
                                        <div class="room-cards mb-2" id="roomCards">
                                            @foreach ($roomTypes as $roomOption)
                                                <div class="room-card {{ $reqRoomTypeId == $roomOption->id ? 'selected' : '' }}"
                                                    data-room-type-id="{{ $roomOption->id }}"
                                                    data-price="{{ $roomOption->price }}"
                                                    data-image="{{ $roomOption->image_url }}"
                                                    data-name="{{ $roomOption->name }}"
                                                    data-capacity="{{ $roomOption->capacity }}"
                                                    data-base-occupancy="{{ $roomOption->base_occupancy ?? 2 }}"
                                                    data-extra-adult-fee="{{ $roomOption->extra_adult_fee ?? 0 }}"
                                                    data-extra-child-fee="{{ $roomOption->extra_child_fee ?? 0 }}"
                                                    data-units="{{ $roomOption->units_count }}">
                                                    <img src="{{ $roomOption->image_url ?? asset('images/default-room.jpg') }}"
                                                        alt="{{ $roomOption->name }}" class="card-img-top" loading="lazy"
                                                        onerror="this.style.display='none'">
                                                    <span class="capacity-badge"><i class="fas fa-user-friends me-1"></i>{{ $roomOption->capacity }}</span>
                                                    <span class="selected-badge"><i class="fas fa-check me-1"></i>Selected</span>
                                                    <div class="card-body">
                                                        <div class="card-title">{{ $roomOption->name }}</div>
                                                        <div class="card-price">₦{{ number_format($roomOption->price, 2) }}<span class="card-meta"> /night</span></div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <select name="room_type_id" id="room_type_id" class="d-none">
                                            <option value="" disabled {{ empty($reqRoomTypeId) ? 'selected' : '' }}></option>
                                            @foreach ($roomTypes as $roomOption)
                                                <option value="{{ $roomOption->id }}"
                                                    data-price="{{ $roomOption->price }}"
                                                    data-image="{{ $roomOption->image_url }}"
                                                    data-name="{{ $roomOption->name }}"
                                                    data-capacity="{{ $roomOption->capacity }}"
                                                    data-base-occupancy="{{ $roomOption->base_occupancy ?? 2 }}"
                                                    data-extra-adult-fee="{{ $roomOption->extra_adult_fee ?? 0 }}"
                                                    data-extra-child-fee="{{ $roomOption->extra_child_fee ?? 0 }}"
                                                    data-units="{{ $roomOption->units_count }}"
                                                    {{ $reqRoomTypeId == $roomOption->id ? 'selected' : '' }}>
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12" id="roomUnitSection" style="display: none;">
                                        <label class="form-label">Specific Room Unit <span
                                                class="text-muted fw-normal">(optional)</span></label>
                                        <select name="room_unit_id" id="room_unit_id" class="form-select">
                                            <option value="">-- Auto-assign at check-in --</option>
                                        </select>
                                        <div class="form-text text-muted mt-1">
                                            <i class="fas fa-info-circle me-1"></i> Choose a specific room or leave blank
                                            for auto-assignment.
                                        </div>
                                        <div id="unitLoadingSpinner" class="text-center py-2 d-none">
                                            <span class="spinner-border spinner-border-sm"
                                                style="color: var(--brand-gold);"></span> Loading available units...
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif

                    <div class="form-section reveal">
                        <div class="form-section-header">
                            <div class="section-icon"><i class="fas fa-user"></i></div>
                            <h5>Guest Information</h5>
                            <span class="step-badge">Step 2</span>
                        </div>
                        <div class="form-section-body">
                            @php
                                $hasName = Auth::check() && $guest && !empty($guest->full_name);
                                $hasEmail = Auth::check() && $guest && !empty($guest->email);
                            @endphp
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">Full Name <span class="text-danger">*</span></label>
                                    <input type="text" name="guest_name"
                                        class="form-control {{ $hasName ? 'bg-light text-muted' : '' }}"
                                        value="{{ old('guest_name', $guest->full_name ?? (Auth::user()->name ?? '')) }}"
                                        required {{ $hasName ? 'readonly' : '' }} placeholder="e.g. John Doe">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="guest_email" id="guest_email"
                                        class="form-control {{ $hasEmail ? 'bg-light text-muted' : '' }}"
                                        value="{{ old('guest_email', $guest->email ?? (Auth::user()->email ?? '')) }}"
                                        required {{ $hasEmail ? 'readonly' : '' }} placeholder="your@email.com">
                                    <div id="emailFeedback" class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Phone Number <span class="text-danger">*</span></label>
                                    <input type="tel" name="guest_phone"
                                        class="form-control phone-input {{ $hasPhone ? 'bg-light text-muted' : '' }}"
                                        value="{{ old('guest_phone', $guest->contact_number ?? '') }}" required
                                        {{ $hasPhone ? 'readonly' : '' }} placeholder="+234 800 000 0000">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                                    @php $selGender = old('guest_gender', $guest->gender ?? ''); @endphp
                                    @if ($hasGender)
                                        <input type="hidden" name="guest_gender" value="{{ $selGender }}">
                                    @endif
                                    <select name="guest_gender"
                                        class="form-select {{ $hasGender ? 'bg-light text-muted' : '' }}" required
                                        {{ $hasGender ? 'disabled' : '' }}>
                                        <option value="" disabled {{ empty($selGender) ? 'selected' : '' }}>Select
                                            Gender...</option>
                                        <option value="male" {{ $selGender == 'male' ? 'selected' : '' }}>Male</option>
                                        <option value="female" {{ $selGender == 'female' ? 'selected' : '' }}>Female
                                        </option>
                                        <option value="other" {{ $selGender == 'other' ? 'selected' : '' }}>Other
                                        </option>
                                    </select>
                                </div>
                                <div class="col-12">
                                    <label class="form-label">Home Address <span class="text-danger">*</span></label>
                                    <input type="text" name="guest_address"
                                        class="form-control {{ $hasAddress ? 'bg-light text-muted' : '' }}"
                                        placeholder="Street Address, City, State"
                                        value="{{ old('guest_address', $guest->home_address ?? '') }}" required
                                        {{ $hasAddress ? 'readonly' : '' }}>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Nationality <span class="text-danger">*</span></label>
                                    <input type="text" name="guest_nationality"
                                        class="form-control {{ $hasNationality ? 'bg-light text-muted' : '' }}"
                                        value="{{ old('guest_nationality', $guest->nationality ?? 'Nigeria') }}" required
                                        {{ $hasNationality ? 'readonly' : '' }}>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">Date of Birth</label>
                                    <input type="date" name="guest_dob"
                                        class="form-control {{ $hasDob ? 'bg-light text-muted' : '' }}"
                                        value="{{ old('guest_dob', $guest && $guest->birthday ? $guest->birthday->format('Y-m-d') : '') }}"
                                        {{ $hasDob ? 'readonly' : '' }}>
                                    <div class="form-text text-muted mt-1"><i class="fas fa-info-circle me-1"></i>
                                        Required for age verification</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-section reveal">
                        <div class="form-section-header">
                            <div class="section-icon"><i class="fas fa-id-card"></i></div>
                            <h5>Identity Verification</h5>
                            <span class="step-badge">Step 3</span>
                        </div>
                        <div class="form-section-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label">ID Card Type <span class="text-danger">*</span></label>
                                    @php $selIdType = old('guest_id_type', $guest->identification_type ?? ''); @endphp
                                    @if ($hasIdType)
                                        <input type="hidden" name="guest_id_type" value="{{ $selIdType }}">
                                    @endif
                                    <select name="guest_id_type"
                                        class="form-select {{ $hasIdType ? 'bg-light text-muted' : '' }}" required
                                        {{ $hasIdType ? 'disabled' : '' }}>
                                        <option value="" disabled {{ empty($selIdType) ? 'selected' : '' }}>Select
                                            ID Type...</option>
                                        <option value="International Passport"
                                            {{ $selIdType == 'International Passport' ? 'selected' : '' }}>International
                                            Passport</option>
                                        <option value="NIN" {{ $selIdType == 'NIN' ? 'selected' : '' }}>NIN (National
                                            ID)</option>
                                        <option value="Drivers License"
                                            {{ $selIdType == 'Drivers License' ? 'selected' : '' }}>Driver's License
                                        </option>
                                        <option value="Voters Card" {{ $selIdType == 'Voters Card' ? 'selected' : '' }}>
                                            Voter's Card</option>
                                        <option value="Other" {{ $selIdType == 'Other' ? 'selected' : '' }}>Other Govt ID
                                        </option>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label">ID Number <span class="text-danger">*</span></label>
                                    <input type="text" name="guest_id_number"
                                        class="form-control {{ $hasIdNumber ? 'bg-light text-muted' : '' }}"
                                        placeholder="e.g. A01234567"
                                        value="{{ old('guest_id_number', $guest->identification_number ?? '') }}" required
                                        {{ $hasIdNumber ? 'readonly' : '' }}>
                                </div>
                            </div>
                            <div class="form-text text-muted mt-2">
                                <i class="fas fa-shield-alt me-1" style="color: var(--brand-gold);"></i>
                                Your ID information is collected for check-in verification and is securely stored.
                            </div>
                        </div>
                    </div>

                    <div class="form-section reveal">
                        <div class="form-section-header">
                            <div class="section-icon"><i class="fas fa-users"></i></div>
                            <h5>Guests &amp; Requests</h5>
                            <span class="step-badge">Step 4</span>
                        </div>
                        <div class="form-section-body">
                            <div class="row g-3">
                                <div class="col-12">
                                    <div class="guest-count-box">
                                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                                            <div class="d-flex align-items-center gap-4 flex-wrap">
                                                <div class="d-flex align-items-center gap-2">
                                                    <label class="form-label mb-0 text-nowrap">Adults</label>
                                                    <div class="guest-stepper">
                                                        <button type="button" class="step-dec" data-target="adults"
                                                            aria-label="Decrease adults">−</button>
                                                        <input type="number" name="adults" id="adults"
                                                            value="{{ old('adults', 1) }}" min="1" max="{{ $selectedRoomType->capacity ?? 2 }}"
                                                            required readonly>
                                                        <button type="button" class="step-inc" data-target="adults"
                                                            aria-label="Increase adults">+</button>
                                                    </div>
                                                </div>
                                                <div class="d-flex align-items-center gap-2">
                                                    <label class="form-label mb-0 text-nowrap">Children</label>
                                                    <div class="guest-stepper">
                                                        <button type="button" class="step-dec" data-target="children"
                                                            aria-label="Decrease children">−</button>
                                                        <input type="number" name="children" id="children"
                                                            value="{{ old('children', 0) }}" min="0" max="{{ ($selectedRoomType->capacity ?? 2) - 1 }}"
                                                            readonly>
                                                        <button type="button" class="step-inc" data-target="children"
                                                            aria-label="Increase children">+</button>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="max-capacity-badge" id="maxCapacityBadge" style="display: none;">
                                                <i class="fas fa-users me-1"></i>Max <span id="maxCapacityValue">-</span> guests
                                            </div>
                                        </div>
                                        <div class="occupancy-bar-wrap">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <span class="occupancy-label" id="occupancyLabel">
                                                    <i class="fas fa-bed me-1"></i>Select a room
                                                </span>
                                                <span class="occupancy-count" id="occupancyCount"></span>
                                            </div>
                                            <div class="occupancy-bar">
                                                <div class="occupancy-bar-fill" id="occupancyBarFill" style="width: 0%"></div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <small class="text-muted" id="capacityHint">
                                                    <i class="fas fa-info-circle me-1"></i>Pick a room above to see occupancy &amp; fees
                                                </small>
                                                <small class="occupancy-fee-hint d-none" id="guestFeeHint"></small>
                                            </div>
                                        </div>
                                    </div>
                                    <div id="capacityError" class="text-danger small mt-2 d-none"></div>
                                </div>
                                <div class="col-12">
                                    <div class="special-requests-box">
                                        <label class="form-label mb-2"><i class="far fa-comment-dots me-2"
                                                style="color: var(--brand-gold);"></i>Special Requests</label>
                                        <textarea name="special_requests" class="form-control" rows="2"
                                            placeholder="e.g. Late check-in, extra pillows, anniversary celebration...">{{ old('special_requests') }}</textarea>
                                    </div>
                                </div>
                            </div>

                            @if (!Auth::check())
                                <div class="mt-4">
                                    <div class="create-account-box">
                                        <div class="form-check form-switch d-flex align-items-center gap-2 mb-0">
                                            <input class="form-check-input" type="checkbox" id="createAccountToggle"
                                                name="create_account" value="1"
                                                {{ old('create_account') ? 'checked' : '' }}
                                                style="width: 2.5rem; height: 1.25rem; cursor: pointer;">
                                            <label class="form-check-label fw-bold" for="createAccountToggle"
                                                style="cursor: pointer;">
                                                <i class="fas fa-user-plus me-2"
                                                    style="color: var(--brand-gold);"></i>Create an account for faster
                                                booking next time
                                            </label>
                                        </div>
                                        <div class="collapse mt-3 {{ old('create_account') ? 'show' : '' }}"
                                            id="accountFields">
                                            <div class="p-3 bg-white rounded border">
                                                <label class="form-label">Choose Password</label>
                                                <input type="password" name="password" class="form-control"
                                                    placeholder="Min. 8 characters" style="max-width: 350px;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="form-section reveal">
                        <div class="form-section-header">
                            <div class="section-icon"><i class="fas fa-credit-card"></i></div>
                            <h5>Payment Method</h5>
                            <span class="step-badge">Step 5</span>
                        </div>
                        <div class="form-section-body">
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="payment-option">
                                        <input type="radio" name="payment_method" value="paystack" checked>
                                        <div class="payment-content d-flex align-items-start gap-3">
                                            <div class="payment-icon pay-now"><i class="fas fa-credit-card"></i></div>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold mb-1">Pay Now</div>
                                                <div class="small text-muted">Instant confirmation via card or transfer
                                                </div>
                                            </div>
                                            <div class="check-indicator"><i class="fas fa-check"></i></div>
                                        </div>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label class="payment-option">
                                        <input type="radio" name="payment_method" value="pay_on_arrival">
                                        <div class="payment-content d-flex align-items-start gap-3">
                                            <div class="payment-icon pay-later"><i class="fas fa-hotel"></i></div>
                                            <div class="flex-grow-1">
                                                <div class="fw-bold mb-1">Pay at Hotel</div>
                                                <div class="small text-muted">Settle payment at the front desk upon arrival
                                                </div>
                                            </div>
                                            <div class="check-indicator"><i class="fas fa-check"></i></div>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            <p class="payment-hint text-center mt-2 mb-0">
                                <i class="fas fa-lock me-1"></i> We never store your card details — payments are securely
                                processed by Paystack.
                            </p>
                        </div>
                    </div>

                    <div class="review-strip" id="reviewStrip" @if($useCartFlow) style="display:none;" @endif>
                        <div class="rv-item">
                            <i class="fas fa-calendar-alt"></i>
                            <span class="rv-value" id="reviewRoom">Select a room</span>
                        </div>
                        <div class="rv-divider"></div>
                        <div class="rv-item">
                            <i class="fas fa-calendar-day"></i>
                            <span><span class="rv-value" id="reviewDates">—</span></span>
                        </div>
                        <div class="rv-divider"></div>
                        <div class="rv-item">
                            <i class="fas fa-moon"></i>
                            <span><span class="rv-value" id="reviewNights">1</span> night</span>
                        </div>
                        <div class="rv-divider"></div>
                        <div class="rv-item">
                            <i class="fas fa-users"></i>
                            <span><span class="rv-value" id="reviewGuests">1 Adult</span></span>
                        </div>
                        <div class="rv-divider"></div>
                        <div class="rv-item">
                            <i class="fas fa-naira-sign"></i>
                            <span class="rv-value" id="reviewTotal">₦0.00</span>
                        </div>
                    </div>

                    <div class="booking-cta">
                        @if ($useCartFlow)
                            <div class="total-row">
                                <span class="total-label">Total Amount</span>
                                <span class="total-amount">{{ $cart['formatted_total'] }}</span>
                            </div>
                        @endif
                        <button type="submit" id="submitBtn" class="btn btn-brand btn-lg w-100">
                            <span id="btnText"><i class="fas fa-lock me-2 lock-icon"></i>Complete Booking</span>
                            <span id="btnSpinner" class="spinner-border spinner-border-sm ms-2 d-none"
                                role="status"></span>
                        </button>
                        <p class="secure-badge">
                            <i class="fas fa-shield-alt"></i>
                            Secured with SSL encryption
                        </p>
                    </div>
                </form>
            </div>

            <div class="col-lg-4">
                <div class="card-summary shadow-sm">
                    <div class="card-header">
                        <h5><i class="fas fa-receipt me-2"></i>Booking Summary</h5>
                    </div>

                    @if ($useCartFlow)
                        <div class="card-body">
                            <div class="d-flex justify-content-between mb-3 small">
                                <span class="text-muted">Check-in</span>
                                <span
                                    class="fw-bold">{{ \Carbon\Carbon::parse($cart['check_in'])->format('M d, Y') }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-3 small">
                                <span class="text-muted">Check-out</span>
                                <span
                                    class="fw-bold">{{ \Carbon\Carbon::parse($cart['check_out'])->format('M d, Y') }}</span>
                            </div>

                            <hr class="my-3">

                            <div class="fw-bold small mb-3" style="color: var(--brand-gold);">SELECTED ROOMS</div>
                            @foreach ($cart['items'] as $item)
                                <div class="summary-room-item">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="room-icon"><i class="fas fa-bed"></i></div>
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="fw-bold small">{{ $item['room_type_name'] }}</div>
                                            <div class="text-muted small">{{ $item['quantity'] }} room &times;
                                                {{ $item['nights'] }} nights</div>
                                        </div>
                                        <div class="fw-bold" style="color: #16a34a; font-size: 0.9rem;">
                                            ₦{{ number_format($item['subtotal'], 2) }}</div>
                                    </div>
                                </div>
                            @endforeach

                            <div class="summary-total-row mt-3">
                                <div class="d-flex justify-content-between align-items-center">
                                    <div>
                                        <div class="fw-bold small">Total</div>
                                        <div class="text-muted" style="font-size: 0.75rem;">{{ $cart['total_rooms'] }}
                                            room(s), {{ $cart['nights'] }} nights</div>
                                    </div>
                                    <span class="amount">{{ $cart['formatted_total'] }}</span>
                                </div>
                            </div>

                            <p class="text-muted small mt-3 mb-0">
                                <i class="fas fa-info-circle me-1"></i> Rooms assigned at check-in
                            </p>
                        </div>
                    @else
                        <img id="summary-image"
                            src="{{ $selectedRoomType->image_url ?? asset('images/default-room.jpg') }}"
                            class="card-img-top {{ $selectedRoomType ? '' : 'd-none' }}"
                            style="height: 180px; object-fit: cover;">

                        <div class="card-body">
                            <div class="text-center mb-3">
                                <h5 id="summary-name" class="fw-bold mb-1" style="color: var(--brand-dark);">
                                    {{ $selectedRoomType->name ?? 'Select a Room Type' }}
                                </h5>
                                <div class="small text-muted">
                                    <i class="fas fa-user-friends me-1"></i> Max <span
                                        id="summary-capacity">{{ $selectedRoomType->capacity ?? '-' }}</span> Guests
                                </div>
                            </div>

                            <hr class="my-3">

                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">Check-in</span>
                                <span class="fw-bold"
                                    id="summary-checkin">{{ $reqCheckIn ? \Carbon\Carbon::parse($reqCheckIn)->format('M d, Y') : '...' }}</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">Check-out</span>
                                <span class="fw-bold"
                                    id="summary-checkout">{{ $reqCheckOut ? \Carbon\Carbon::parse($reqCheckOut)->format('M d, Y') : '...' }}</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">Nights</span>
                                <span class="fw-bold" id="summary-nights">1</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">Rate</span>
                                <span id="summary-rate">₦{{ number_format($selectedRoomType->price ?? 0, 2) }}</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-2">
                                <span class="text-muted">Guests</span>
                                <span class="fw-bold" id="summary-guests">1 Adult</span>
                            </div>
                            <div class="d-flex justify-content-between small mb-2 d-none" id="guest-fee-row">
                                <span class="text-muted">Guest Fee</span>
                                <span id="summary-guest-fee">₦0.00</span>
                            </div>

                            <hr class="my-3">

                            <div class="summary-total-row">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="fw-bold small">Total</span>
                                    <span class="amount" id="summary-total">₦0.00</span>
                                </div>
                            </div>

                            <p class="text-muted small mt-3 mb-0">
                                <i class="fas fa-info-circle me-1"></i> Specific room assigned at check-in
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
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

            const availabilityAlert = document.getElementById('availabilityAlert');
            const submitBtn = document.getElementById('submitBtn');
            const btnText = document.getElementById('btnText');
            const btnSpinner = document.getElementById('btnSpinner');
            const bookingForm = document.getElementById('bookingForm');

            const formatMoney = (amount) => '₦' + parseFloat(amount).toFixed(2).replace(/\d(?=(\d{3})+\.)/g, '$&,');
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
                if (from === to) {
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

            if (emailInput) {
                emailInput.addEventListener('blur', function() {
                    const email = this.value;
                    if (email && email.includes('@')) {
                        fetch('/website/check-email', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value
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
                }
                return 1;
            }

            function updateSummary() {
                if (!roomSelect) return;
                const selectedOption = roomSelect.options[roomSelect.selectedIndex];

                if (summaryCheckIn && checkInInput?.value) summaryCheckIn.textContent = formatDate(checkInInput.value);
                if (summaryCheckOut && checkOutInput?.value) summaryCheckOut.textContent = formatDate(checkOutInput.value);

                const nights = calculateNights();
                if (summaryNights) summaryNights.textContent = nights;

                if (selectedOption.value) {
                    const price = parseFloat(selectedOption.dataset.price);
                    const capacity = parseInt(selectedOption.dataset.capacity || 2);
                    const baseOccupancy = parseInt(selectedOption.dataset.baseOccupancy || 2);
                    const extraAdultFee = parseFloat(selectedOption.dataset.extraAdultFee || 0);
                    const extraChildFee = parseFloat(selectedOption.dataset.extraChildFee || 0);

                    if (summaryName) summaryName.textContent = selectedOption.dataset.name;
                    if (summaryCapacity) summaryCapacity.textContent = capacity;
                    if (summaryRate) summaryRate.textContent = formatMoney(price);

                    // Update stepper max based on capacity
                    const adultsInput = document.getElementById('adults');
                    const childrenInput = document.getElementById('children');
                    const maxCapacityBadge = document.getElementById('maxCapacityBadge');
                    const maxCapacityValue = document.getElementById('maxCapacityValue');
                    
                    // Show max capacity badge
                    if (maxCapacityBadge && maxCapacityValue) {
                        maxCapacityBadge.style.display = 'inline-block';
                        maxCapacityValue.textContent = capacity;
                    }
                    
                    if (adultsInput) {
                        adultsInput.max = capacity;
                        if (parseInt(adultsInput.value) > capacity) adultsInput.value = capacity;
                    }
                    if (childrenInput) {
                        const maxChildren = Math.max(0, capacity - parseInt(adultsInput?.value || 1));
                        childrenInput.max = maxChildren;
                        if (parseInt(childrenInput.value) > maxChildren) childrenInput.value = maxChildren;
                    }
                    syncSteppers();

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

                    // Guest fee hint
                    const extraAdults = Math.max(0, adults - baseOccupancy);
                    const extraChildren = children;
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

                    // Update guest fee row
                    const guestFeeRow = document.getElementById('guest-fee-row');
                    const guestFeeEl = document.getElementById('summary-guest-fee');
                    if (guestFeeRow && guestFeeEl) {
                        if (guestFeeTotal > 0) {
                            guestFeeRow.classList.remove('d-none');
                            guestFeeEl.textContent = formatMoney(guestFeeTotal);
                        } else {
                            guestFeeRow.classList.add('d-none');
                        }
                    }

                    // For cart flow: price/total will be set by AJAX response (server-side pricing)
                    // For non-cart flow: compute client-side
                    const isCartFlow = bookingForm && bookingForm.hasAttribute('data-cart-flow');
                    if (!isCartFlow) {
                        if (summaryTotal) setMoneyAnimated(summaryTotal, total);
                    }

                    if (summaryImage && selectedOption.dataset.image) {
                        summaryImage.src = selectedOption.dataset.image;
                        summaryImage.classList.remove('d-none');
                    }

                    checkAvailability();
                    loadAvailableUnits();
                } else {
                    // No room selected — clear occupancy bar and hide capacity badge
                    const occBar = document.getElementById('occupancyBarFill');
                    const occLabel = document.getElementById('occupancyLabel');
                    const occCount = document.getElementById('occupancyCount');
                    const maxCapacityBadge = document.getElementById('maxCapacityBadge');
                    if (occBar) { occBar.style.width = '0%'; occBar.classList.remove('occupancy-warning', 'occupancy-full'); }
                    if (occLabel) occLabel.innerHTML = '<i class="fas fa-bed me-1"></i>Select a room';
                    if (occCount) { occCount.textContent = ''; occCount.style.color = ''; }
                    if (maxCapacityBadge) maxCapacityBadge.style.display = 'none';
                    const feeHint = document.getElementById('guestFeeHint');
                    if (feeHint) feeHint.classList.add('d-none');
                    const capacityHint = document.getElementById('capacityHint');
                    if (capacityHint) capacityHint.innerHTML = '<i class="fas fa-info-circle me-1"></i>Choose a room to see occupancy';
                }
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

                fetch(`/website/api/available-units?${queryParams.toString()}`, {
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

                if (roomTypeId && checkIn && checkOut) {
                    btnText.textContent = 'Checking Availability...';
                    submitBtn.disabled = true;

                    const queryParams = new URLSearchParams({
                        room_type_id: roomTypeId,
                        check_in_date: checkIn,
                        check_out_date: checkOut
                    });

                    fetch(`{{ route('website.room.checkAvailability') }}?${queryParams.toString()}`, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'application/json'
                            }
                        })
                        .then(r => r.json())
                        .then(data => {
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
                                btnText.textContent = 'Confirm & Pay Reservation';
                            }
                        })
                        .catch(err => {
                            console.error('Check failed:', err);
                            submitBtn.disabled = false;
                            btnText.textContent = 'Confirm & Pay Reservation';
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
                    // Validate room selection in non-cart flow (cards are present)
                    const cards = document.querySelectorAll('.room-card');
                    const selectedCard = document.querySelector('.room-card.selected');
                    if (cards.length > 0 && !selectedCard) {
                        e.preventDefault();
                        const alertEl = document.getElementById('availabilityAlert');
                        if (alertEl) {
                            alertEl.classList.remove('d-none', 'alert-success');
                            alertEl.classList.add('alert-danger');
                            alertEl.innerHTML = '<i class="fas fa-exclamation-circle me-2"></i>Please select a room type to continue.';
                            alertEl.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        }
                        return;
                    }
                    if (!submitBtn.disabled) {
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
                });
            }

            if (roomSelect && roomSelect.value) updateSummary();

            // Payment option styling via click
            document.querySelectorAll('.payment-option').forEach(opt => {
                opt.addEventListener('click', function() {
                    this.querySelector('input[type="radio"]').checked = true;
                });
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
                        const base = new Date();
                        base.setDate(base.getDate() + offset);
                        const ci = new Date(base);
                        const co = new Date(base);
                        co.setDate(co.getDate() + 1);
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

            // Guest steppers
            function syncSteppers() {
                document.querySelectorAll('.guest-stepper').forEach(stepper => {
                    const input = stepper.querySelector('input[type="number"]');
                    const min = parseInt(input.min, 10) || 0;
                    const max = parseInt(input.max, 10) || 20;
                    const v = parseInt(input.value, 10) || min;
                    stepper.querySelector('.step-dec').disabled = v <= min;
                    stepper.querySelector('.step-inc').disabled = v >= max;
                });
            }

            // Debounced AJAX call to persist guest counts to cart session
            let _guestSyncTimer = null;
            function persistGuestCountsToCart() {
                if (!bookingForm || !bookingForm.hasAttribute('data-cart-flow')) return;
                const roomTypeId = document.getElementById('room_type_id')?.value;
                if (!roomTypeId) return;
                const adults = parseInt(document.getElementById('adults')?.value || 1);
                const children = parseInt(document.getElementById('children')?.value || 0);
                clearTimeout(_guestSyncTimer);
                _guestSyncTimer = setTimeout(() => {
                    fetch('{{ route("website.cart.update-guests") }}', {
                        method: 'PUT',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({ room_type_id: roomTypeId, adults, children }),
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success && data.item) {
                            const item = data.item;
                            // Fix: Use correct element IDs with hyphens
                            const rateEl = document.getElementById('summary-rate');
                            const totalEl = document.getElementById('summary-total');
                            const guestFeeRow = document.getElementById('guest-fee-row');
                            const guestFeeEl = document.getElementById('summary-guest-fee');
                            const guestsSummaryEl = document.getElementById('summary-guests');

                            if (rateEl && item.price_per_night != null) rateEl.textContent = formatMoney(item.price_per_night);
                            if (totalEl && item.total_rate != null) setMoneyAnimated(totalEl, item.total_rate);

                            if (guestFeeRow && guestFeeEl) {
                                if (item.guest_fee_total > 0) {
                                    guestFeeRow.classList.remove('d-none');
                                    guestFeeEl.textContent = formatMoney(item.guest_fee_total);
                                } else {
                                    guestFeeRow.classList.add('d-none');
                                }
                            }
                            
                            // Update guest summary display
                            if (guestsSummaryEl) {
                                const parts = [];
                                if (item.adults > 0) parts.push(item.adults + (item.adults === 1 ? ' Adult' : ' Adults'));
                                if (item.children > 0) parts.push(item.children + (item.children === 1 ? ' Child' : ' Children'));
                                guestsSummaryEl.textContent = parts.join(', ') || '1 Adult';
                            }
                        }
                    })
                    .catch(() => {});
                }, 200);
            }

            document.querySelectorAll('.guest-stepper').forEach(stepper => {
                const input = stepper.querySelector('input[type="number"]');
                const getMin = () => parseInt(input.min, 10) || 0;
                const getMax = () => parseInt(input.max, 10) || 20;
                const sync = () => {
                    const min = getMin();
                    const max = getMax();
                    const v = parseInt(input.value, 10) || min;
                    stepper.querySelector('.step-dec').disabled = v <= min;
                    stepper.querySelector('.step-inc').disabled = v >= max;
                    updateProgressBar();
                    updateSummary();
                    updateGuestSummary();
                    updateReviewStrip();
                    // Update children max based on remaining capacity after adults
                    const adultsInput = document.getElementById('adults');
                    const childrenInput = document.getElementById('children');
                    if (adultsInput && childrenInput) {
                        const capacity = parseInt(roomSelect?.options[roomSelect.selectedIndex]?.dataset?.capacity || 20);
                        childrenInput.max = Math.max(0, capacity - parseInt(adultsInput.value || 1));
                        if (parseInt(childrenInput.value) > parseInt(childrenInput.max)) childrenInput.value = childrenInput.max;
                        // Validate capacity
                        const totalGuests = parseInt(adultsInput.value || 1) + parseInt(childrenInput.value || 0);
                        const capacityError = document.getElementById('capacityError');
                        if (totalGuests > capacity) {
                            if (capacityError) {
                                capacityError.textContent = `Total guests (${totalGuests}) exceed room capacity (${capacity})`;
                                capacityError.classList.remove('d-none');
                            }
                            document.getElementById('submitBtn').disabled = true;
                        } else {
                            if (capacityError) capacityError.classList.add('d-none');
                            document.getElementById('submitBtn').disabled = false;
                        }
                    }
                    syncSteppers();
                    // Persist guest counts to cart session (server-side)
                    persistGuestCountsToCart();
                };
                stepper.querySelector('.step-dec').addEventListener('click', () => {
                    const min = getMin();
                    input.value = Math.max(min, (parseInt(input.value, 10) || min) - 1);
                    sync();
                });
                stepper.querySelector('.step-inc').addEventListener('click', () => {
                    const max = getMax();
                    const min = getMin();
                    input.value = Math.min(max, (parseInt(input.value, 10) || min) + 1);
                    sync();
                });
                sync();
            });

            // Live progress as the guest types
            function refreshUI() {
                updateProgressBar();
                updateStepIndicator();
                updateReviewStrip();
                updateSummary();
            }
            if (bookingForm) {
                bookingForm.querySelectorAll('input, select, textarea').forEach(el => {
                    el.addEventListener('input', refreshUI);
                    el.addEventListener('change', refreshUI);
                });
            }
            refreshUI();

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
            if (bookingForm) {
                bookingForm.addEventListener('submit', function() {
                    if (!submitBtn.disabled) burstConfetti();
                });
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
                if (!btnTextEl) return;
                if (selected && selected.value === 'pay_on_arrival') {
                    btnTextEl.innerHTML = '<i class="fas fa-hotel me-2"></i>Reserve — Pay at Hotel';
                } else {
                    btnTextEl.innerHTML = '<i class="fas fa-lock me-2 lock-icon"></i>Complete Booking';
                }
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
            });

            // ── Step indicator update ──
            function updateStepIndicator() {
                const steps = document.querySelectorAll('.step-item');
                const connectors = document.querySelectorAll('.step-connector');
                const isCartFlow = bookingForm && bookingForm.hasAttribute('data-cart-flow');
                const sections = [
                    { name: 'dates', fields: ['check_in_date', 'check_out_date', 'room_type_id'] },
                    { name: 'guest', fields: ['guest_name', 'guest_email', 'guest_phone', 'guest_gender', 'guest_address', 'guest_nationality'] },
                    { name: 'id', fields: ['guest_id_type', 'guest_id_number'] },
                    { name: 'extras', fields: [] },
                    { name: 'payment', fields: [] },
                ];
                let activeIdx = 0;
                for (let i = 0; i < sections.length; i++) {
                    if (i === 0 && isCartFlow) { activeIdx = 1; continue; }
                    const allFilled = sections[i].fields.every(name => {
                        const el = bookingForm.querySelector('[name="' + name + '"]');
                        return el && el.value && el.value.trim() !== '';
                    });
                    if (!allFilled) { activeIdx = i; break; }
                    activeIdx = i + 1;
                }
                if (activeIdx >= steps.length) activeIdx = steps.length - 1;
                steps.forEach((s, i) => {
                    s.classList.toggle('active', i === activeIdx);
                    s.classList.toggle('completed', i < activeIdx);
                });
                connectors.forEach((c, i) => {
                    c.classList.toggle('completed', i < activeIdx);
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
                        const baseOccupancy = parseInt(opt.dataset.baseOccupancy || 2);
                        const extraAdultFee = parseFloat(opt.dataset.extraAdultFee || 0);
                        const extraChildFee = parseFloat(opt.dataset.extraChildFee || 0);
                        const adults = parseInt(document.getElementById('adults')?.value || 1);
                        const children = parseInt(document.getElementById('children')?.value || 0);
                        const extraAdults = Math.max(0, adults - baseOccupancy);
                        const extraChildren = children;
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
        });
    </script>
@endpush
