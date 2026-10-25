<?php

namespace App\Repositories\Contracts;

interface CatalogProductProjectionRepositoryInterface
{
    /**
     * Persist or refresh a product row from a Catalog Kafka payload (product.created / product.updated).
     *
     * @param  array<string, mixed>  $payload
     */
    public function upsertFromCatalogEvent(array $payload): void;

    public function deleteByProductAndStore(int $productId, int $storeId): void;

    /**
     * @param  list<int>  $productIds
     * @return list<array{product_id: int, store_id: int, payload: array<string, mixed>}>
     */
    public function findPayloadsByStore(int $storeId, array $productIds): array;
}
