<?php

namespace App\Repositories\Contracts;

use App\Models\Order;

interface OrderRepositoryInterface
{
    /**
     * @param  list<array{product_id: int, store_id: int, product_name: string, sku: ?string, price_cents: int, quantity: int}>  $items
     */
    public function create(
        int $userId,
        string $userEmail,
        string $status,
        int $totalCents,
        string $currency,
        ?array $shippingAddress,
        array $items,
    ): Order;

    public function findById(int $orderId): ?Order;
}
