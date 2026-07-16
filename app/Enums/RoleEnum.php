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
    case LINE_MANAGER = 'line_manager';
    case PURCHASER = 'purchaser';
    case GM = 'gm';
    case FINANCE = 'finance';
    case AUDITOR = 'auditor';
    case GGM = 'ggm';
}
