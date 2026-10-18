<?php

namespace App\Repositories;

use App\Models\Stock;
use App\Repositories\Contracts\StockRepositoryInterface;

class EloquentStockRepository implements StockRepositoryInterface
{
    public function lockForUpdate(int $productId, int $storeId): ?Stock
    {
        return Stock::query()
            ->where('product_id', $productId)
            ->where('store_id', $storeId)
            ->lockForUpdate()
            ->first();
    }

    public function save(Stock $stock): void
    {
        $stock->save();
    }
}
