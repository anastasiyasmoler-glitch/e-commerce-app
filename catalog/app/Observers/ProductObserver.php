<?php

namespace App\Observers;

use App\Contracts\KafkaPublisherInterface;
use App\Models\Product;

class ProductObserver
{
    public function __construct(
        private readonly KafkaPublisherInterface $kafka,
    ) {}

    public function created(Product $product): void
    {
        $this->kafka->publish('product.created', $this->body($product));
    }

    public function updated(Product $product): void
    {
        $this->kafka->publish('product.updated', $this->body($product));
    }

    public function deleted(Product $product): void
    {
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
        return [
            'id' => $product->id,
            'store_id' => $product->store_id,
            'category_id' => $product->category_id,
            'name' => $product->name,
            'slug' => $product->slug,
            'sku' => $product->sku,
        ];
    }
}
