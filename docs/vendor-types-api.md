---
group: Catalog
title: Vendor Types API
order: 5
---

# Vendor Types API

Vendor type endpoints return the active modules/business types available in the app, such as food, grocery, service, parcel, taxi, booking, or other configured modules.

These endpoints are public and do not require authentication.

## List Vendor Types

```http
GET /api/vendor/types
```

Query parameters:

- `latitude`: optional latitude used to filter vendor types by delivery zone.
- `longitude`: optional longitude used to filter vendor types by delivery zone.

Behavior:

- Without coordinates, returns active vendor types ordered by `in_order`.
- With `latitude` and `longitude`, checks active delivery zones containing the coordinate.
- When coordinates are sent, returns active vendor types linked to matching delivery zones, plus active vendor types that do not have delivery-zone restrictions.

Example request:

```http
GET /api/vendor/types?latitude=5.6037&longitude=-0.187
```

Example response:

```json
[
  {
    "id": 1,
    "name": "Food",
    "description": "Restaurants and food delivery",
    "slug": "food",
    "is_active": 1,
    "color": "#22c55e",
    "formatted_date": "07 Jun 2026",
    "logo": "https://example.com/storage/1/logo.png",
    "website_header": "https://example.com/storage/1/header.png",
    "has_banners": 1
  },
  {
    "id": 2,
    "name": "Service",
    "description": "Book services nearby",
    "slug": "service",
    "is_active": 1,
    "color": "#0ea5e9",
    "formatted_date": "07 Jun 2026",
    "logo": "https://example.com/images/default.png",
    "website_header": "https://example.com/images/default.png",
    "has_banners": 0
  }
]
```

## Show Vendor Type

```http
GET /api/vendor/types/{id}
```

Path parameters:

- `id`: vendor type ID.

Example response:

```json
{
  "id": 1,
  "name": "Food",
  "description": "Restaurants and food delivery",
  "slug": "food",
  "is_active": 1,
  "color": "#22c55e",
  "formatted_date": "07 Jun 2026",
  "logo": "https://example.com/storage/1/logo.png",
  "website_header": "https://example.com/storage/1/header.png",
  "has_banners": 1
}
```

If no vendor type matches the ID, the endpoint returns `null`:

```json
null
```

## Response Fields

- `id`: vendor type ID.
- `name`: translated vendor type name when translations are configured.
- `description`: translated vendor type description when translations are configured.
- `slug`: machine-readable module slug, for example `food`, `service`, `parcel`, `taxi`, or `booking`.
- `is_active`: `1` for active, `0` for inactive.
- `color`: configured module color.
- `formatted_date`: formatted creation date from the base model accessor.
- `logo`: first media URL in the `logo` collection, or the default image fallback.
- `website_header`: first media URL in the `website_header` collection, or the default image fallback.
- `has_banners`: `1` when at least one banner exists for vendors under this type, otherwise `0`.

## Notes

- The route file declares a full API resource, but the controller currently implements only list and show behavior.
- Vendor types are used by product, service, vendor, parcel, taxi, and settings flows to decide which modules to show in the client.
- The same data also appears inside the app settings response as `strings.enabledVendorType`, but `/api/vendor/types` is the dedicated endpoint for listing all active modules.
