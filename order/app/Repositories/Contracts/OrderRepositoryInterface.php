<?php

namespace App\Repositories\Contracts;

use App\Models\Order;
use Illuminate\Pagination\LengthAwarePaginator;

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


    public function transitionStatus(int $orderId, string $fromStatus, string $toStatus): bool;

    /**
     * @param  list<string>  $fromStatuses
     */
    public function transitionStatusFromAny(int $orderId, array $fromStatuses, string $toStatus): bool;

    /**
     * @return LengthAwarePaginator<int, Order>
     */
    public function paginate(?int $userId, int $page, int $perPage): LengthAwarePaginator;
}
