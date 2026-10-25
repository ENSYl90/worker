---
group: Catalog
title: Vendor API
order: 30
---

# Vendor API

Vendor browse endpoints are public. Authenticated manager-only vendor detail endpoints are documented where they are routed inside the authenticated API group.

## List Vendors

```http
GET /api/vendors
```

Query parameters:

- `latitude` and `longitude`: limits vendors by delivery zone or delivery range.
- `vendor_type_id`: limits vendors to one module/vendor type.
- `package_type_id`: limits vendors to parcel/package vendors with pricing for the package type.
- `type`: optional vendor list mode.

Supported `type` values:

- `top`: vendors with highest sales count first.
- `featured`: featured vendors only.
- `you`: random vendor ordering.
- `rated`: highest average rating first.
- `fresh`: latest vendors first.
- `package`: vendors whose vendor type slug is `parcel`.

Behavior:

- Returns active vendors only.
- Orders open vendors before closed vendors.
- Uses `vendorsHomePageListCount` as page size when `type` is present.
- Uses the controller default page size when `type` is absent.

Example request:

```http
GET /api/vendors?type=featured&vendor_type_id=1&page=1
```

Example response:

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 4,
      "name": "Fresh Market",
      "email": "store@example.com",
      "phone": "+233551234567",
      "vendor_type_id": 1,
      "address": "Accra",
      "latitude": 5.6037,
      "longitude": -0.187,
      "is_active": 1,
      "is_open": 1,
      "featured": 1,
      "logo": "https://example.com/storage/4/logo.jpg",
      "feature_image": "https://example.com/storage/4/feature.jpg"
    }
  ],
  "first_page_url": "https://example.com/api/vendors?page=1",
  "from": 1,
  "last_page": 2,
  "last_page_url": "https://example.com/api/vendors?page=2",
  "links": [],
  "next_page_url": "https://example.com/api/vendors?page=2",
  "path": "https://example.com/api/vendors",
  "per_page": 20,
  "prev_page_url": null,
  "to": 20,
  "total": 32
}
```

## Show Vendor

```http
GET /api/vendors/{id}
```

Query parameters:

- `type=small`: loads active menus and categories with subcategories.
- `type=brief`: returns the vendor without the large nested menu/category payload.
- No `type`: returns vendor with menus, active menu products, categories, subcategories, and active subcategory products.

Behavior:

- Adds an `All` subcategory fallback for categories that do not have subcategories.

Example request:

```http
GET /api/vendors/4?type=small
```

Example response:

```json
{
  "id": 4,
  "name": "Fresh Market",
  "email": "store@example.com",
  "phone": "+233551234567",
  "vendor_type_id": 1,
  "address": "Accra",
  "is_active": 1,
  "is_open": 1,
  "menus": [
    {
      "id": 3,
      "name": "Lunch",
      "vendor_id": 4,
      "is_active": 1
    }
  ],
  "categories": [
    {
      "id": 2,
      "name": "Meals",
      "sub_categories": [
        {
          "id": 0,
          "name": "All",
          "products_count": 12,
          "services_count": 0
        }
      ]
    }
  ]
}
```

Vendor-not-found response:

```json
{
  "message": "No query results for model [App\\Models\\Vendor] 999"
}
```

Status code: `400`

## List Vendor Reviews

```http
GET /api/vendor/reviews?vendor_id={vendor_id}
```

Query parameters:

- `vendor_id`: vendor ID.

Example response:

```json
{
  "data": [
    {
      "id": 10,
      "rating": 5,
      "review": "Fast delivery",
      "user_id": 3,
      "vendor_id": 4,
      "driver_id": null,
      "order_id": 44,
      "created_at": "2026-06-04T12:00:00.000000Z",
      "updated_at": "2026-06-04T12:00:00.000000Z",
      "formatted_date": "04 Jun 2026",
      "formatted_updated_date": "04 Jun 2026",
      "photo": null,
      "user": {
        "id": 3,
        "name": "Jane Customer",
        "email": "jane@example.com",
        "phone": "+233501234567"
      },
      "vendor": {
        "id": 4,
        "name": "Fresh Market"
      }
    }
  ],
  "links": {},
  "meta": {}
}
```

## Get Authenticated Vendor Details

```http
GET /api/vendor/{id}/details
Authorization: Bearer {token}
```

Middleware:

- `auth:sanctum`
- `user.active.check`

Behavior:

- The authenticated user's `vendor_id` must match `{id}`.
- Returns vendor earnings, total sales count, and a seven-day order chart.

Successful response:

```json
{
  "vendor": {
    "id": 4,
    "name": "Fresh Market",
    "sales_count": 125,
    "earning": {
      "id": 1,
      "vendor_id": 4,
      "amount": 4500.5
    },
    "menus": [
      {
        "id": 3,
        "name": "Lunch"
      }
    ]
  },
  "total_earnig": 4500.5,
  "total_orders": 125,
  "report": [
    {
      "date": "Mon",
      "value": 10
    },
    {
      "date": "Tue",
      "value": 12
    }
  ]
}
```

Unauthorized response:

```json
{
  "message": "Unauthorised Access"
}
```

Status code: `400`
