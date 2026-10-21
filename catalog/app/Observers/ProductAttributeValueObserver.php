<?php

namespace App\Observers;

use App\Contracts\KafkaPublisherInterface;
use App\Models\ProductAttributeValue;
use App\Services\CatalogReadService;

class ProductAttributeValueObserver
{
    public function __construct(
        private readonly KafkaPublisherInterface $kafka,
        private readonly CatalogReadService $cache,
    ) {}

    public function saved(ProductAttributeValue $value): void
    {
        $this->publishProductUpdated($value);
    }

    public function deleted(ProductAttributeValue $value): void
    {
        $this->publishProductUpdated($value);
    }

    private function publishProductUpdated(ProductAttributeValue $value): void
    {
        $product = $value->product;
        if ($product === null) {
            return;
        }

        $this->cache->invalidateProduct($product->store_id, $product->id);
        $this->kafka->publish('product.updated', $product->toEventPayload());
    }
}
