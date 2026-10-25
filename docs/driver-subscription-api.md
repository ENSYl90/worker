---
group: Driver
title: Driver Subscription API
order: 10
---

# Driver Subscription API

Driver subscription endpoints are available to authenticated users with the `driver` role.

Base middleware:

- `auth:sanctum`
- `user.active.check`
- `role:driver`

## Data Model Summary

Driver plans are stored in `driver_subscriptions`.

Driver purchased subscriptions are stored in `driver_subscription_histories`.

Plan types:

- `time`: subscription is valid until `expires_at`
- `orders`: subscription is valid while `remaining_orders` is greater than `0`

Only one active driver subscription is kept at a time. When a driver buys a new subscription, existing active subscriptions for that driver are marked `cancelled`.

## Finance Setting

Driver subscription enforcement is controlled by:

```text
finance.enableDriverSubscription
```

Default: `false`

When enabled:

- drivers need an active subscription to go online
- drivers without an active subscription are excluded from server-side order assignment
- drivers without an active subscription cannot accept an order assignment
- driver commission/remittance earning calculation is skipped because drivers are subscription-based instead of commission-based

The app settings endpoint exposes this value in:

- `strings.enableDriverSubscription`
- `finance.enableDriverSubscription`

## List Available Driver Subscriptions

```http
GET /api/driver/subscriptions
Authorization: Bearer {token}
```

Returns active subscription plans ordered by amount.

Example response:

```json
[
  {
    "id": 1,
    "name": "30 Days",
    "type": "time",
    "days": 30,
    "order_limit": null,
    "amount": 20,
    "is_active": true
  },
  {
    "id": 2,
    "name": "100 Orders",
    "type": "orders",
    "days": null,
    "order_limit": 100,
    "amount": 50,
    "is_active": true
  }
]
```

## Subscribe With Wallet Balance

```http
POST /api/driver/subscriptions/subscribe
Authorization: Bearer {token}
Content-Type: application/json
```

Request body:

```json
{
  "driver_subscription_id": 2
}
```

Behavior:

- Validates that the plan exists and is active.
- Locks the driver wallet row during purchase.
- Fails if wallet balance is lower than the subscription amount.
- Debits the driver wallet.
- Creates a successful wallet transaction.
- Cancels previous active driver subscriptions.
- Creates the new successful driver subscription history.

Successful response:

```json
{
  "message": "Driver subscription successful",
  "subscription": {
    "id": 12,
    "driver_subscription_id": 2,
    "driver_id": 45,
    "wallet_transaction_id": 88,
    "code": "dsh_abc123...",
    "type": "orders",
    "status": "successful",
    "amount": 50,
    "order_limit": 100,
    "remaining_orders": 100,
    "completed_orders": 0,
    "starts_at": "2026-06-04 12:00:00",
    "expires_at": null,
    "subscription": {
      "id": 2,
      "name": "100 Orders",
      "type": "orders"
    }
  },
  "wallet": {
    "id": 6,
    "user_id": 45,
    "balance": 150
  }
}
```

Error response:

```json
{
  "message": "Wallet balance is less than subscription amount"
}
```

Status code: `400`

## Fetch Driver Subscription State

```http
GET /api/driver/subscriptions/state
Authorization: Bearer {token}
```

Returns the currently active subscription, plus the latest subscription record even if it is expired or cancelled.

Example response:

```json
{
  "has_active_subscription": true,
  "subscription": {
    "id": 12,
    "type": "orders",
    "status": "successful",
    "amount": 50,
    "order_limit": 100,
    "remaining_orders": 73,
    "completed_orders": 27,
    "starts_at": "2026-06-04 12:00:00",
    "expires_at": null,
    "subscription": {
      "id": 2,
      "name": "100 Orders",
      "type": "orders"
    }
  },
  "latest_subscription": {
    "id": 12,
    "type": "orders",
    "status": "successful"
  }
}
```

If no active subscription exists:

```json
{
  "has_active_subscription": false,
  "subscription": null,
  "latest_subscription": {
    "id": 10,
    "type": "orders",
    "status": "expired"
  }
}
```

## Fetch Driver Subscription History

```http
GET /api/driver/subscriptions/history
Authorization: Bearer {token}
```

Returns the authenticated driver's subscription history, newest first, with the related plan loaded. The response is paginated.

Example response:

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 12,
      "driver_subscription_id": 2,
      "driver_id": 45,
      "wallet_transaction_id": 88,
      "code": "dsh_abc123...",
      "type": "orders",
      "status": "successful",
      "amount": 50,
      "order_limit": 100,
      "remaining_orders": 73,
      "completed_orders": 27,
      "starts_at": "2026-06-04 12:00:00",
      "expires_at": null,
      "subscription": {
        "id": 2,
        "name": "100 Orders",
        "type": "orders"
      }
    }
  ],
  "per_page": 15,
  "total": 1
}
```

## Order Limit Handling

Order-based subscriptions are consumed by `OrderStatusObserver`.

When an order with a `driver_id` receives one of these statuses:

- `delivered`
- `completed`
- `successful`

the observer finds that driver's active `orders` subscription and:

- decrements `remaining_orders` by `1`
- increments `completed_orders` by `1`
- marks the subscription `expired` when `remaining_orders` reaches `0`

Time-based subscriptions are checked by `expires_at` through the active subscription scope.

## Admin Report

Admin users with `manage-driver-subscriptions` can view subscribed drivers at:

```http
GET /reports/driver-subscriptions
```

The report shows:

- driver
- phone
- plan
- plan type
- started date
- expiry date
- days left
- orders left
- completed orders
- status
