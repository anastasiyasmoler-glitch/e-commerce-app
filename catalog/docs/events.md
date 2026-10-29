# Kafka events

Consumer group: `catalog-service-group`. Outbox relay: `php artisan kafka:publish-outbox` (service `outbox`).

## Published topics

Catalog and inventory facts (JSON payloads include `store_id`, product/category identifiers, and attributes as defined in outbox writers):

| Topic | Description |
| --- | --- |
| `product.created` | New product |
| `product.updated` | Product changed |
| `product.deleted` | Product removed |
| `product.published` | Product visible on storefront |
| `product.unpublished` | Product hidden |
| `category.created` | Category added |
| `category.updated` | Category changed |
| `category.deleted` | Category removed |
| `stock.changed` | Available quantity changed |
| `stock.reserved` | Stock reserved for an order |
| `stock.reservation_failed` | Reservation could not be fulfilled |

Product payloads include `is_published` and `in_stock` where applicable.

## Consumed topics

| Topic | Action |
| --- | --- |
| `order.created` | Reserve stock for line items |
| `stock.release_requested` | Release reserved stock (compensation) |

### `order.created` (consume)

```json
{
  "order_id": 1,
  "items": [
    { "product_id": 10, "store_id": 1, "quantity": 2 }
  ]
}
```

## Message format

UTF-8 JSON record values. Topic name identifies the event type. Publication is transactional with database changes via the outbox table.
