---
group: Catalog
title: Service API
order: 20
---

# Service API

Service endpoints are public browse endpoints. The route file exposes a full API resource, but the controller currently implements listing, detail, and duration lookup only.

## List Services

```http
GET /api/services
```

Query parameters:

- `keyword`: searches service name and description.
- `category_id`: limits services to one category.
- `is_open`: filters by open status.
- `vendor_type_id`: limits services to active vendors in the given module/vendor type.
- `vendor_id`: limits services to one vendor.
- `latitude` and `longitude`: limits services to vendors that match the delivery zone.
- `type`: optional service list mode.
- `direction`: sort direction. Defaults to `asc`.
- `page`: when present, returns a paginated response. When absent, returns an array.
- `exclude`: comma-separated relationships to exclude from eager loading.
- `exclude_attributes`: comma-separated model fillable attributes to exclude.
- `exclude_columns`: comma-separated database columns to exclude.

Supported `type` values:

- `discount`: services with `discount_price` greater than `0`, ordered by discount price descending.
- `best`: orders by service sales count using `direction`.

Example paginated request:

```http
GET /api/services?vendor_id=4&page=1
```

Example paginated response:

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 7,
      "name": "Home Cleaning",
      "description": "Deep home cleaning service",
      "price": 120,
      "discount_price": 100,
      "duration": "1 hour",
      "vendor_id": 4,
      "category_id": 2,
      "is_active": 1,
      "is_open": 1,
      "photo": "https://example.com/storage/7/service.jpg",
      "vendor": {
        "id": 4,
        "name": "Fresh Market",
        "is_open": 1
      }
    }
  ],
  "first_page_url": "https://example.com/api/services?page=1",
  "from": 1,
  "last_page": 2,
  "last_page_url": "https://example.com/api/services?page=2",
  "links": [],
  "next_page_url": "https://example.com/api/services?page=2",
  "path": "https://example.com/api/services",
  "per_page": 20,
  "prev_page_url": null,
  "to": 20,
  "total": 35
}
```

Example non-paginated request:

```http
GET /api/services?type=discount
```

Example non-paginated response:

```json
[
  {
    "id": 7,
    "name": "Home Cleaning",
    "description": "Deep home cleaning service",
    "price": 120,
    "discount_price": 100,
    "duration": "1 hour",
    "vendor_id": 4,
    "category_id": 2,
    "is_active": 1,
    "is_open": 1
  }
]
```

## Show Service

```http
GET /api/services/{id}
```

Example response:

```json
{
  "id": 7,
  "name": "Home Cleaning",
  "description": "Deep home cleaning service",
  "price": 120,
  "discount_price": 100,
  "duration": "1 hour",
  "vendor_id": 4,
  "category_id": 2,
  "is_active": 1,
  "is_open": 1,
  "photo": "https://example.com/storage/7/service.jpg"
}
```

If no service matches the ID, the endpoint returns `null`:

```json
null
```

## Service Durations

```http
GET /api/service/durations
```

Returns the possible enum values for the `duration` field.

Example response:

```json
[
  "30 minutes",
  "1 hour",
  "2 hours"
]
```
