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

### Creating New Modules

```powershell
php artisan module:make ModuleName
php artisan module:make-controller ControllerName ModuleName
php artisan module:make-model ModelName ModuleName
php artisan module:make-migration create_tablename_table ModuleName
```
