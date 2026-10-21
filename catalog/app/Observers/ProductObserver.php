<?php

namespace App\Observers;

use App\Contracts\KafkaPublisherInterface;
use App\Models\Product;
use App\Services\CatalogReadService;

class ProductObserver
{
    public function __construct(
        private readonly KafkaPublisherInterface $kafka,
        private readonly CatalogReadService $cache,
    ) {}

    public function created(Product $product): void
    {
        $this->cache->invalidateProduct($product->store_id, $product->id);
        $this->kafka->publish('product.created', $this->body($product));
    }

    public function updated(Product $product): void
    {
        $this->cache->invalidateProduct($product->store_id, $product->id);
        $this->kafka->publish('product.updated', $this->body($product));
    }

    public function deleted(Product $product): void
    {
        $this->cache->invalidateProduct($product->store_id, $product->id);
        $this->kafka->publish('product.deleted', [
            'id' => $product->id,
            'store_id' => $product->store_id,
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function body(Product $product): array
    {
        return $product->toEventPayload();
    }
}
