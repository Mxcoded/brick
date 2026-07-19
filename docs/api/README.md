# Brickspoint HMS REST API Documentation

Base URL: `/api/v1`

Authentication: Bearer token via Laravel Sanctum

Property Context: All protected routes require an `X-Property-ID` header or `property_id` query parameter.

---

## Authentication

### POST /api/v1/login
Authenticate a user and receive a token.

**Request:**
```json
{
    "email": "user@example.com",
    "password": "password"
}
```

**Response (200):**
```json
{
    "token": "1|abc123...",
    "user": {
        "id": 1,
        "name": "Admin User",
        "email": "user@example.com",
        "type": "staff",
        "current_property": {
            "id": 1,
            "name": "Brickspoint Asokoro"
        }
    }
}
```

### POST /api/v1/register
Register a new user.

**Request:**
```json
{
    "name": "New User",
    "email": "new@example.com",
    "password": "password",
    "password_confirmation": "password"
}
```

### POST /api/v1/logout
Revoke the current token. Requires auth.

### POST /api/v1/refresh
Get a fresh token. Requires auth.

### GET /api/v1/me
Get the authenticated user's profile. Requires auth.

### POST /api/v1/switch-property
Switch the active property context.

**Request:**
```json
{
    "property_id": 2
}
```

---

## Registrations

### GET /api/v1/registrations
List all registrations for the current property.

**Query params:** `status`, `search`, `per_page`

### POST /api/v1/registrations
Create a new registration.

**Request:**
```json
{
    "guest_id": 1,
    "room_unit_id": 5,
    "check_in": "2026-07-19",
    "check_out": "2026-07-22",
    "room_rate": 25000,
    "payment_method": "cash"
}
```

### GET /api/v1/registrations/{id}
Show a single registration.

### PUT /api/v1/registrations/{id}
Update a registration.

### DELETE /api/v1/registrations/{id}
Delete (soft-delete) a registration.

### POST /api/v1/registrations/{id}/checkin
Check in a registration. Sets `stay_status` to `checked_in`.

### POST /api/v1/registrations/{id}/checkout
Check out a registration. Sets `stay_status` to `checked_out`.

---

## Guests

### GET /api/v1/guests
List all guests. **Query params:** `search`, `per_page`

### POST /api/v1/guests
Create a new guest.

### GET /api/v1/guests/{id}
Show a single guest.

### PUT /api/v1/guests/{id}
Update a guest.

### DELETE /api/v1/guests/{id}
Delete a guest.

### GET /api/v1/guests/search?query={term}
Search guests by name, email, or phone.

---

## Orders (Restaurant)

### GET /api/v1/orders
List orders. **Query params:** `status`, `type`, `per_page`

### POST /api/v1/orders
Create a new order.

### GET /api/v1/orders/{id}
Show a single order with items.

### PUT /api/v1/orders/{id}
Update an order.

### DELETE /api/v1/orders/{id}
Delete an order.

### POST /api/v1/orders/{id}/status
Update order status.

**Request:**
```json
{
    "status": "preparing"
}
```

---

## Menu Items (Restaurant)

### GET /api/v1/menu-items
List menu items. **Query params:** `category_id`, `available`, `per_page`

### POST /api/v1/menu-items
Create a menu item.

### GET /api/v1/menu-items/{id}
Show a menu item.

### PUT /api/v1/menu-items/{id}
Update a menu item.

### DELETE /api/v1/menu-items/{id}
Delete (soft-delete) a menu item.

---

## Tables (Restaurant)

### GET /api/v1/tables
List tables. **Query params:** `section`, `per_page`

### POST /api/v1/tables
Create a table.

### GET /api/v1/tables/{id}
Show a table.

### PUT /api/v1/tables/{id}
Update a table.

### DELETE /api/v1/tables/{id}
Delete a table.

---

## Rooms (Housekeeping)

### GET /api/v1/rooms
List rooms. **Query params:** `type_id`, `per_page`

### POST /api/v1/rooms
Create a room.

### GET /api/v1/rooms/{id}
Show a room.

### PUT /api/v1/rooms/{id}
Update a room.

### DELETE /api/v1/rooms/{id}
Delete a room.

---

## Room Units (Housekeeping)

### GET /api/v1/room-units
List room units. **Query params:** `room_id`, `status`, `per_page`

### POST /api/v1/room-units
Create a room unit.

### GET /api/v1/room-units/{id}
Show a room unit.

### PUT /api/v1/room-units/{id}
Update a room unit.

### DELETE /api/v1/room-units/{id}
Delete a room unit.

### POST /api/v1/room-units/{id}/status
Update room unit status.

**Request:**
```json
{
    "status": "clean"
}
```

---

## Payments (Finance)

### GET /api/v1/payments
List payments. **Query params:** `status`, `per_page`

### POST /api/v1/payments
Record a payment.

### GET /api/v1/payments/{id}
Show a payment.

---

## Employees (Staff)

### GET /api/v1/employees
List employees. **Query params:** `department_id`, `status`, `search`, `per_page`

### POST /api/v1/employees
Create an employee.

### GET /api/v1/employees/{id}
Show an employee.

### PUT /api/v1/employees/{id}
Update an employee.

### DELETE /api/v1/employees/{id}
Delete an employee.

---

## Users (Admin)

### GET /api/v1/users
List users.

### POST /api/v1/users
Create a user.

### GET /api/v1/users/{id}
Show a user.

### PUT /api/v1/users/{id}
Update a user.

### DELETE /api/v1/users/{id}
Delete a user.

---

## Error Responses

All errors follow this format:

```json
{
    "message": "Error description"
}
```

| Status Code | Meaning |
|-------------|---------|
| 401 | Unauthenticated — invalid or missing token |
| 403 | Forbidden — no access to requested property |
| 404 | Not Found — resource does not exist |
| 422 | Validation Error — request data is invalid |
| 429 | Too Many Requests — rate limit exceeded (60/min) |
| 500 | Server Error |

---

## Rate Limiting

All API endpoints are rate-limited to **60 requests per minute** per authenticated user. Unauthenticated requests are limited by IP address.

---

## Property Isolation

Every API request is scoped to a single property via the `X-Property-ID` header. Users can only access data belonging to properties they are assigned to. Attempting to access data from an unassigned property returns `403 Forbidden`.
