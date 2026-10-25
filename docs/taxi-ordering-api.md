---
group: Ordering
title: Taxi Ordering API
order: 40
---

# Taxi Ordering API

Taxi ordering endpoints require an authenticated Sanctum user. Driver assignment endpoints additionally require the `driver` role.

Base middleware:

- `auth:sanctum`
- `user.active.check`

Taxi booking also uses:

- `idempotency`
- `throttle.order.api`

## Check Taxi Location Availability

```http
GET /api/taxi/location/available?latitude=5.6037&longitude=-0.187
Authorization: Bearer {token}
```

Successful response:

```json
{
  "message": "Service location"
}
```

Unavailable response:

```json
{
  "message": "Does not service location"
}
```

Status code: `400`

## Recent Taxi Dropoff Locations

```http
GET /api/taxi/location/history
Authorization: Bearer {token}
```

Returns up to 10 previous dropoff locations for the authenticated user.

Example response:

```json
[
  {
    "latitude": "5.6500",
    "longitude": "-0.1200",
    "address": "Airport Road",
    "name": "Airport Road"
  }
]
```

## List Vehicle Types

```http
GET /api/vehicle/types
Authorization: Bearer {token}
```

Example response:

```json
[
  {
    "id": 1,
    "name": "Standard",
    "base_fare": 10,
    "distance_fare": 3,
    "time_fare": 1,
    "min_fare": 15,
    "is_active": 1
  }
]
```

## Calculate Taxi Fare

```http
GET /api/vehicle/types/pricing
Authorization: Bearer {token}
```

Query parameters:

- `pickup`: pickup coordinate string used by the pricing trait, for example `5.6037,-0.187`.
- `dropoff`: dropoff coordinate string, for example `5.6500,-0.1200`.
- `country_code`: optional country code for multiple-currency taxi pricing.

Successful response:

```json
[
  {
    "id": 1,
    "name": "Standard",
    "base_fare": 10,
    "distance_fare": 3,
    "time_fare": 1,
    "min_fare": 15,
    "total": 42.5,
    "tax": 5,
    "trip_distance": 8.3,
    "trip_time": 18,
    "encrypted": "encrypted-vehicle-type-pricing",
    "nearest_driver_in_minutes": 4,
    "currency": {
      "id": 1,
      "code": "GHS",
      "symbol": "GH₵"
    }
  }
]
```

Send the `encrypted` value back as `vehicle_type` when booking to avoid recalculation drift.

## Book Taxi Order

```http
POST /api/taxi/book/order
Authorization: Bearer {token}
Content-Type: application/json
Idempotency-Key: {unique-request-key}
```

Request body:

```json
{
  "vehicle_type": "encrypted-vehicle-type-pricing",
  "vehicle_type_id": 1,
  "payment_method_id": 2,
  "pickup": {
    "lat": 5.6037,
    "lng": -0.187,
    "address": "Pickup address"
  },
  "dropoff": {
    "lat": 5.6500,
    "lng": -0.1200,
    "address": "Dropoff address"
  },
  "sub_total": 40,
  "discount": 0,
  "tax": 2.5,
  "total": 42.5,
  "tip": 0,
  "fees": [],
  "coupon_code": null,
  "pickup_date": "2026-06-07",
  "pickup_time": "14:30"
}
```

Successful response:

```json
{
  "order": {
    "id": 80,
    "code": "1234567890",
    "user_id": 3,
    "payment_method_id": 2,
    "sub_total": 40,
    "discount": 0,
    "tax": 2.5,
    "total": 42.5,
    "payment_status": "pending",
    "status": "pending"
  },
  "message": "Order placed successfully. Relax while the vendor process your order",
  "link": ""
}
```

Amount validation error:

```json
{
  "message": "An error occured while trying to book your trip  Amount issue"
}
```

Status code: `400`

## Current Taxi Order

```http
GET /api/taxi/current/order
Authorization: Bearer {token}
```

Returns the current non-failed, non-cancelled, non-delivered, non-scheduled taxi order for the authenticated user or driver.

Example response:

```json
{
  "order": {
    "id": 80,
    "code": "1234567890",
    "status": "pending",
    "driver": {
      "id": 12,
      "name": "Driver Name",
      "vehicle": {
        "id": 3,
        "reg_no": "GR-1234-26"
      }
    },
    "payment_method": {
      "id": 2,
      "name": "Cash"
    }
  }
}
```

## Rateable Taxi Order

```http
GET /api/taxi/rateable/order
Authorization: Bearer {token}
```

Returns the latest delivered/successful taxi order that has not been reviewed by the authenticated user.

Example response:

```json
{
  "order": {
    "id": 80,
    "code": "1234567890",
    "status": "delivered"
  }
}
```

## Cancel Taxi Order

```http
GET /api/taxi/order/cancel/{id}
Authorization: Bearer {token}
```

Successful response:

```json
{
  "message": "Trip cancelled successfully"
}
```

Failure response:

```json
{
  "message": "Trip cancellation failed"
}
```

Status code: `400`

## Driver Info

```http
GET /api/taxi/driver/info/{id}
Authorization: Bearer {token}
```

Successful response:

```json
{
  "driver": {
    "id": 12,
    "name": "Driver Name",
    "phone": "+233501234567"
  },
  "vehicle": {
    "id": 3,
    "driver_id": 12,
    "reg_no": "GR-1234-26",
    "color": "Black"
  }
}
```

## Driver Accept Taxi Assignment

```http
POST /api/taxi/order/asignment/accept
Authorization: Bearer {driver_token}
Content-Type: application/json
```

Middleware:

- `role:driver`

Request body:

```json
{
  "order_id": 80,
  "status": "preparing"
}
```

Successful response:

```json
{
  "message": "Order accepted and assigned",
  "order": {
    "id": 80,
    "code": "1234567890",
    "driver_id": 12,
    "status": "preparing"
  }
}
```

## Driver Reject Taxi Assignment

```http
POST /api/taxi/order/asignment/reject
Authorization: Bearer {driver_token}
Content-Type: application/json
```

Middleware:

- `role:driver`

Request body:

```json
{
  "order_id": 80
}
```

Successful response:

```json
{
  "message": "Driver reject order successul"
}
```
