# AGENTS.md

This file provides guidance to WARP (warp.dev) when working with code in this repository.

## Project Overview

Brickspoint HMS ("Brick") is a modular hospitality management ERP built on Laravel 11 using the `nwidart/laravel-modules` package. Each business domain (Frontdesk, Restaurant, Gym, etc.) is encapsulated as an independent module.

## Development Commands

```powershell
# Install dependencies
composer install
npm install

# Start full development environment (server + queue + logs + vite)
composer dev

# Individual services
php artisan serve          # Laravel server
npm run dev                # Vite dev server
php artisan queue:listen   # Queue worker
php artisan pail           # Log viewer

# Build assets for production
npm run build

# Database
php artisan migrate        # Core migrations
php artisan module:migrate # Module migrations (run after core)
php artisan db:seed        # Seed roles, admin, settings

# Linting
php artisan pint           # Laravel Pint code style fixer

# Hikvision Attendance
php artisan attendance:import-hikvision                        # Manual import
php artisan attendance:import-hikvision --dry-run              # Preview before import
php artisan attendance:import-hikvision --from="2026-07-01 00:00:00" --to="2026-07-04 23:59:59"  # Date range

# Testing
php artisan test                           # All tests
php artisan test --filter=TestName         # Single test
php artisan test Modules/ModuleName/tests  # Module tests
```

## Architecture

### Modular Monolith Structure

Business logic lives in `Modules/`, not in the standard Laravel `app/` directory. Each module is a self-contained Laravel application:

```
Modules/{ModuleName}/
├── app/
│   ├── Http/Controllers/
│   ├── Models/
│   ├── Providers/
│   │   ├── {Module}ServiceProvider.php    # Main provider, registers routes/views/config
│   │   ├── RouteServiceProvider.php
│   │   └── EventServiceProvider.php
│   └── Rules/
├── config/
├── database/
│   ├── migrations/
│   ├── seeders/
│   └── factories/
├── resources/views/
├── routes/
│   ├── web.php
│   └── api.php
└── module.json              # Module metadata and provider registration
```

### Core App (`app/`)

Contains only shared/cross-cutting concerns:
- `App\Models\User` - User model used across all modules
- `App\Enums\RoleEnum` - RBAC roles: `SUPER_ADMIN`, `ADMIN`, `STAFF`, `GUEST`
- `App\Notifications` - Shared notification classes
- `App\Http` - Global middleware

### Active Modules

Admin, Banquet, Frontdeskcrm, Gym, Inventory, Maintenance, Restaurant, Staff, Tasks, Website

Module status controlled via `modules_statuses.json`.

### Key Conventions

**Namespacing**: `Modules\{ModuleName}\{Path}` (e.g., `Modules\Frontdeskcrm\Http\Controllers\RegistrationController`)

**Views**: Reference with module prefix - `{modulename}::viewname` (e.g., `frontdeskcrm::registrations.index`)

**Routes**: Each module defines routes in `Modules/{ModuleName}/routes/web.php`. Check middleware like `can:access_frontdesk_dashboard` for permission requirements.

**RBAC**: Uses `spatie/laravel-permission`. Reference `App\Enums\RoleEnum` for role constants. Permissions are capability-based (e.g., `access_frontdesk_dashboard`).

**DataTables**: Server-side rendering with `yajra/laravel-datatables`.

**PDFs**: Generated via `barryvdh/laravel-dompdf` (invoices, function sheets, registration forms).

<<<<<<< HEAD
## Multi-Property Architecture

Multi-property support uses:
- **`App\Models\Property`** — Property model with `getDefault()`/`current()` helpers, `scopeActive()`, `is_headquarters` flag, `users()` BelongsToMany via `property_user` pivot (with `is_default`)
- **`App\Models\Traits\HasProperty`** — Trait applied to scoped models: auto-adds `PropertyScope` global filter + auto-fills `property_id` on `creating`
- **`App\Models\Scopes\PropertyScope`** — Global scope that applies `WHERE property_id = ?` to all queries on scoped models
- **`App\Services\PropertyService`** — Service with `current()`/`id()`/`scope()`/`setCurrent()`/`clear()` helpers
- **`App\Http\Middleware\SetPropertyContext`** — Middleware registered in `web` group, sets property from query param or session
- **`Modules\Frontdeskcrm\Http\Controllers\PropertyController`** — Full CRUD + `switch()` + user management
- **Property switcher dropdown** in navbar (`resources/views/layouts/navbar.blade.php`)
- **Menu entry** at `Modules/Frontdeskcrm/resources/views/layouts/menu.blade.php` under "Configuration"
- **Routes** at `Modules/Frontdeskcrm/routes/web.php` under `frontdesk.properties.*` prefix

### Scoped Models (have `property_id` + `HasProperty` trait)
`App\Models\RoomType`, `App\Models\RoomUnit`, `Modules\Frontdeskcrm\Models\Registration`, `RateCode`, `ChargeType`, `BookingSource`, `GuestType`, `Channel`, `NightAudit`, `Modules\Website\Models\Booking`

### NOT scoped
`App\Models\Room` (legacy), `Property` itself, `User`, `Guest`, `FolioCharge`, `NightAuditLog`, `RateCalendar`, `RateCodePrice`

## Pre-Arrival / Digital Guest Journey

The pre-arrival check-in flow is split across two modules:
- **Guest-facing**: `Modules/Website/Http/Controllers/PreArrivalController` (11 routes) — `website::guest.pre-arrival.*` views
- **Admin dashboard**: `Modules/Frontdeskcrm/Http/Controllers/PreArrivalDashboardController` (7 routes) — `frontdeskcrm::pre-arrival.*` views

Key models (all in `Modules/Frontdeskcrm/Models`):
- `Registration` — pre-arrival fields: `pre_arrival_token`, `special_requests`, `estimated_arrival_at`, `pre_arrival_completed_at`, `opt_in_marketing`
- `GuestDocument` — uploaded IDs/docs linked to a `Registration` via `Guest`. Has `verified_at`, `rejected_at`, `type` enum
- `MessageTemplate` — templates with `{guest_name}`, `{reservation_code}` placeholders
- `GuestMessage` — delivery log with `channel` (email/sms/whatsapp) and `status`

Services:
- `Modules/Frontdeskcrm/Services/PreArrivalService` — token gen, guest detail update, document upload/delete, signature, completion
- `Modules/Frontdeskcrm/Services/GuestMessagingService` — dispatches via `Mail::raw()`, `BulkSmsNigeria`, and `WhatsAppService`
- `app/Services/WhatsAppService` — config-based stub (`config('services.whatsapp')`), real Graph API call commented in

Console commands (registered, with schedule in `bootstrap/app.php`):
- `hotel:send-pre-arrival-reminders` — daily 09:00, sends for `reserved` registrations within 3 days of checkout
- `hotel:send-review-requests` — daily 10:00, sends for checkout dates >= 2 days ago (not already sent)
- `hotel:re-engagement-campaign` — weekly Monday 11:00, sends for bookings with checkout > 30 days ago

Observer (`RegistrationObserver`): auto-generates token + sends reminder when `stay_status` → `reserved`.

**Warning — observer re-entrancy**: The `updated` event fires before `syncOriginal()`. Nested `update()` in an observer sees all prior attributes as dirty. To prevent infinite recursion, ensure gating fields (e.g. `!$registration->pre_arrival_token`) are in `$fillable` so the nested `update()` changes their value and breaks the cycle.

## Production Operations

### Required Environment Variables

```bash
# Multi-property subdomain routing
PROPERTY_DOMAIN_FORMAT={property}.brickspoint.com

# Guest messaging
BULKSMSNIGERIA_API_TOKEN=
WHATSAPP_API_TOKEN=
WHATSAPP_PHONE_NUMBER_ID=
MAIL_MAILER=smtp
MAIL_HOST=
MAIL_PORT=587
MAIL_USERNAME=
MAIL_PASSWORD=
MAIL_FROM_ADDRESS=reservations@brickspoint.com
MAIL_FROM_NAME="Brickspoint Hotels"
```

### First-Time Deploy

```powershell
# Before first deploy
php artisan key:generate
php artisan migrate
php artisan module:migrate --all
php artisan db:seed
php artisan module:seed --all

# Verify route cache build (no closures)
php artisan route:list

# Warm cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

### Queue Setup (Required for Messaging)

All guest messaging dispatches via `Mail::raw()` which uses the queue. Configure the queue worker:

```powershell
# In production, run as a service/supervisor process
php artisan queue:work --tries=3 --delay=5
```

### Verifying Pre-Arrival Module

```powershell
# Check tables exist
php artisan module:migration:check --name=Frontdeskcrm

# Verify 3 scheduled commands are registered
php artisan schedule:list

# Quick test: trigger a pre-arrival reminder for a specific registration
php artisan hotel:send-pre-arrival-reminders --registration-id=1

# Check guest message log
php artisan tinker
>>> \Modules\Frontdeskcrm\Models\GuestMessage::all();
```
=======
## Hikvision DS-K1A802AMF-B Attendance Middleware (Windows PC)

This device is a **face recognition access control terminal** — it does NOT support
ISAPI event search (`AcsEvent/Search` returns `invalidID`). Event data must be
captured via the Hikvision HCNetSDK on a Windows PC on the same LAN.

Location: `scripts/HikvisionMiddleware.ps1` + `scripts/HikvisionSDKHelper.cs`
Config:  `scripts/HikvisionConfig.json` (auto-generated on first run)

### Quick Start (ISAPI Poll — limited)

```powershell
cd scripts
.\HikvisionMiddleware.ps1 -Mode once
```

### Full Setup (HCNetSDK — recommended)

1. Download HCNetSDK from Hikvision, extract `HCNetSDK.dll` + `HCCore.dll` into `scripts/`
2. Compile the C# helper:
   ```powershell
   & "$env:windir\Microsoft.NET\Framework\v4.0.30319\csc.exe" `
       -target:exe -platform:x86 -out:HikvisionSDKHelper.exe HikvisionSDKHelper.cs
   ```
3. Run listener:
   ```powershell
   .\HikvisionMiddleware.ps1 -Mode listen
   ```
>>>>>>> 028f43090958fcfb8dab5fc3dcf10f38e8c68629

### Creating New Modules

```powershell
php artisan module:make ModuleName
php artisan module:make-controller ControllerName ModuleName
php artisan module:make-model ModelName ModuleName
php artisan module:make-migration create_tablename_table ModuleName
```
