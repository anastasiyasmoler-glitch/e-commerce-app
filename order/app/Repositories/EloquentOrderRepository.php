<?php

namespace App\Repositories;

use App\Models\Order;
use App\Models\OrderItem;
use App\Repositories\Contracts\OrderRepositoryInterface;

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
}
