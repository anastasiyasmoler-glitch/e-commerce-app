# Kafka events

Consumer group: `order-service-group`. Outbox relay publishes order facts after DB commits.

## Published topics

| Topic | Description |
| --- | --- |
| `order.created` | Order placed; triggers catalog stock reservation |
| `order.paid` | Payment succeeded |
| `order.confirmed` | Order confirmed after payment |
| `order.cancelled` | Order cancelled |
| `order.payment_failed` | Payment failed |
| `order.shipped` | Order shipped (staff) |
| `order.completed` | Order completed (staff) |
| `stock.release_requested` | Compensation — release reserved stock |

### `order.created` (publish)

```json
{
  "order_id": 1,
  "items": [
    { "product_id": 10, "store_id": 1, "quantity": 2 }
  ]
}
```

Additional order events include identifiers and amounts as produced by order services (see outbox writers and OpenAPI).

## Consumed topics

| Topic | Action |
| --- | --- |
| `product.created` | Update local product snapshot |
| `product.updated` | Update local product snapshot |
| `stock.reserved` | Continue payment flow |
| `stock.reservation_failed` | Cancel order |

## Message format

UTF-8 JSON values. Checkout uses local snapshots; stock validation happens asynchronously via Catalog.
