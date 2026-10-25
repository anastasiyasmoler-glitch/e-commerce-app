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
}
