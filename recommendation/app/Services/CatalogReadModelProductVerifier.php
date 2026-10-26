<?php

namespace App\Services;

class CatalogReadModelProductVerifier
{
    /**
     * Keep only projection rows that are safe to show in chat (local read-model verify).
     *
     * @param  list<array{
     *     product_id: int,
     *     store_id: int,
     *     payload: array<string, mixed>,
     *     is_published: bool,
     *     in_stock: bool
     * }>  $rows
     * @return list<array{
     *     product_id: int,
     *     store_id: int,
     *     payload: array<string, mixed>,
     *     is_published: bool,
     *     in_stock: bool
     * }>
     */
    public function verify(int $storeId, array $rows, bool $requireInStock): array
    {
        if ($storeId < 1 || $rows === []) {
            return [];
        }

        $verified = [];
        foreach ($rows as $row) {
            if (! $this->isVerifiedRow($storeId, $row, $requireInStock)) {
                continue;
            }

            $verified[] = $row;
        }

        return $verified;
    }

    /**
     * @param  array{
     *     product_id: int,
     *     store_id: int,
     *     payload: array<string, mixed>,
     *     is_published: bool,
     *     in_stock: bool
     * }  $row
     */
    private function isVerifiedRow(int $storeId, array $row, bool $requireInStock): bool
    {
        if ((int) $row['store_id'] !== $storeId) {
            return false;
        }

        if ((int) ($row['payload']['store_id'] ?? 0) !== $storeId) {
            return false;
        }

        if ((int) ($row['payload']['id'] ?? 0) !== (int) $row['product_id']) {
            return false;
        }

        if (! $row['is_published']) {
            return false;
        }

        if ($requireInStock && ! $row['in_stock']) {
            return false;
        }

        $name = $row['payload']['name'] ?? null;

        return is_string($name) && trim($name) !== '';
    }
}
