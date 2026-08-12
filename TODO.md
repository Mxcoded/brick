# Todo — Website Rebuild

Server-side authoritative pricing via `WebsiteRateService` + `BookingCartService::getCartSummary()`;
Livewire `booking-summary-updated` bridge pushes totals to the review panel.

- [x] **Phase 1 — Foundations** (`8fc01f7`): `BookingDraftService`, draft route, stepper, tests.
- [x] **Phase 2 — Search** (`55e8a2e`): stateless `CartSidebar` Livewire on room search.
- [x] **Phase 3 — Add-ons** (`b5860f6`): guest add-on flow + admin CRUD + pricing; `BookingAddonTest` (31 tests); fixed 3 real bugs (cart clear before add-ons, Livewire dispatch array-wrapping, soft-delete assertion).
- [x] **Phase 4 — Review** (`e3a93e9`): review step with edit buttons + `#rvGrandTotal`/`#rvAddons`.
- [x] **Phase 5 — Payment** (`cd688f8`): paystack init charges add-on-inclusive amounts (single + grouped).
- [x] **Phase 6 — Confirmation** (`4ca7d62`): room line separates add-ons (no double-count), abandoned-payment access to confirmation, `POST /booking/pay/{ref}` + "Complete Payment" button.
- [ ] **Phase 7 — Notifications**: audit `sendConfirmationEmail()` + guest/staff mailables (add-on totals, payment-state banner, Pay Now link), `resendConfirmation`.
- [ ] **Phase 8 — PMS sync**: frontdesk registration/handoff maps bookings + add-ons + `booking_group_id` correctly.

## Other fixes

- [x] `fix(staff)` (`1367daa`): attendance report `month`/`year` query strings cast to int (Carbon `setUnit` TypeError).
- [x] `feat(frontdeskcrm)`: downloadable Excel guide + fillable template for guest bulk import (`GET /guests/import/template`), buttons on Import page + Guest Directory, `GuestImportTest` (5 tests).

## Verification routine (per completed phase)

Run full `Modules/Website/tests` + `Modules/Staff/tests`, `pint --dirty`, Vite build (JS changes only), `view:cache` then `route:cache`, then commit.

Suite baseline: Website **208** green, Staff **57** green (PHP 8.5 `DEPR` marks pass).
