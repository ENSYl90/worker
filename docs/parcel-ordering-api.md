---
group: Ordering
title: Parcel
order: 30
---

# Parcel Ordering API

Parcel ordering uses package vendors, package types, stops, and package pricing. All endpoints in this document require an authenticated Sanctum user.

Base middleware:

- `auth:sanctum`
- `user.active.check`

Order placement also uses:

- `idempotency`
- `throttle.order.api`

## Fetch Parcel Vendors

```http
POST /api/package/order/vendors
Authorization: Bearer {token}
Content-Type: application/json
```

Request body:

```json
{
  "vendor_type_id": 3,
  "package_type_id": 2,
  "correct_only": true,
  "locations": [
    {
      "id": 10,
      "lat": 5.6037,
      "lng": -0.187,
      "address": "Pickup address"
    },
    {
      "id": 11,
      "lat": 5.6500,
      "lng": -0.1200,
      "address": "Dropoff address"
    }
  ]
}
```

Behavior:

- Finds active vendors for the given vendor type and package type pricing.
- Checks whether each vendor services all requested locations.
- When `correct_only` is absent or `true`, returns only vendors that service all locations.
- When `correct_only` is `false`, includes vendors with unsupported locations and an `unsupported_locations` field.

Successful response:

```json
{
  "vendors": [
    {
      "id": 5,
      "name": "Parcel Express",
      "vendor_type_id": 3,
      "is_active": 1,
      "package_types_pricing": [
        {
          "id": 9,
          "vendor_id": 5,
          "package_type_id": 2,
          "base_price": 10,
          "distance_price": 3,
          "size_price": 5
        }
      ]
    }
  ]
}
```

## Calculate Parcel Order Summary

```http
GET /api/package/order/summary
Authorization: Bearer {token}
```

Query parameters:

- `vendor_id`: parcel vendor ID.
- `package_type_id`: package type ID.
- `pickup_location_id`: saved pickup delivery address ID.
- `dropoff_location_id`: saved dropoff delivery address ID.
- `weight`, `width`, `length`, `height`: optional package dimensions.
- `coupon_code`: optional coupon.
- `ignore_check`: set truthy to skip vendor location checks.
- `stops`: array or JSON array of stops for multi-stop delivery.

Example stop payload:

```json
[
  {
    "id": 10,
    "lat": 5.6037,
    "lng": -0.187,
    "name": "Sender",
    "phone": "+233501234567",
    "note": "Pickup from reception"
  },
  {
    "id": 11,
    "lat": 5.6500,
    "lng": -0.1200,
    "name": "Recipient",
    "phone": "+233551234567",
    "note": "Call on arrival"
  }
]
```

Successful response:

```json
{
  "delivery_fee": 25,
  "package_type_fee": 10,
  "distance": 5,
  "sub_total": 35,
  "discount": 0,
  "tax": 1.75,
  "tax_rate": 5,
  "fees": 2,
  "vendor_fees": [
    {
      "id": 1,
      "name": "Service fee",
      "value": 2,
      "percentage": false,
      "amount": 2
    }
  ],
  "total": 38.75,
  "coupon": null,
  "stops": [
    {
      "id": 10,
      "lat": 5.6037,
      "lng": -0.187
    },
    {
      "id": 11,
      "lat": 5.65,
      "lng": -0.12,
      "price": 25
    }
  ],
  "token": "encrypted-parcel-summary-token"
}
```

Location error response:

```json
{
  "message": "Vendor does not service pickup location"
}
```

Status code: `400`

## Place Parcel Order

```http
POST /api/orders
Authorization: Bearer {token}
Content-Type: application/json
Idempotency-Key: {unique-request-key}
```

Request body:

```json
{
  "type": "parcel",
  "vendor_id": 5,
  "package_type_id": 2,
  "payment_method_id": 2,
  "pickup_location_id": 10,
  "dropoff_location_id": 11,
  "recipient_name": "Recipient Name",
  "recipient_phone": "+233551234567",
  "payer": "sender",
  "weight": 2,
  "width": 20,
  "length": 30,
  "height": 10,
  "sub_total": 35,
  "discount": 0,
  "delivery_fee": 25,
  "tax": 1.75,
  "tax_rate": 5,
  "total": 38.75,
  "fees": [],
  "coupon_code": null,
  "pickup_date": "2026-06-07",
  "pickup_time": "14:30",
  "note": "Fragile item",
  "token": "encrypted-parcel-summary-token"
}
```

Multi-stop request body:

```json
{
  "type": "package",
  "vendor_id": 5,
  "package_type_id": 2,
  "payment_method_id": 2,
  "weight": 2,
  "sub_total": 35,
  "discount": 0,
  "delivery_fee": 25,
  "tax": 1.75,
  "total": 38.75,
  "stops": [
    {
      "id": 10,
      "price": 0,
      "name": "Sender",
      "phone": "+233501234567",
      "note": "Pickup"
    },
    {
      "id": 11,
      "price": 25,
      "name": "Recipient",
      "phone": "+233551234567",
      "note": "Dropoff"
    }
  ],
  "token": "encrypted-parcel-summary-token"
}
```

Successful response:

```json
{
  "message": "Order placed successfully. Relax while the vendor process your order",
  "link": "",
  "code": "1234567890",
  "token": "encrypted-payment-token"
}
```

Invalid summary response:

```json
{
  "message": "Invalid Order Summary. Please contact support",
  "errorCode": 1
}
```

Status code: `400`

## Verify Parcel Stop

```http
POST /api/package/order/stop/verify/{id}
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

Path parameters:

- `id`: order stop ID.

Request fields:

- `signature`: optional proof image/file.

Successful response:

```json
{
  "message": "Order stop verified",
  "order": {
    "id": 50,
    "code": "1234567890",
    "status": "enroute",
    "stops": [
      {
        "id": 1,
        "verified": true
      }
    ]
  }
}
```

Invalid stop response:

```json
{
  "message": "Invalid order stop"
}
```

Status code: `400`
