<?php

namespace App\Observers;

use App\Contracts\KafkaPublisherInterface;
use App\Models\Stock;
use App\Services\CatalogReadService;

class StockObserver
{
    public function __construct(
        private readonly KafkaPublisherInterface $kafka,
        private readonly CatalogReadService $cache,
    ) {}

    public function saved(Stock $stock): void
    {
        $this->cache->invalidateProduct($stock->store_id, $stock->product_id);
        $this->kafka->publish('stock.changed', [
            'product_id' => $stock->product_id,
            'store_id' => $stock->store_id,
            'quantity' => $stock->quantity,
            'reserved' => $stock->reserved,
        ]);
    }
}
