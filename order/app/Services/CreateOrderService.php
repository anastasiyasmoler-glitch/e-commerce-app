<?php

namespace App\Services;

use App\Contracts\KafkaPublisherInterface;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\ProductSnapshot;
use App\Repositories\Contracts\OrderIdempotencyRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\ProductSnapshotRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CreateOrderService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly ProductSnapshotRepositoryInterface $snapshots,
        private readonly KafkaPublisherInterface $kafka,
        private readonly OrderIdempotencyRepositoryInterface $idempotency,
    ) {}

    /**
     * @param  list<array{product_id: int, quantity: int}>  $lineItems
     */
    public function create(
        int $userId,
        string $userEmail,
        string $idempotencyKey,
        array $lineItems,
        ?array $shippingAddress = null,
        string $currency = 'USD',
    ): Order {
        return DB::transaction(function () use (
            $userId,
            $userEmail,
            $idempotencyKey,
            $lineItems,
            $shippingAddress,
            $currency,
        ): Order {
            $existingOrderId = $this->idempotency->lockForKey($userId, $idempotencyKey);

            if ($existingOrderId !== null) {
                $existing = $this->orders->findById($existingOrderId);

                if ($existing !== null) {
                    return $existing;
                }
            }

            $items = $this->buildOrderItems($lineItems);
            $totalCents = array_sum(
                array_map(
                    fn (array $item): int => $item['price_cents'] * $item['quantity'],
                    $items,
                ),
            );

            $order = $this->orders->create(
                $userId,
                $userEmail,
                OrderStatus::Created->value,
                $totalCents,
                $currency,
                $shippingAddress,
                $items,
            );

            $this->idempotency->attachOrderId($userId, $idempotencyKey, $order->id);

            $this->kafka->publish('order.created', [
                'order_id' => $order->id,
                'items' => array_map(
                    fn (array $item): array => [
                        'product_id' => $item['product_id'],
                        'store_id' => $item['store_id'],
                        'quantity' => $item['quantity'],
                    ],
                    $items,
                ),
            ]);

            return $order;
        });
    }

    /**
     * @param  list<array{product_id: int, quantity: int}>  $lineItems
     * @return list<array{product_id: int, store_id: int, product_name: string, sku: ?string, price_cents: int, quantity: int}>
     */
    private function buildOrderItems(array $lineItems): array
    {
        $productIds = array_values(array_unique(array_map(
            fn (array $line): int => (int) $line['product_id'],
            $lineItems,
        )));

        $snapshots = $this->snapshots->findMany($productIds);
        $items = [];

        foreach ($lineItems as $line) {
            $productId = (int) $line['product_id'];
            $quantity = (int) $line['quantity'];

            if ($productId < 1 || $quantity < 1) {
                throw ValidationException::withMessages([
                    'items' => ['Each line must have a valid product_id and quantity.'],
                ]);
            }

            /** @var ProductSnapshot|null $snapshot */
            $snapshot = $snapshots->get($productId);

            if ($snapshot === null) {
                throw ValidationException::withMessages([
                    'items' => ["Product {$productId} is not available for checkout."],
                ]);
            }

            $items[] = [
                'product_id' => $productId,
                'store_id' => (int) $snapshot->store_id,
                'product_name' => $snapshot->name,
                'sku' => $snapshot->sku,
                'price_cents' => (int) $snapshot->price_cents,
                'quantity' => $quantity,
            ];
        }

        return $items;
    }
}
