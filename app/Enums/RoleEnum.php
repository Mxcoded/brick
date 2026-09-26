<?php

namespace App\Enums;

enum RoleEnum: string
{
    case ADMIN = 'admin';
    case HR_MANAGER = 'hr_manager';
    case GUEST = 'guest';
    case RECEPTIONIST = 'receptionist';
    case RESTAURANT_MANAGER = 'restaurant_manager';
    case WAITER = 'waiter';
    case GYM_SUPERVISOR = 'gym_supervisor';
    case STORE_KEEPER = 'store_keeper';
    case MAINTENANCE_ENGINEER = 'maintenance_engineer';
    case EVENT_MANAGER = 'event_manager';
    case WEBSITE_ADMIN = 'website_admin';
    case CONTRACTS_MANAGER = 'contracts_manager';
    case LINE_MANAGER = 'line_manager';
    case PURCHASER = 'purchaser';
    case GM = 'gm';
    case FINANCE = 'finance';
    case AUDITOR = 'auditor';
    case GGM = 'ggm';

    public static function procurementRoles(): array
    {
        return [
            self::LINE_MANAGER->value,
            self::PURCHASER->value,
            self::GM->value,
            self::FINANCE->value,
            self::AUDITOR->value,
            self::GGM->value,
        ];
    }

    public static function procurementApproverRoles(): array
    {
        return [
            self::PURCHASER->value,
            self::GM->value,
            self::FINANCE->value,
            self::AUDITOR->value,
            self::GGM->value,
        ];
    }
}
