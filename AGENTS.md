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

### Creating New Modules

```powershell
php artisan module:make ModuleName
php artisan module:make-controller ControllerName ModuleName
php artisan module:make-model ModelName ModuleName
php artisan module:make-migration create_tablename_table ModuleName
```
