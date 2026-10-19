<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Pagination\LengthAwarePaginator;

class EloquentOrderRepository implements OrderRepositoryInterface
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
    ): Order {
        $order = Order::query()->create([
            'user_id' => $userId,
            'user_email' => $userEmail,
            'status' => $status,
            'total_cents' => $totalCents,
            'currency' => $currency,
            'shipping_address' => $shippingAddress,
        ]);

        foreach ($items as $item) {
            OrderItem::query()->create([
                'order_id' => $order->id,
                'product_id' => $item['product_id'],
                'store_id' => $item['store_id'],
                'product_name' => $item['product_name'],
                'sku' => $item['sku'],
                'price_cents' => $item['price_cents'],
                'quantity' => $item['quantity'],
            ]);
        }

        return $order->fresh(['items']);
    }

    public function findById(int $orderId): ?Order
    {
        return Order::query()->with('items')->find($orderId);
    }

    public function transitionStatus(int $orderId, string $fromStatus, string $toStatus): bool
    {
        return Order::query()
            ->whereKey($orderId)
            ->where('status', $fromStatus)
            ->update(['status' => $toStatus]) === 1;
    }

    /**
     * @param  list<string>  $fromStatuses
     */
    public function transitionStatusFromAny(int $orderId, array $fromStatuses, string $toStatus): bool
    {
        if ($fromStatuses === []) {
            return false;
        }

        return Order::query()
            ->whereKey($orderId)
            ->whereIn('status', $fromStatuses)
            ->update(['status' => $toStatus]) === 1;
    }

    /**
     * @return LengthAwarePaginator<int, Order>
     */
    public function paginate(?int $userId, int $page, int $perPage): LengthAwarePaginator
    {
        $query = Order::query()->with('items')->orderByDesc('id');

        if ($userId !== null) {
            $query->where('user_id', $userId);
        }

        return $query->paginate(perPage: $perPage, page: $page);
    }
}
