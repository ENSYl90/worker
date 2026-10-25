---
group: Ordering
title: Pharmacy Prescription Upload
order: 50
---

# Pharmacy Prescription Upload API

Prescription upload is handled through the regular order placement endpoint. It creates an order in `review` payment status and attaches one or more uploaded prescription images/files to the order.

This flow is useful when a pharmacy needs to review the prescription before confirming products and final pricing.

Base middleware:

- `auth:sanctum`
- `user.active.check`

Order placement also uses:

- `idempotency`
- `throttle.order.api`

## Upload Prescription Order

```http
POST /api/orders
Authorization: Bearer {token}
Content-Type: multipart/form-data
Idempotency-Key: {unique-request-key}
```

Request fields:

```json
{
  "type": "prescription",
  "vendor_id": 4,
  "delivery_address_id": 9,
  "sub_total": 0,
  "discount": 0,
  "delivery_fee": 0,
  "tax": 0,
  "tax_rate": 0,
  "total": 0,
  "tip": 0,
  "fees": [],
  "pickup_date": "2026-06-07",
  "pickup_time": "14:30",
  "note": "Please review this prescription",
  "photo": "prescription image file"
}
```

Multiple-file request fields:

```json
{
  "type": "pharmacy",
  "vendor_id": 4,
  "delivery_address_id": 9,
  "sub_total": 0,
  "discount": 0,
  "delivery_fee": 0,
  "tax": 0,
  "total": 0,
  "photos[]": "prescription image files"
}
```

Accepted upload fields:

- `photo`: single prescription file.
- `photos[]`: multiple prescription files.

Behavior:

- `type=prescription` always uses the prescription flow.
- `type=pharmacy` uses the prescription flow when `photo` or `photos[]` is present.
- Sets `payment_status` to `review`.
- Saves uploaded files to the order's default media collection.
- Returns a payment token, but `link` is empty because the order is awaiting review.

Successful response:

```json
{
  "message": "Order placed successfully. Relax while the vendor process your order",
  "link": "",
  "code": "1234567890",
  "token": "encrypted-payment-token"
}
```

## Prescription File Limits

Clients can read prescription file limits from the app settings endpoint:

```http
GET /api/settings
```

Relevant response section:

```json
{
  "strings": {
    "file_limit": {
      "prescription": "2048"
    }
  }
}
```

## Review Order Status

Use the normal order detail endpoint to fetch the prescription order after upload:

```http
GET /api/orders/{id}
Authorization: Bearer {token}
```

Example response:

```json
{
  "id": 50,
  "code": "1234567890",
  "vendor_id": 4,
  "delivery_address_id": 9,
  "payment_status": "review",
  "status": "pending",
  "photo": "https://example.com/storage/50/prescription.jpg",
  "attachments": [
    "https://example.com/storage/50/prescription-1.jpg",
    "https://example.com/storage/50/prescription-2.jpg"
  ]
}
```

## Common Errors

Missing payment method on non-prescription orders:

```json
{
  "message": "Payment Method is required"
}
```

Status code: `400`

Validation error:

```json
{
  "message": "The vendor id field is required when data is not present."
}
```

Status code: `400`
