<?php

namespace App\Services;

use App\Contracts\KafkaPublisherInterface;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class OrderFulfillmentService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly KafkaPublisherInterface $kafka,
    ) {}

    public function shipForStaff(int $orderId): Order
    {
        return $this->transitionForStaff(
            $orderId,
            OrderStatus::Confirmed->value,
            OrderStatus::Shipped->value,
            'order.shipped',
        );
    }

    public function completeForStaff(int $orderId): Order
    {
        return $this->transitionForStaff(
            $orderId,
            OrderStatus::Shipped->value,
            OrderStatus::Completed->value,
            'order.completed',
        );
    }

    private function transitionForStaff(
        int $orderId,
        string $fromStatus,
        string $toStatus,
        string $topic,
    ): Order {
        return DB::transaction(function () use ($orderId, $fromStatus, $toStatus, $topic): Order {
            $order = $this->orders->findById($orderId);

            if ($order === null) {
                abort(404);
            }

            $moved = $this->orders->transitionStatus($orderId, $fromStatus, $toStatus);

            if (! $moved) {
                throw new UnprocessableEntityHttpException(
                    "Order cannot move from {$fromStatus} to {$toStatus}.",
                );
            }

            $this->kafka->publish($topic, [
                'order_id' => $orderId,
                'total_cents' => (int) $order->total_cents,
                'currency' => (string) $order->currency,
            ]);

            return $order->fresh() ?? $order;
        });
    }
}
