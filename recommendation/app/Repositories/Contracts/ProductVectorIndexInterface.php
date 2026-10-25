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

    /**
     * @param  list<float>  $queryVector
     * @return list<array{product_id: int, store_id: int, score: float}>
     */
    public function searchSimilar(
        int $storeId,
        array $queryVector,
        int $limit,
        bool $inStockOnly = false,
    ): array;
}
