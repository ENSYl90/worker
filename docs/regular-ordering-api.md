---
group: Ordering
title: Regular Product
order: 10
---

# Regular Product Ordering API

Regular product ordering covers product-based vendor orders, including single-vendor and multiple-vendor checkout. All endpoints in this document require an authenticated Sanctum user.

Base middleware:

- `auth:sanctum`
- `user.active.check`

Order placement also uses:

- `idempotency`
- `throttle.order.api`

## Calculate Delivery Fee

```http
GET /api/general/order/delivery/fee/summary?vendor_id={vendor_id}&delivery_address_id={address_id}
Authorization: Bearer {token}
```

Query parameters:

- `vendor_id`: vendor ID.
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

## Calculate Product Order Summary

```http
POST /api/general/order/summary
Authorization: Bearer {token}
Content-Type: application/json
```

Request body:

```json
{
  "vendor_id": 4,
  "delivery_address_id": 9,
  "pickup": 0,
  "delievryAddressOutOfRange": 0,
  "coupon_code": "SAVE10",
  "tip": 5,
  "products": [
    {
      "product": {
        "id": 12
      },
      "selected_qty": 2,
      "options_ids": [3, 4],
      "options": [],
      "options_flatten": "Large, Extra Cheese"
    }
  ]
}
```

Successful response:

```json
{
  "vendor_id": 4,
  "delivery_fee": 15,
  "delivery_discount": 0,
  "sub_total": 80,
  "subtotal": 80,
  "discount": 8,
  "tax": 4,
  "tax_rate": 5,
  "total": 91,
  "total_with_tip": 96,
  "tip": 5,
  "fees": [
    {
      "name": "Service fee",
      "amount": 2,
      "value": 2,
      "id": 1
    }
  ],
  "total_fee": 2,
  "products": [
    {
      "product": {
        "id": 12,
        "name": "Chicken Burger"
      },
      "id": 12,
      "selected_qty": 2,
      "price": 80,
      "sell_price": 35,
      "options": [],
      "options_ids": [3, 4],
      "options_flatten": "Large, Extra Cheese",
      "options_price": 5,
      "product_price": 35
    }
  ],
  "token": "encrypted-order-summary-token"
}
```

The returned `token` should be sent when placing the order. The server decrypts it to verify the final amounts and product data.

## Place Product Order

```http
POST /api/orders
Authorization: Bearer {token}
Content-Type: application/json
Idempotency-Key: {unique-request-key}
```

Request body:

```json
{
  "vendor_id": 4,
  "delivery_address_id": 9,
  "payment_method_id": 2,
  "sub_total": 80,
  "discount": 8,
  "delivery_fee": 15,
  "tax": 4,
  "total": 91,
  "tip": 5,
  "fees": [],
  "coupon_code": "SAVE10",
  "pickup": false,
  "pickup_date": "2026-06-07",
  "pickup_time": "14:30",
  "note": "Call on arrival",
  "token": "encrypted-order-summary-token"
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

Error response:

```json
{
  "message": "Order Data is tampered. We are unable to process your order",
  "errorCode": 1
}
```

Status code: `400`

## Place Multiple Vendor Order

```http
POST /api/orders
Authorization: Bearer {token}
Content-Type: application/json
Idempotency-Key: {unique-request-key}
```

Request body:

```json
{
  "data": [
    {
      "vendor_id": 4,
      "token": "encrypted-summary-token-for-vendor-4"
    },
    {
      "vendor_id": 8,
      "token": "encrypted-summary-token-for-vendor-8"
    }
  ],
  "delivery_address_id": 9,
  "payment_method_id": 2,
  "sub_total": 140,
  "discount": 10,
  "delivery_fee": 25,
  "tax": 7,
  "total": 162,
  "coupon_code": "SAVE10",
  "pickup": false
}
```

Successful response:

```json
{
  "message": "Order placed successfully. Relax while the vendor process your order",
  "link": ""
}
```

## List Orders

```http
GET /api/orders
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
- `vendor_type_slug`: filter by vendor type slug.

Example response:

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 50,
      "code": "1234567890",
      "vendor_id": 4,
      "user_id": 3,
      "driver_id": null,
      "delivery_address_id": 9,
      "payment_method_id": 2,
      "sub_total": 80,
      "discount": 8,
      "delivery_fee": 15,
      "tax": 4,
      "total": 91,
      "payment_status": "pending",
      "status": "pending",
      "products": [],
      "vendor": {
        "id": 4,
        "name": "Fresh Market"
      },
      "payment_method": {
        "id": 2,
        "name": "Cash"
      }
    }
  ],
  "links": {},
  "meta": {}
}
```

## Show Order

```http
GET /api/orders/{id}
Authorization: Bearer {token}
```

Example response:

```json
{
  "id": 50,
  "code": "1234567890",
  "status": "pending",
  "payment_status": "pending",
  "total": 91,
  "products": [],
  "stops": [],
  "vendor": {
    "id": 4,
    "name": "Fresh Market"
  }
}
```

## Track Order By Code

```http
POST /api/track/order
Authorization: Bearer {token}
Content-Type: application/json
```

Request body:

```json
{
  "code": "1234567890",
  "vendor_type_id": 1
}
```

Successful response:

```json
{
  "id": 50,
  "code": "1234567890",
  "status": "pending",
  "payment_status": "pending",
  "total": 91
}
```

Invalid tracking code response:

```json
{
  "message": "Invalid tracking code"
}
```

Status code: `400`

## Update Order

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
    "id": 50,
    "code": "1234567890",
    "status": "pending",
    "payment_status": "pending"
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
