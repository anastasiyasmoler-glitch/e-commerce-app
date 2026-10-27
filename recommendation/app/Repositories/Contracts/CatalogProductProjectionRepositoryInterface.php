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
     * @return array{is_published: bool, in_stock: bool}|null
     */
    public function syncStockAvailability(int $productId, int $storeId, bool $inStock): ?array;

    /**
     * @return array{is_published: bool, in_stock: bool}|null
     */
    public function syncPublicationFlag(int $productId, int $storeId, bool $isPublished): ?array;

    /**
     * @param  list<int>  $productIds
     * @return list<array{
     *     product_id: int,
     *     store_id: int,
     *     payload: array<string, mixed>,
     *     is_published: bool,
     *     in_stock: bool
     * }>
     */
    public function findPayloadsByStore(int $storeId, array $productIds): array;

    /**
     * @return list<array{
     *     product_id: int,
     *     store_id: int,
     *     payload: array<string, mixed>,
     *     is_published: bool,
     *     in_stock: bool
     * }>
     */
    public function allForReindex(?int $storeId = null): array;
}
