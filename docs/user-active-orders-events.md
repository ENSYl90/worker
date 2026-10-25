---
group: Websocket
title: User Active Orders Events
order: 12
---

# User Active Orders Events

The active orders event broadcasts the current number of active orders for a user whenever one of that user's order statuses changes.

Use this event when a client needs to update an active-order badge, counter, dashboard value, or any UI that depends on the number of in-progress orders for the authenticated user.

## Channel Format

```text
users.active-orders.{userId}
```

Example:

```text
users.active-orders.15
```

This is a private channel. A user can only listen to their own active-order channel.

## Event Name

```text
ActiveOrdersUpdated
```

When listening with Laravel Echo, prefix custom broadcast names with a dot:

```js
.ActiveOrdersUpdated
```

## Payload

The event payload only contains the active order count.

Example:

```json
{
  "active": 3
}
```

Fields:

| Field | Type | Description |
| --- | --- | --- |
| `active` | `number` | Current number of active orders for the authenticated user. |

## Active Order Definition

An order is counted as active when its current status is not one of these final or inactive statuses:

- `failed`
- `cancelled`
- `delivered`
- `completed`
- `successful`

All other current statuses are counted as active.

## Listen With Laravel Echo

```js
const userId = authUser.id;

Echo.private(`users.active-orders.${userId}`)
  .listen('.ActiveOrdersUpdated', (event) => {
    updateActiveOrdersBadge(event.active);
  });
```

## Listen With Pusher JS Or Reverb Protocol

Private channels use the `private-` prefix when subscribing directly with Pusher JS or the Reverb protocol.

```js
const channel = pusher.subscribe(`private-users.active-orders.${userId}`);

channel.bind('ActiveOrdersUpdated', (event) => {
  updateActiveOrdersBadge(event.active);
});
```

## Emit Current Count With API

Clients can request the backend to emit the current active-order count for the authenticated user.

```http
POST /api/orders/active-count/emit
Authorization: Bearer {token}
```

Successful response:

```json
{
  "message": "Active orders event emitted successfully.",
  "active": 3
}
```

The endpoint emits the same `ActiveOrdersUpdated` event to:

```text
users.active-orders.{authenticatedUserId}
```

Use this endpoint when a client reconnects, opens an order screen, or needs to force-refresh the active-order counter without waiting for the next order status update.

## Backend Flow

The backend listens for order status updates through:

```text
Spatie\ModelStatus\Events\StatusUpdated
```

When an order status actually changes, `App\Listeners\OrderStatusEventSubscriber` dispatches:

```text
App\Jobs\UserActiveOrdersUpdatedJob
```

The job:

1. Counts active orders for the user.
2. Dispatches `App\Events\UserActiveOrdersUpdatedEvent`.
3. Broadcasts the result to `users.active-orders.{userId}`.

The API endpoint `POST /api/orders/active-count/emit` calls the same job logic directly, so manual emits and status-change emits use the same active-order rules.

## Backend Reference

Relevant files:

- `app/Listeners/OrderStatusEventSubscriber.php`
- `app/Jobs/UserActiveOrdersUpdatedJob.php`
- `app/Events/UserActiveOrdersUpdatedEvent.php`
- `app/Http/Controllers/API/UserActiveOrdersController.php`
- `routes/channels.php`
