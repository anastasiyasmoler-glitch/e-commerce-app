<?php

namespace App\Repositories\Contracts;

interface ProductVectorIndexInterface
{
    /**
     * @param  list<float>  $embedding
     */
    public function upsertProductVector(
        int $productId,
        int $storeId,
        array $embedding,
        bool $isPublished,
        bool $inStock,
    ): void;

    public function deleteProductVector(int $productId, int $storeId): void;
}
