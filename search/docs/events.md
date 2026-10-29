# Kafka events

Consumer group: configured via environment (see `internal/config`). Worker: `cmd/consumer`.

## Consumed topics

| Topic | Action |
| --- | --- |
| `product.created` | Index product document |
| `product.updated` | Update document |
| `product.deleted` | Remove document |
| `product.published` | Update visibility |
| `product.unpublished` | Update visibility |
| `category.created` | Index category metadata |
| `category.updated` | Update category |
| `category.deleted` | Remove category facets |
| `stock.changed` | Update availability fields |

Documents are stored in the OpenSearch `products` index.

## Published topics

### `search.performed`

Emitted after each search request handled by the API.

```json
{
  "query": "desk",
  "filters": { "store_id": 1, "category_id": 2 },
  "result_count": 15,
  "timestamp": "2026-10-29T12:00:00Z"
}
```

| Field | Type | Description |
| --- | --- | --- |
| `query` | string | Search text |
| `filters` | object | Applied filters |
| `result_count` | integer | Hits returned |
| `timestamp` | string (RFC3339) | UTC time |

## Message format

UTF-8 JSON record values on consume and publish.
