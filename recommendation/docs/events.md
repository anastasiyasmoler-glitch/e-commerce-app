# Kafka events

Consumer group: `recommendation-service-group`. Command: `php artisan kafka:consume-catalog-index`.

## Consumed topics

| Topic | Action |
| --- | --- |
| `product.created` | Upsert projection; embed and index in Qdrant |
| `product.updated` | Upsert projection; re-embed |
| `product.deleted` | Remove projection and vector |
| `product.published` | Update publication metadata |
| `product.unpublished` | Update publication metadata |
| `stock.changed` | Update stock flags in projection |

Payload fields align with Catalog Kafka contract (`product_id`, `store_id`, `name`, `description`, `category_id`, `attributes`, `price`, `is_published`, `in_stock`, etc.).

## Published topics

Recommendation does not publish Kafka events.

## Message format

UTF-8 JSON values. Chat reads only the local projection table; it does not call Catalog over HTTP at request time.
