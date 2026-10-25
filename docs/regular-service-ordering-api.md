---
group: Ordering
title: Regular Service
order: 20
---

# Regular Service Ordering API

Regular service ordering covers booking services from service vendors. All endpoints in this document require an authenticated Sanctum user.

Base middleware:

- `auth:sanctum`
- `user.active.check`

Order placement also uses:

- `idempotency`
- `throttle.order.api`

## Calculate Service Delivery Fee

```http
GET /api/general/order/delivery/fee/summary?vendor_id={vendor_id}&delivery_address_id={address_id}
Authorization: Bearer {token}
```

Query parameters:

- `vendor_id`: service vendor ID.
- `delivery_address_id`: saved delivery address ID. Use `"null"` or omit when using `latlng`.
- `latlng`: comma-separated latitude and longitude, for example `5.6037,-0.1870`.

Behavior:

- Uses vendor delivery-zone fees first when available.
- Falls back to Google distance when `enableGoogleDistance` is enabled.
- Otherwise uses linear distance and vendor delivery fee settings.

Example response:

```json
{
  "delivery_fee": 15
}
```

## Calculate Service Order Summary

```http
POST /api/service/order/summary
Authorization: Bearer {token}
Content-Type: application/json
```

Request body:

```json
{
  "vendor_id": 4,
  "service_id": 7,
  "delivery_address_id": 9,
  "qty": 2,
  "options_ids": [5, 6],
  "coupon_code": "SAVE10",
  "tip": 5
}
```

Successful response:

```json
{
  "vendor_id": 4,
  "sub_total": 130,
  "subtotal": 130,
  "discount": 10,
  "delivery_fee": 15,
  "delivery_discount": null,
  "tax": 6.5,
  "tax_rate": 5,
  "total": 143.5,
  "total_with_tip": 148.5,
  "tip": 5,
  "fees": [],
  "total_fee": 0,
  "service": {
    "id": 7,
    "price": 70,
    "sell_price": 60,
    "options": [],
    "options_ids": [5, 6],
    "options_flatten": "Window cleaning, Supplies",
    "options_price": 5,
    "service_price": 5,
    "qty": 2
  },
  "token": "encrypted-service-summary-token"
}
```

The returned `token` should be sent when placing the service order. The server decrypts it to verify final amounts and selected service data.

## Place Service Order

```http
POST /api/orders
Authorization: Bearer {token}
Content-Type: application/json
Idempotency-Key: {unique-request-key}
```

Request body:

```json
{
  "type": "service",
  "vendor_id": 4,
  "service_id": 7,
  "delivery_address_id": 9,
  "payment_method_id": 2,
  "sub_total": 130,
  "discount": 10,
  "delivery_fee": 15,
  "tax": 6.5,
  "total": 143.5,
  "pickup_date": "2026-06-07",
  "pickup_time": "14:30",
  "note": "Bring cleaning supplies",
  "token": "encrypted-service-summary-token",
  "options_ids": [5, 6],
  "options_flatten": "Window cleaning, Supplies"
}
```

Required fields:

- `type`: must be `service`.
- `vendor_id`: service vendor ID.
- `service_id`: selected service ID.
- `payment_method_id`: selected payment method.
- `sub_total`, `discount`, `delivery_fee`, `tax`, `total`: totals from the summary response.
- `token`: encrypted token from `POST /api/service/order/summary`.

Optional fields:

- `delivery_address_id`
- `pickup_date`, `pickup_time`
- `note`
- `fees`
- `coupon_code`
- `options_ids`
- `options_flatten`

Successful response:

```json
{
  "message": "Order placed successfully. Relax while the vendor process your order",
  "link": "",
  "code": "1234567890",
  "token": "encrypted-payment-token"
}
```

Tampered summary response:

```json
{
  "message": "The payload is invalid.",
  "errorCode": 0
}
```

Status code: `400`

## List Service Orders

```http
GET /api/orders?vendor_type_slug=service
Authorization: Bearer {token}
```

Query parameters:

- `page`: when present, returns a paginated response; otherwise returns an array.
- `status`: filters by current order status.
- `type=history`: returns failed, cancelled, and delivered orders.
- `type=assigned`: returns active assigned orders.
- `vendor_id`: filter by vendor.
- `driver_id`: filter by driver.
- `vendor_type_id`: filter by vendor type ID.
- `vendor_type_slug=service`: filter to service orders.

Example response:

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 51,
      "code": "1234567890",
      "vendor_id": 4,
      "user_id": 3,
      "delivery_address_id": 9,
      "payment_method_id": 2,
      "sub_total": 130,
      "discount": 10,
      "delivery_fee": 15,
      "tax": 6.5,
      "total": 143.5,
      "payment_status": "pending",
      "status": "pending",
      "order_service": {
        "id": 5,
        "order_id": 51,
        "service_id": 7,
        "hours": 2,
        "price": 60,
        "options": "Window cleaning, Supplies",
        "options_ids": "5,6"
      },
      "vendor": {
        "id": 4,
        "name": "Cleaning Pros"
      }
    }
  ],
  "links": {},
  "meta": {}
}
```

## Show Service Order

```http
GET /api/orders/{id}
Authorization: Bearer {token}
```

Example response:

```json
{
  "id": 51,
  "code": "1234567890",
  "status": "pending",
  "payment_status": "pending",
  "total": 143.5,
  "order_service": {
    "service_id": 7,
    "hours": 2,
    "price": 60,
    "options": "Window cleaning, Supplies",
    "options_ids": "5,6"
  },
  "vendor": {
    "id": 4,
    "name": "Cleaning Pros"
  }
}
```

## Track Service Order By Code

```http
POST /api/track/order
Authorization: Bearer {token}
Content-Type: application/json
```

Request body:

```json
{
  "code": "1234567890",
  "vendor_type_id": 2
}
```

Successful response:

```json
{
  "id": 51,
  "code": "1234567890",
  "status": "pending",
  "payment_status": "pending",
  "total": 143.5,
  "order_service": {
    "service_id": 7,
    "hours": 2
  }
}
```

Invalid tracking code response:

```json
{
  "message": "Invalid tracking code"
}
```

Status code: `400`

## Update Service Order

```http
PUT /api/orders/{id}
Authorization: Bearer {token}
Content-Type: multipart/form-data
```

Common fields:

- `status`: new order status, for example `cancelled`, `preparing`, `ready`, `enroute`, or `delivered`.
- `payment_status`: payment status update.
- `payment_method_id`: payment method used when finalizing payment.
- `driver_id`: assigns a driver when allowed.
- `signature`: optional proof file.
- `proof_type`: media collection for proof. Defaults to `signature`.

Successful response:

```json
{
  "message": "Order placed pending",
  "order": {
    "id": 51,
    "code": "1234567890",
    "status": "pending",
    "payment_status": "pending",
    "order_service": {
      "service_id": 7,
      "hours": 2
    }
  }
}
```

Error response:

```json
{
  "message": "Order can't be cancelled."
}
```

Status code: `400`
