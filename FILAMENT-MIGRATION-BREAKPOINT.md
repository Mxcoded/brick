# Filament Migration — Breakpoint Log

Track work here across sessions. Newest first.

---

## BP-002 — Frontdesk PMS authorization closed + role map landed

**Status:** complete, verified, ready to commit
**Date:** 2026-10-03
**Branch:** `website-filament`

### What happened

Migrating `Modules/Frontdeskcrm` to Filament meant registering its resources in
the shared `/admin` panel. Filament's default is `canAccess() => true`, so the
six new PMS resources were reachable by *any* authenticated user — including
roles with no Frontdesk access at all. `guests.*` was already granular, but
`registrations`, `folios`, `invoices`, `rate_codes` and `guest_types` had **no
permissions backing them whatsoever**, because the legacy module sat behind the
single `access_frontdesk_dashboard` gate.

Fixed by giving the module a real taxonomy rather than a panel-wide guard,
which would have hidden the PMS from legitimate staff.

### Files added

| File | Purpose |
|---|---|
| `Modules/Frontdeskcrm/app/Support/FrontdeskPermission.php` | 35-permission taxonomy, `canAccess` helpers, `catalogOverrides()` for the role editor matrix |
| `Modules/Frontdeskcrm/database/seeders/FrontdeskPermissionSeeder.php` | Creates permissions, applies `frontdeskcrm.roles`, revokes from unmapped roles |
| `Modules/Frontdeskcrm/tests/Concerns/GrantsFrontdeskRoles.php` | Test helper mirroring the seeder |
| `Modules/Frontdeskcrm/tests/Feature/FrontdeskRoleMatrixTest.php` | 13 tests / 303 assertions |

### Files changed

- `Modules/Frontdeskcrm/config/config.php` — added `roles` map + `export_permissions`
- `Modules/Frontdeskcrm/database/seeders/FrontdeskcrmDatabaseSeeder.php` — runs the permission seeder first
- `Modules/Frontdeskcrm/app/Filament/Resources/{Guest,Registration,Folio,Invoice,RateCode,GuestType}Resource.php` — `canAccess()` / `canViewAny()` / `canView()` / `canCreate()` / `canEdit()` / `canDelete()`
- `Modules/Frontdeskcrm/app/Http/Controllers/GuestController.php` — **bug fix**, see below

### Design decisions

**Reused `guests.*` rather than renaming.** The permission names already existed
and the legacy Blade routes enforce them. Renaming would have made the Filament
resource and the legacy controller disagree about the same action.

**Split money-moving actions away from operational ones.** `invoices.issue` sits
with the receptionist because issuing a bill is the desk's job. `invoices.void`
and `invoices.credit_note` sit with finance, because they rewrite a receivable.
Same pattern Restaurant uses for `payments.refund` / `orders.void`.

**`guests.import` is separate from `guests.create`.** A 5,000-row spreadsheet
import that overwrites existing profiles is not the same risk as adding one
guest by hand.

**Gate is not a capability.** Every role named in the config map receives
`access_frontdesk_dashboard` so it can reach the shell. The seeder does this
automatically; `test_every_role_in_the_map_gets_the_module_gate` guards it.
This mirrors the bug Restaurant already fixed, where the waiter shipped without
the gate and 403'd in the browser.

### Bug found and fixed (pre-existing, not caused by this work)

`GuestController::import()` instantiated `new GuestsExcelImport` but never
imported the class, so PHP resolved it against the controller's own namespace and
threw `Class "Modules\Frontdeskcrm\Http\Controllers\GuestsExcelImport" not
found`. Committed code, only reachable when an actual `.xlsx`/`.xls` upload hit
that branch — the CSV path masked it. One-line `use` statement added.

### Verification

```
Modules/Frontdeskcrm/tests            158 tests, 766 assertions — pass
FrontdeskRoleMatrixTest                13 tests, 303 assertions — pass
vendor/bin/pint --test <changed files> — pass
```

`test_a_user_without_frontdesk_permissions_reaches_no_pms_resource` is the
regression guard: a `waiter` (a legitimate panel user) gets `403` on all six PMS
resources. Proven to have teeth by temporarily injecting a `hasRole('receptionist')`
probe, which the guard caught, then deleting it.

### Roles configured

`admin`, `gm`, `ggm` (all `*`) · `receptionist` (full PMS minus void/credit-note)
· `finance`, `senior_accountant` (billing only) · `auditor` (read-only)

Before seeding, `access_frontdesk_dashboard` was held only by `admin` and
`receptionist`, so the seeder revokes nothing from a role that had it.

### Follow-up needed after deploy

Run the seeder once against the target database — the PMS resources 403 until the
permission rows exist:

```powershell
php artisan module:seed Frontdeskcrm
```

> **Note:** `gm` and `ggm` do **not** currently hold `access_frontdesk_dashboard`.
> Seeding *grants* it to them (they had nothing before). Confirm that is intended
> before running in production.

### Known pre-existing failures (NOT caused by this work — verified by `git stash`)

6 Admin tests fail on `HEAD` and on a clean provider, from earlier uncommitted
Admin WIP that gated Admin resources on granular permissions:

- `ActivityLogsPageTest > activity logs resource rejects users without permission`
- `AuditTrailRenderTest > non admin user is forbidden`
- `AuditTrailTest > audit trail requires admin permission`
- `AuditTrailTest > restore requires admin permission`
- `LoginLogsTest > login logs requires admin permission`
- `RolePermissionManagementTest > staff without privilege cannot view role and permission resources`

All fail with "Failed asserting that false is true". Green: `ExecutiveDashboardTest`,
`FilamentAdminPanelTest`, `PermissionCatalogTest`, `RbacReconciliationTest`.

### Environment gotchas hit this session

- `php artisan filament:install --panels` **overwrites** an existing
  `AdminPanelProvider`. It destroyed theming, `->login()`, `->default()`, Admin
  page/widget discovery and both `boot()` render hooks. Recovered with
  `git checkout --`. Never run it on a project that already has a panel.
- Stale config cache made `config('frontdeskcrm.roles')` return `[]` because the
  module provider merges config in `boot()`. `php artisan optimize:clear` fixes
  it — and `AGENTS.md` warns never to run `config:cache` while testing.
- PowerShell mangles `php artisan tinker --execute='...$var...'`; inline PHP with
  `$` is unusable. Write a bootstrap script instead.
- PDO `MYSQL_ATTR_SSL_CA` deprecation on PHP 8.5 marks passing tests as
  "deprecated" rather than passing. Cosmetic.

---

## BP-001 — Panel provider restored after accidental overwrite

**Status:** complete
**Branch:** `website-filament`

`filament:install --panels` replaced the committed `AdminPanelProvider` with a
fresh scaffold, which is what collapsed the sidebar to Frontdesk only and made
the Executive Dashboard disappear. Restored from git, then re-applied only the
intended addition — `discoverResources` for Frontdeskcrm, Restaurant, Finance and
Tasks using `module_path()`, matching the existing Admin entry's style.

`bootstrap/providers.php` was also reformatted cosmetically; reverted.