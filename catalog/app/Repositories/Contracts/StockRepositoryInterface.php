<?php

namespace App\Repositories\Contracts;

use App\Models\Stock;

interface StockRepositoryInterface
{
    public function lockForUpdate(int $productId, int $storeId): ?Stock;

    public function save(Stock $stock): void;
}
