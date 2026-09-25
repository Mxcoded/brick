<?php

namespace Modules\Contracts\Enums;

enum AgreementStatus: string
{
    case DRAFT = 'draft';
    case INTERNAL_REVIEW = 'internal_review';
    case PENDING_APPROVAL = 'pending_approval';
    case APPROVED = 'approved';
    case SENT = 'sent';
    case CLIENT_REVIEW = 'client_review';
    case CLIENT_SIGNED = 'client_signed';
    case HOTEL_SIGNED = 'hotel_signed';
    case EXECUTED = 'executed';
    case ACTIVE = 'active';
    case AMENDED = 'amended';
    case EXPIRED = 'expired';
    case CANCELLED = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::DRAFT => 'Draft',
            self::INTERNAL_REVIEW => 'Internal Review',
            self::PENDING_APPROVAL => 'Awaiting Approval',
            self::APPROVED => 'Approved',
            self::SENT => 'Sent to Client',
            self::CLIENT_REVIEW => 'Client Review',
            self::CLIENT_SIGNED => 'Client Signed',
            self::HOTEL_SIGNED => 'Hotel Signed',
            self::EXECUTED => 'Executed',
            self::ACTIVE => 'Active',
            self::AMENDED => 'Amended',
            self::EXPIRED => 'Expired',
            self::CANCELLED => 'Cancelled',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::DRAFT => 'bg-secondary',
            self::INTERNAL_REVIEW => 'bg-info',
            self::PENDING_APPROVAL => 'bg-warning',
            self::APPROVED, self::SENT => 'bg-primary',
            self::CLIENT_REVIEW => 'bg-info',
            self::CLIENT_SIGNED, self::HOTEL_SIGNED => 'bg-primary',
            self::EXECUTED, self::ACTIVE => 'bg-success',
            self::AMENDED => 'bg-warning',
            self::EXPIRED => 'bg-dark',
            self::CANCELLED => 'bg-danger',
        };
    }
}
