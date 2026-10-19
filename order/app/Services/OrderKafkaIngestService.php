<?php

namespace App\Services;

/**
 * Single entry point for the Order Kafka consumer: routes each topic to the right handler.
 *
 * Keeps {@see ConsumeOrdersCommand} thin (decode JSON only) and groups saga/catalog concerns in services.
 */
class OrderKafkaIngestService
{
    public function __construct(
        private readonly ProductSnapshotSyncService $productSnapshots,
        private readonly OrderStockReservationHandler $stockOutcomes,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(string $topic, array $payload): void
    {
        $this->productSnapshots->handle($topic, $payload);
        $this->stockOutcomes->handle($topic, $payload);
    }
}
