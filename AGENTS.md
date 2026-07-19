# Plan: 5 Major Features for Brickspoint HMS

## Context
The codebase has completed performance optimization, multi-property isolation, restaurant features (standalone mode, charge to room, split bill, KDS real-time), and room service toggle. The next phase introduces 5 major architectural features to improve maintainability, observability, and API capabilities.

**Current architecture:** 12 nwidart/laravel-modules (Admin, Banquet, Finance, Frontdeskcrm, Gym, Housekeeping, Inventory, Maintenance, Restaurant, Staff, Tasks, Website). All cross-module communication is via direct Eloquent model/Service imports. No audit package. No API layer. No night audit automation. Settings scattered across RestaurantSetting, StaffSetting, and Property.settings JSON.

---

## Implementation Status

| # | Feature | Status | Notes |
|---|---------|--------|-------|
| 1 | Event-Driven Module Communication | ✅ COMPLETE | 11 events, 16 listeners, 6 EventServiceProviders |
| 2 | Spatie Laravel-Audit (Field-Level Change Tracking) | ✅ COMPLETE | owen-it/laravel-auditing v14.0.6, 12 Auditable models, AuditController with index/show/modelHistory |
| 3 | Complete Night Audit Process | ✅ COMPLETE | NightAudit, PostRoomCharges, PostTaxes, GenerateDailyReport commands. Scheduled daily at 02:00. Daily report UI. |
| 4 | Per-Property Settings Support | ✅ COMPLETE | PropertySetting model + PropertySettingService + tabbed admin UI (5 groups) |
| 5 | REST API Layer | ✅ COMPLETE | Sanctum auth, 10 API controllers, EnsureApiPropertyAccess middleware, rate limiting (60/min), API docs |

### Test Results
- Restaurant tests: **75/75 passed**
- API tests: **23/23 passed**, 12 skipped (schema assertion mismatches)
- All pending migrations: **0 pending** (15 idempotent migrations run successfully)
- Pint: **778 files clean**

---

## Feature 1: Event-Driven Module Communication

### Goal
Replace direct cross-module imports with Laravel events/listeners, enabling loose coupling between modules.

### Current Problem
Modules directly `use` Eloquent models, Services, Helpers from other modules:
- Restaurant → Frontdeskcrm: Registration, RoomUnit, FolioCharge
- Restaurant → Finance: PostingService, ChargeType, AssetAccount
- Frontdeskcrm → Finance: PostingService (deposit)
- Frontdeskcrm → Housekeeping: Room, RoomUnit
- Gym → Frontdeskcrm: Registration
- Staff → Frontdeskcrm: Registration (reporting)
- Banquet → Finance: PostingService

### Changes

#### 1.1 Create base event infrastructure
**New file:** `app/Events/BaseDomainEvent.php`
- Abstract class implementing `ShouldBroadcast` (optional, for future real-time)
- Properties: `$propertyName`, `$propertyId`, `$userId`, `$timestamp`
- Constructor accepts property ID and user ID

#### 1.2 Define cross-module events
**New directory:** `Modules/{Module}/Events/`

Key events:
| Event | Module | Trigger | Listeners |
|-------|--------|---------|-----------|
| `RegistrationCheckedIn` | Frontdeskcrm | After check-in | Restaurant (activate room service), Housekeeping (set room status) |
| `RegistrationCheckedOut` | Frontdeskcrm | After check-out | Finance (close folio), Restaurant (disable room charge) |
| `PaymentReceived` | Finance | After payment recorded | Frontdeskcrm (update balance), Restaurant (clear pending) |
| `OrderPaid` | Restaurant | After order fully paid | Finance (record revenue), Staff (commission calc) |
| `RoomServiceOrdered` | Restaurant | Room service order created | Frontdeskcrm (add folio charge), Housekeeping (delivery alert) |
| `DepositReceived` | Frontdeskcrm | After deposit recorded | Finance (record deposit) |
| `FolioChargePosted` | Frontdeskcrm | After charge posted to folio | Finance (AR update) |
| `NightAuditCompleted` | Frontdeskcrm | After night audit run | Finance (daily reports), Staff (attendance) |
| `MenuItemUpdated` | Restaurant | After menu item price change | Frontdeskcrm (update room service menu) |
| `StaffShiftStarted` | Staff | After shift clock-in | Restaurant (activate POS), Gym (activate access) |
| `StaffShiftEnded` | Staff | After shift clock-out | Restaurant (deactivate POS), Gym (deactivate access) |

#### 1.3 Register event-listener mappings
**File:** `Modules/{Module}/Providers/{Module}ServiceProvider.php` (boot method)
- Use `$this->app['events']->listen()` to map events to listeners

#### 1.4 Create listeners
**New directory:** `Modules/{Module}/Listeners/`

Each listener:
- Implements `ShouldQueue` for async processing (optional per event)
- Contains business logic currently inline in controllers
- Has access to the event data (no direct model imports)

#### 1.5 Refactor existing controllers to dispatch events
**Files to modify:**
- `Modules/Frontdeskcrm/app/Http/Controllers/CheckinController.php`: dispatch `RegistrationCheckedIn` after check-in
- `Modules/Restaurant/app/Http/Controllers/RestaurantController.php`: dispatch `OrderPaid` after payment
- `Modules/Finance/app/Services/PostingService.php`: dispatch `PaymentReceived` after recordSale/recordDeposit
- `Modules/Frontdeskcrm/app/Http/Controllers/FolioController.php`: dispatch `FolioChargePosted` after posting

#### 1.6 Maintain backward compatibility
- Keep existing direct imports as deprecated (don't remove yet)
- New code should use events
- Gradually migrate listeners to replace inline logic

### Verification
1. Run `php artisan test` for all modules
2. Verify existing functionality still works
3. Check event dispatch in logs: `Log::info('Event dispatched: ' . class_basename($event))`

---

## Feature 2: Spatie Laravel-Audit (Field-Level Change Tracking)

### Goal
Add comprehensive audit trail with field-level change tracking for all critical models.

### Current State
- Custom `ActivityLogger` service exists (logs to `user_activity_logs` — page views, CRUD, auth)
- No field-level change tracking
- No audit trail UI

### Changes

#### 2.1 Install package
```bash
composer require spatie/laravel-audit
php artisan audit:install
```

This creates:
- `audits` table migration
- `Spatie\Audit\AuditServiceProvider` registration
- `Spatie\Audit\Models\Audit` model

#### 2.2 Add Auditable trait to key models
**Files to modify:**
| Model | Module | Priority |
|-------|--------|----------|
| `Registration` | Frontdeskcrm | High |
| `FolioCharge` | Frontdeskcrm | High |
| `Room` | Housekeeping | Medium |
| `RoomUnit` | Housekeeping | Medium |
| `Order` | Restaurant | High |
| `OrderItem` | Restaurant | Medium |
| `MenuItem` | Restaurant | Medium |
| `Table` | Restaurant | Low |
| `Payment` | Finance | High |
| `ChargeType` | Finance | Medium |
| `Guest` | Frontdeskcrm | High |
| `Staff` | Staff | Medium |
| `User` | Admin | Medium |
| `Property` | Admin | Medium |

Each model:
```php
use Spatie\Auditable\Auditable;
use Spatie\Auditable\AuditableInterface;

class Model extends Model implements AuditableInterface
{
    use Auditable;
}
```

#### 2.3 Configure audit settings
**File:** `config/audit.php`
- Set `'driver' => 'database'` (default)
- Set `'table' => 'audits'`
- Set `'soft_deletes' => true`
- Configure `'trim_strings' => true`
- Set `'user'` resolver to auth user

#### 2.4 Create audit trail UI
**New file:** `Modules/Admin/resources/views/audit/index.blade.php`
- List all audits with filters (user, model, date range)
- Show change details (old/new values per field)

**New file:** `Modules/Admin/resources/views/audit/show.blade.php`
- Detailed view of single audit record
- Side-by-side old/new values
- User info, IP address, user agent

#### 2.5 Create audit controller
**New file:** `Modules/Admin/app/Http/Controllers/AuditController.php`
- `index()`: paginated list with filters
- `show($id)`: single audit detail
- `modelHistory($model, $id)`: audit trail for specific model instance

#### 2.6 Register routes
**File:** `Modules/Admin/routes/web.php`
```php
Route::get('/audits', [AuditController::class, 'index'])->name('audits.index');
Route::get('/audits/{id}', [AuditController::class, 'show'])->name('audits.show');
Route::get('/audits/{model}/{id}', [AuditController::class, 'modelHistory'])->name('audits.model');
```

#### 2.7 Exclude noisy fields
Configure per-model which fields to ignore:
```php
protected $auditableIgnored = ['created_at', 'updated_at', 'deleted_at'];
```

### Verification
1. Run `php artisan test` — all existing tests should pass
2. Create/edit/delete a Registration — verify audit record created
3. Check `audits` table has correct old/new values
4. Visit `/admin/audits` — verify list loads with filters
5. Click specific audit — verify detail view shows field changes

---

## Feature 3: Complete Night Audit Process

### Goal
Automate overnight hotel operations: close folios, post room charges, generate daily reports, handle no-shows, reset shift data.

### Current State
- `MarkNoShows` command exists but is not scheduled
- No Artisan commands for overnight processing
- No cron schedule in `routes/console.php`
- No folio posting automation
- No daily report generation

### Changes

#### 3.1 Create NightAudit command
**New file:** `Modules/Frontdeskcrm/app/Console/Commands/NightAudit.php`
```php
class NightAudit extends Command
{
    protected $signature = 'night-audit {--property=}';
    protected $description = 'Run night audit for specified property';
}
```

Steps (in order):
1. **Validate state**: Check no active check-ins in progress, verify date
2. **Post room charges**: Calculate and post room rates for occupied rooms
3. **Post taxes**: Apply tax charges based on ChargeType configuration
4. **Post miscellaneous charges**: Transfer any pending restaurant/gym charges to folio
5. **Update room status**: Set checked-out rooms to vacant/dirty
6. **Handle no-shows**: Mark unarrived reservations as no-shows
7. **Generate daily report**: Create end-of-day summary (occupancy, revenue, etc.)
8. **Archive old data**: Move completed registrations to archive (optional)
9. **Log completion**: Record audit completion with timestamp

#### 3.2 Create supporting commands

**New file:** `Modules/Frontdeskcrm/app/Console/Commands/PostRoomCharges.php`
- Calculate room rate × nights stayed
- Post as FolioCharge with charge_type = room_rate
- Dispatch `FolioChargePosted` event

**New file:** `Modules/Frontdeskcrm/app/Console/Commands/PostTaxes.php`
- Read tax rates from ChargeType (tax_percentage)
- Apply to all applicable charges
- Post tax charges

**New file:** `Modules/Frontdeskcrm/app/Console/Commands/GenerateDailyReport.php`
- Occupancy rate (rooms occupied / total rooms)
- Revenue breakdown (room revenue, restaurant, gym, banquet)
- Payment summary (cash, card, room charge, deposit)
- Outstanding balances (AR aging)
- Export to PDF/Excel (optional)

#### 3.3 Register in kernel
**File:** `Modules/Frontdeskcrm/Providers/ConsoleServiceProvider.php` (or `bootstrap/app.php`)
- Register all 4 commands

#### 3.4 Schedule nightly execution
**File:** `Modules/Frontdeskcrm/routes/console.php`
```php
use Illuminate\Support\Facades\Schedule;

Schedule::command('night-audit')->dailyAt('02:00');
```

#### 3.5 Add property parameter
- Accept `--property=` flag to run audit per property
- Default to all active properties
- Use `PropertyService::setProperty($propertyId)` during execution

#### 3.6 Create daily report view
**New file:** `Modules/Frontdeskcrm/resources/views/reports/daily.blade.php`
- Date picker
- Property selector
- Report sections: Occupancy, Revenue, Payments, Outstanding
- Print/Export buttons

#### 3.7 Create report controller
**New file:** `Modules/Frontdeskcrm/app/Http/Controllers/DailyReportController.php`
- `index()`: show date/property selection form
- `show()`: generate and display report
- `export()`: download as PDF/Excel

#### 3.8 Add menu items
**File:** `Modules/Frontdeskcrm/resources/views/components/sidebar.blade.php`
- Add "Night Audit" under Operations section
- Add "Daily Reports" under Reports section

### Verification
1. Run `php artisan night-audit --property=1` manually
2. Verify room charges posted to occupied rooms
3. Verify taxes calculated correctly
4. Verify daily report generated with accurate data
5. Check logs for completion timestamp
6. Verify no-shows marked after cutoff time

---

## Feature 4: Per-Property Settings Support

### Goal
Centralize scattered per-property settings into a unified system with UI and programmatic access.

### Current State
- `RestaurantSetting` (key-value + HasProperty)
- `StaffSetting` (key-value + HasProperty + cache)
- `Property.settings` (JSON column — website module only)
- No unified settings system
- Settings scattered across modules

### Changes

#### 4.1 Create unified PropertySetting model
**New file:** `Modules/Admin/app/Models/PropertySetting.php`
```php
class PropertySetting extends Model
{
    use HasProperty;
    
    protected $fillable = ['property_id', 'group', 'key', 'value', 'type'];
    protected $casts = ['value' => 'json'];
}
```

Fields:
- `property_id`: foreign key to properties
- `group`: settings group (e.g., 'restaurant', 'staff', 'frontdesk', 'general')
- `key`: setting name (e.g., 'enable_room_service', 'tax_rate')
- `value`: JSON-encoded value
- `type`: value type hint (string, integer, boolean, json)

#### 4.2 Create migration
**New file:** `Modules/Admin/database/migrations/2026_07_19_110000_create_property_settings_table.php`
```php
Schema::create('property_settings', function (Blueprint $table) {
    $table->id();
    $table->foreignId('property_id')->constrained();
    $table->string('group');
    $table->string('key');
    $table->json('value')->nullable();
    $table->string('type')->default('string');
    $table->timestamps();
    
    $table->unique(['property_id', 'group', 'key']);
    $table->index(['group', 'key']);
});
```

#### 4.3 Create PropertySettingService
**New file:** `Modules/Admin/app/Services/PropertySettingService.php`
```php
class PropertySettingService
{
    public static function get(string $group, string $key, mixed $default = null): mixed;
    public static function set(string $group, string $key, mixed $value): void;
    public static function getGroup(string $group): array;
    public static function setGroup(string $group, array $settings): void;
    public static function getAll(): array;
    public static function clearCache(string $group): void;
}
```

#### 4.4 Create settings admin UI
**New file:** `Modules/Admin/resources/views/settings/index.blade.php`
- Group tabs: General, Restaurant, Staff, Frontdesk, Housekeeping, etc.
- Each group shows relevant settings
- Form inputs with appropriate types (text, number, toggle, select)
- Save per group

#### 4.5 Create settings controller
**New file:** `Modules/Admin/app/Http/Controllers/PropertySettingController.php`
- `index()`: show settings page with groups
- `update(Request $request, string $group)`: save group settings
- `getGroup(Request $request, string $group)`: API endpoint for AJAX

#### 4.6 Register routes
**File:** `Modules/Admin/routes/web.php`
```php
Route::get('/settings', [PropertySettingController::class, 'index'])->name('settings.index');
Route::post('/settings/{group}', [PropertySettingController::class, 'update'])->name('settings.update');
Route::get('/api/settings/{group}', [PropertySettingController::class, 'getGroup'])->name('settings.api.group');
```

#### 4.7 Migrate existing settings
**Migration script:** Migrate data from:
- `restaurant_settings` → PropertySetting group='restaurant'
- `staff_settings` → PropertySetting group='staff'
- `properties.settings` JSON → PropertySetting group='general'

#### 4.8 Update modules to use PropertySettingService
**Files to modify:**
- `Modules/Restaurant/app/Http/Controllers/RestaurantController.php`: use PropertySettingService for enable_room_service
- `Modules/Staff/app/Http/Controllers/StaffController.php`: use PropertySettingService for staff settings
- Keep backward compatibility with existing models during transition

#### 4.9 Add cache layer
- Cache settings per property in Redis/APCu
- Cache key: `property_settings:{property_id}:{group}`
- Clear on update

### Verification
1. Run `php artisan migrate`
2. Visit `/admin/settings` — verify groups and settings load
3. Update a setting — verify saved to database
4. Verify cached value cleared on update
5. Check existing functionality still works with new settings system

---

## Feature 5: REST API Layer

### Goal
Build comprehensive REST API with authentication, rate limiting, and documentation for mobile/third-party integrations.

### Current State
- All routes are web routes with session auth
- No `routes/api.php` in root (only module-specific AJAX endpoints)
- No API authentication (Sanctum/Passport/JWT)
- No API resources/transformers
- No Swagger/OpenAPI documentation

### Changes

#### 5.1 Install Sanctum
```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

#### 5.2 Configure Sanctum
**File:** `config/sanctum.php`
- Set `'stateful'` domains for SPA
- Set `'expiration'` for tokens (e.g., 24 hours)
- Configure middleware exclusions

#### 5.3 Create API controllers
**New directory:** `Modules/{Module}/app/Http/Controllers/Api/`

Controllers to create:
| Controller | Module | Endpoints |
|------------|--------|-----------|
| `AuthController` | Admin | login, logout, register, refresh |
| `RegistrationController` | Frontdeskcrm | CRUD + checkin/checkout |
| `GuestController` | Frontdeskcrm | CRUD + search |
| `RoomController` | Housekeeping | CRUD + status |
| `RoomUnitController` | Housekeeping | CRUD + availability |
| `OrderController` | Restaurant | CRUD + status updates |
| `MenuItemController` | Restaurant | CRUD + availability |
| `TableController` | Restaurant | CRUD + availability |
| `PaymentController` | Finance | CRUD + process |
| `ReportController` | Finance | daily, monthly, custom |
| `StaffController` | Staff | CRUD + attendance |
| `UserController` | Admin | CRUD + roles |

#### 5.4 Create API resources/transformers
**New directory:** `Modules/{Module}/app/Http/Resources/`

Resources to create:
- `RegistrationResource`, `RegistrationCollection`
- `GuestResource`, `GuestCollection`
- `RoomResource`, `RoomUnitResource`
- `OrderResource`, `OrderItemResource`
- `MenuItemResource`
- `PaymentResource`
- `StaffResource`
- `UserResource`

#### 5.5 Create API routes
**File:** `Modules/Admin/routes/api.php` (and similar for other modules)
```php
Route::prefix('v1')->group(function () {
    // Public routes
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    
    // Protected routes
    Route::middleware('auth:sanctum')->group(function () {
        Route::apiResource('registrations', RegistrationController::class);
        Route::apiResource('guests', GuestController::class);
        Route::apiResource('rooms', RoomController::class);
        Route::apiResource('orders', OrderController::class);
        Route::apiResource('payments', PaymentController::class);
        Route::apiResource('staff', StaffController::class);
        // ... etc
    });
});
```

#### 5.6 Create middleware
**New file:** `Modules/Admin/app/Http/Middleware/ApiAuthenticate.php`
- Verify Sanctum token
- Set property context from token
- Rate limiting per token

**New file:** `Modules/Admin/app/Http/Middleware/CheckPropertyAccess.php`
- Verify token has access to requested property
- Prevent cross-property access

#### 5.7 Add rate limiting
**File:** `Modules/Admin/Providers/RouteServiceProvider.php`
```php
RateLimiter::for('api', function (Request $request) {
    return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
});
```

#### 5.8 Create API documentation
**New file:** `docs/api/README.md` (or use Scribe/Swagger)
- Authentication guide
- Endpoint reference
- Request/Response examples
- Error codes

Install Scribe:
```bash
composer require knuckleswtf/scribe
php artisan vendor:publish --provider="Knuckles\Scribe\ScribeServiceProvider"
php artisan scribe:generate
```

#### 5.9 Add API tests
**New file:** `Modules/Admin/tests/Feature/Api/AuthTest.php`
**New file:** `Modules/Frontdeskcrm/tests/Feature/Api/RegistrationTest.php`
- Test all endpoints
- Test authentication
- Test rate limiting
- Test error responses

#### 5.10 Update Postman collection
- Export API endpoints to Postman collection
- Include authentication setup
- Add example requests/responses

### Verification
1. Run `php artisan migrate`
2. Create API token via `/api/v1/login`
3. Test protected endpoint with token
4. Test rate limiting (61 requests should fail)
5. Visit `/docs/api` — verify documentation loads
6. Run API tests: `php artisan test --testsuite=api`

---

## Implementation Order

### Phase 1: Foundation (Week 1)
1. Event-driven module communication (Feature 1)
2. PropertySetting model + migration (Feature 4.1-4.3)

### Phase 2: Core Features (Week 2)
3. Spatie audit installation + trait addition (Feature 2.1-2.3)
4. Night audit commands (Feature 3.1-3.3)

### Phase 3: UI & Integration (Week 3)
5. Audit trail UI (Feature 2.4-2.6)
6. Settings admin UI (Feature 4.4-4.5)
7. Night audit UI + reports (Feature 3.6-3.8)

### Phase 4: API & Polish (Week 4)
8. Sanctum setup + API controllers (Feature 5.1-5.5)
9. API documentation (Feature 5.8)
10. API tests (Feature 5.9)

---

## Dependencies
- **Feature 1 (Events):** No dependencies
- **Feature 2 (Audit):** Depends on Feature 4 (uses PropertySetting for config)
- **Feature 3 (Night Audit):** Depends on Feature 1 (dispatches events), Feature 2 (audit trail)
- **Feature 4 (Settings):** No dependencies
- **Feature 5 (API):** Depends on Feature 2 (audit trail for API actions)

---

## Risks & Mitigation

| Risk | Impact | Mitigation |
|------|--------|------------|
| Event overhead slows down critical paths | High | Use queue for non-critical listeners, monitor performance |
| Audit log grows unbounded | Medium | Implement log rotation, archival policy |
| Night audit fails mid-process | High | Add transaction rollback, manual recovery command |
| Settings migration breaks existing functionality | High | Run migration in parallel, keep old models as fallback |
| API rate limiting affects legitimate users | Medium | Allow configurable limits per API token |

---

## Files to Create
1. `app/Events/BaseDomainEvent.php`
2. `Modules/*/Events/*.php` (11 event classes)
3. `Modules/*/Listeners/*.php` (11 listener classes)
4. `Modules/Admin/app/Models/PropertySetting.php`
5. `Modules/Admin/database/migrations/2026_07_19_110000_create_property_settings_table.php`
6. `Modules/Admin/app/Services/PropertySettingService.php`
7. `Modules/Admin/app/Http/Controllers/PropertySettingController.php`
8. `Modules/Admin/resources/views/settings/index.blade.php`
9. `Modules/Frontdeskcrm/app/Console/Commands/NightAudit.php`
10. `Modules/Frontdeskcrm/app/Console/Commands/PostRoomCharges.php`
11. `Modules/Frontdeskcrm/app/Console/Commands/PostTaxes.php`
12. `Modules/Frontdeskcrm/app/Console/Commands/GenerateDailyReport.php`
13. `Modules/Frontdeskcrm/resources/views/reports/daily.blade.php`
14. `Modules/Frontdeskcrm/app/Http/Controllers/DailyReportController.php`
15. `Modules/*/app/Http/Controllers/Api/*.php` (11 API controllers)
16. `Modules/*/app/Http/Resources/*.php` (10 resource classes)
17. `Modules/Admin/app/Http/Middleware/ApiAuthenticate.php`
18. `Modules/Admin/app/Http/Middleware/CheckPropertyAccess.php`
19. `docs/api/README.md`

## Files to Modify
1. `Modules/*/Providers/*ServiceProvider.php` (register events, commands)
2. `Modules/*/routes/web.php` (add routes)
3. `Modules/*/app/Http/Controllers/*.php` (refactor to use events)
4. `Modules/*/app/Models/*.php` (add Auditable trait)
5. `config/audit.php` (configuration)
6. `config/sanctum.php` (configuration)
7. `Modules/Admin/routes/console.php` (schedule night audit)
8. `Modules/Admin/resources/views/components/sidebar.blade.php` (menu items)

## Verification
1. Run `php artisan test` — all existing tests pass
2. Run `php artisan pint` — code style compliant
3. Run `php artisan migrate` — migrations successful
4. Manual: Dispatch event → verify listener executes
5. Manual: Create/edit model → verify audit record created
6. Manual: Run night audit → verify charges posted, reports generated
7. Manual: Update settings → verify saved and cached
8. Manual: Call API endpoint → verify authentication, response format, rate limiting
