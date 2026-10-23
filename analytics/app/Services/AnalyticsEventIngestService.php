<?php

namespace App\Services;

use App\Infrastructure\ClickHouseClientFactory;
use Carbon\CarbonImmutable;
use JsonException;

class AnalyticsEventIngestService
{
    public function __construct(
        private readonly ClickHouseClientFactory $clickHouse,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(string $topic, array $payload): void
    {
        $occurredAt = $this->resolveOccurredAt($payload);

        try {
            $encoded = json_encode($payload, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new \InvalidArgumentException('Event payload must be JSON-serializable.', 0, $exception);
        }

        $this->clickHouse->make()->insert('events_raw', [
            [$topic, $encoded, $occurredAt->format('Y-m-d H:i:s.u')],
        ], ['topic', 'payload', 'occurred_at']);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function resolveOccurredAt(array $payload): CarbonImmutable
    {
        $raw = $payload['timestamp'] ?? $payload['occurred_at'] ?? $payload['created_at'] ?? null;

        if (is_string($raw) && $raw !== '') {
            return CarbonImmutable::parse($raw);
        }

        return CarbonImmutable::now('UTC');
    }
}
