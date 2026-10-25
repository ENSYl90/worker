---
group: Catalog
title: Product API
order: 10
---

# Product API

Product endpoints are public unless noted otherwise. Authenticated users may receive more personalized results for some filters.

## List Products

```http
GET /api/products
```

Query parameters:

- `keyword`: searches product name, description, and barcode.
- `vendor_type_id`: limits products to active vendors in the given module/vendor type.
- `vendor_id`: limits products to one vendor.
- `category_id`: limits products to one category.
- `category_ids`: JSON array or array query value of category IDs.
- `sub_category_id`: limits products to one subcategory.
- `sub_category_ids`: JSON array or array query value of subcategory IDs.
- `menu_id`: limits products to one menu.
- `menu_ids`: JSON array or array query value of menu IDs.
- `is_open`: filters by product open status.
- `latitude` and `longitude`: when location filtering is enabled, limits products by delivery zone.
- `type`: optional product list mode.

Supported `type` values:

- `best`: orders by highest sales count.
- `you`: for authenticated users, orders by purchased products; otherwise returns random products.
- `flash`: products with a `discount_price` greater than `0`.
- `new`: latest products first.
- `featured`: featured products only.
- `vendor`: manager/vendor mode. Includes categories, subcategories, and menus, and limits manager users to their assigned vendor.

Example request:

```http
GET /api/products?vendor_id=4&type=featured&page=1
```

Example response:

```json
{
  "current_page": 1,
  "data": [
    {
      "id": 12,
      "name": "Chicken Burger",
      "description": "Grilled chicken burger",
      "price": 35,
      "discount_price": 30,
      "vendor_id": 4,
      "is_active": 1,
      "is_open": 1,
      "featured": 1,
      "photo": "https://example.com/storage/12/product.jpg",
      "vendor": {
        "id": 4,
        "name": "Fresh Market",
        "is_open": 1
      }
    }
  ],
  "first_page_url": "https://example.com/api/products?page=1",
  "from": 1,
  "last_page": 3,
  "last_page_url": "https://example.com/api/products?page=3",
  "links": [],
  "next_page_url": "https://example.com/api/products?page=2",
  "path": "https://example.com/api/products",
  "per_page": 30,
  "prev_page_url": null,
  "to": 30,
  "total": 75
}
```

## Show Product

```http
GET /api/products/{id}
```

Behavior:

- Fetches the product by ID.
- Adds `option_groups` containing active option groups and options connected to the product.

Example response:

```json
{
  "id": 12,
  "name": "Chicken Burger",
  "description": "Grilled chicken burger",
  "price": 35,
  "discount_price": 30,
  "vendor_id": 4,
  "is_active": 1,
  "photo": "https://example.com/storage/12/product.jpg",
  "option_groups": [
    {
      "id": 2,
      "name": "Add-ons",
      "is_active": 1,
      "options": [
        {
          "id": 9,
          "name": "Extra Cheese",
          "price": 5,
          "option_group_id": 2
        }
      ]
    }
  ]
}
```

Product-not-found response:

```json
{
  "message": "No query results for model [App\\Models\\Product] 999"
}
```

Status code: `400`

## Product Review Summary

```http
GET /api/product/review/summary?id={product_id}
```

Query parameters:

- `id`: product ID.

Example response:

```json
{
  "rating_summary": {
    "average": 4.5,
    "total": 20,
    "5": 12,
    "4": 5,
    "3": 2,
    "2": 1,
    "1": 0
  },
  "latest_reviews": [
    {
      "id": 8,
      "product_id": 12,
      "user_id": 3,
      "rating": 5,
      "review": "Great product",
      "user": {
        "id": 3,
        "name": "Jane Customer"
      }
    }
  ]
}
```

## List Product Reviews

```http
GET /api/product/reviews?product_id={product_id}
```

Query parameters:

- `product_id`: product ID.

Example response:

```json
{
  "data": [
    {
      "id": 8,
      "product_id": 12,
      "order_id": 44,
      "user_id": 3,
      "rating": 5,
      "review": "Great product",
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
      }
    }
  ],
  "links": {},
  "meta": {}
}
```

## Create Product Review

```http
POST /api/product/reviews
Authorization: Bearer {token}
Content-Type: application/json
```

Request body:

```json
{
  "product_id": 12,
  "order_id": 44,
  "rating": 5,
  "review": "Great product"
}
```

Behavior:

- Requires authentication through the global authenticated API group.
- Prevents duplicate reviews for the same `user_id`, `order_id`, and `product_id`.

Successful response:

```json
{
  "message": "Product review successful",
  "review": {
    "id": 8,
    "product_id": 12,
    "order_id": 44,
    "user_id": 3,
    "rating": 5,
    "review": "Great product"
  }
}
```

Duplicate review response:

```json
{
  "message": "Product already reviewed"
}
```

Status code: `400`

## Frequently Bought Together

```http
GET /api/product/frequent?id={product_id}
```

Query parameters:

- `id`: product ID used to find products bought in the same orders.

Example response:

```json
{
  "products": [
    {
      "id": 15,
      "name": "Soft Drink",
      "price": 10,
      "available_qty": null,
      "vendor_id": 4
    }
  ]
}
```
