<?php

namespace App\Observers;

use App\Contracts\KafkaPublisherInterface;
use App\Models\Stock;

class StockObserver
{
    public function __construct(
        private readonly KafkaPublisherInterface $kafka,
    ) {}

    public function saved(Stock $stock): void
    {
        $this->kafka->publish('stock.changed', [
            'product_id' => $stock->product_id,
            'store_id' => $stock->store_id,
            'quantity' => $stock->quantity,
            'reserved' => $stock->reserved,
        ]);
    }
}
