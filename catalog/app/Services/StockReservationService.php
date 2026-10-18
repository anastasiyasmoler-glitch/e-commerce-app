<?php

namespace App\Services;

use App\Contracts\KafkaPublisherInterface;
use App\Repositories\Contracts\StockRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

class StockReservationService
{
    public function __construct(
        private readonly KafkaPublisherInterface $kafka,
        private readonly StockRepositoryInterface $stocks,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(string $topic, array $payload): void
    {
        if ($topic === 'order.created') {
            $this->reserve($payload);

            return;
        }

        if ($topic === 'stock.release_requested') {
            $this->release($payload);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function reserve(array $payload): void
    {
        $orderId = $payload['order_id'] ?? null;
        $items = $payload['items'] ?? [];

        if (! is_array($items) || $items === [] || $orderId === null) {
            $this->kafka->publish('stock.reservation_failed', [
                'order_id' => $orderId,
                'reason' => 'invalid_payload',
            ]);

            return;
        }

        try {
            DB::transaction(function () use ($items, $orderId): void {
                foreach ($items as $item) {
                    $productId = (int) ($item['product_id'] ?? 0);
                    $storeId = (int) ($item['store_id'] ?? 0);
                    $qty = (int) ($item['quantity'] ?? 0);

                    if ($productId < 1 || $storeId < 1 || $qty < 1) {
                        throw new \RuntimeException('invalid_item');
                    }

                    $stock = $this->stocks->lockForUpdate($productId, $storeId);
                    $available = $stock === null ? 0 : ($stock->quantity - $stock->reserved);

                    if ($stock === null || $available < $qty) {
                        throw new \RuntimeException('insufficient_stock');
                    }

                    $stock->reserved += $qty;
                    $this->stocks->save($stock);
                }

                $this->kafka->publish('stock.reserved', ['order_id' => $orderId]);
            });
        } catch (Throwable) {
            $this->kafka->publish('stock.reservation_failed', [
                'order_id' => $orderId,
                'reason' => 'insufficient_stock',
            ]);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function release(array $payload): void
    {
        $items = $payload['items'] ?? [];
        if (! is_array($items)) {
            return;
        }

        DB::transaction(function () use ($items): void {
            foreach ($items as $item) {
                $productId = (int) ($item['product_id'] ?? 0);
                $storeId = (int) ($item['store_id'] ?? 0);
                $qty = (int) ($item['quantity'] ?? 0);

                $stock = $this->stocks->lockForUpdate($productId, $storeId);

                if ($stock === null || $qty < 1) {
                    continue;
                }

                $stock->reserved = max(0, $stock->reserved - $qty);
                $this->stocks->save($stock);
            }
        });
    }
}
