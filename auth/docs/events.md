# Kafka events

Auth publishes domain events to Kafka. Configuration: `config/kafka.php`, `KAFKA_BROKERS`, `KAFKA_USER_REGISTERED_TOPIC`.

## Published topics

### `user.registered`

Published when a new user account is created (password registration or first OAuth signup for that email).

**Producer:** `App\Listeners\PublishUserRegisteredToKafka`  
**Trigger:** Laravel `Registered` event

**Payload (JSON value):**

```json
{
  "event": "user.registered",
  "user_id": 42,
  "email": "user@example.com",
  "name": "Jane Customer"
}
```

| Field | Type | Description |
| --- | --- | --- |
| `event` | string | Event name |
| `user_id` | integer | User primary key |
| `email` | string | Email address |
| `name` | string | Display name |

Login and OAuth sign-in for an existing account do not emit this event.

## Consumed topics

Auth does not consume Kafka topics.

## Message format

Records use UTF-8 JSON objects as the message value. Topic name identifies the event type. Consumers subscribe by topic and deserialize the JSON payload.
