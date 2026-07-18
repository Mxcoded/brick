# Plan: Restaurant Room Service Toggle

## Context
The restaurant module has hardcoded room-related features (Room Service card on landing page, "Charge to Room" payment option in the POS). Properties that don't operate a hotel shouldn't see these options. A per-property toggle in restaurant settings will conditionally enable/disable all room-related UI and functionality.

## Changes

### 1. Seed the new setting default
**File:** `Modules/Restaurant/database/seeders/RestaurantSettingSeeder.php`
- Add `'enable_room_service' => '0'` to the defaults array (off by default — properties opt in)

### 2. Add toggle to admin settings page
**File:** `Modules/Restaurant/resources/views/admin/settings.blade.php`
- Add a "Room Service" section below the existing form fields
- Toggle: "Enable Room Service (Charge to Room & Room Orders)" — checkbox or select (Yes/No)
- POSTs alongside the existing settings

### 3. Update controller to read/save the toggle
**File:** `Modules/Restaurant/app/Http/Controllers/RestaurantController.php`
- `adminSettings()`: pass `enable_room_service` value to the view
- `updateSettings()`: add `enable_room_service` validation + save
- `posSettings()`: include `enable_room_service` in the JSON response (for JS-driven hiding)

### 4. Conditionally show Room Service card on landing page
**File:** `Modules/Restaurant/resources/views/index.blade.php`
- Wrap the "Room Service" card in `@if($enableRoomService)` blade conditional

### 5. Conditionally hide "Charge to Room" in waiter dashboard payment modal
**File:** `Modules/Restaurant/resources/views/waiter/dashboard.blade.php`
- Hide the `<option value="room_charge">` when room service is disabled
- Hide the room number input + guest lookup UI when room service is disabled
- Can use either a Blade `@if` (passed from controller) or check the `posSettings` JSON in Alpine.js

### 6. Guard the guest lookup API endpoint
**File:** `Modules/Restaurant/app/Http/Controllers/RestaurantController.php`
- `guestLookup()`: check the `enable_room_service` setting; return 403 if disabled
- `processPayment()` with `room_charge` method: check setting; return 403 if disabled

### 7. Hide room-type orders from POS order cards (optional, lower priority)
- Room orders created via guest menu would still show in KDS and waiter active orders
- No change needed here — if an order exists, it should be fulfilled regardless of the toggle

## Files to modify
1. `Modules/Restaurant/database/seeders/RestaurantSettingSeeder.php`
2. `Modules/Restaurant/resources/views/admin/settings.blade.php`
3. `Modules/Restaurant/app/Http/Controllers/RestaurantController.php`
4. `Modules/Restaurant/resources/views/index.blade.php`
5. `Modules/Restaurant/resources/views/waiter/dashboard.blade.php`

## Verification
1. Run Pint
2. Run full restaurant test suite: `php artisan test Modules/Restaurant/tests/`
3. Manual: toggle ON → Room Service card appears, room_charge option visible, guest lookup works
4. Manual: toggle OFF → Room Service card hidden, room_charge option hidden, guest lookup returns 403
