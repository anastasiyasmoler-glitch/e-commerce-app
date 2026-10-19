<?php

namespace App\Services;

use App\Contracts\KafkaPublisherInterface;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

/**
 * Customer-initiated cancel before the order is shipped (saga compensation when stock was reserved).
 */
class CancelOrderService
{
    /** @var list<string> */
    private const CANCELLABLE_STATUSES = [
        OrderStatus::Created->value,
        OrderStatus::StockReserved->value,
        OrderStatus::PaymentPending->value,
        OrderStatus::Paid->value,
    ];

    /** @var list<string> */
    private const STOCK_RELEASE_STATUSES = [
        OrderStatus::StockReserved->value,
        OrderStatus::PaymentPending->value,
        OrderStatus::Paid->value,
    ];

    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly KafkaPublisherInterface $kafka,
    ) {}

    public function cancelForViewer(int $viewerUserId, bool $canViewAll, int $orderId, ?string $reason = null): Order
    {
        $order = $this->orders->findById($orderId);

        if ($order === null) {
            abort(404);
        }

        if (! $canViewAll && $order->user_id !== $viewerUserId) {
            abort(403, 'You cannot cancel this order.');
        }

        if (! in_array($order->status, self::CANCELLABLE_STATUSES, true)) {
            throw new UnprocessableEntityHttpException('This order cannot be cancelled.');
        }

        $previousStatus = $order->status;
        $cancelReason = $reason ?? 'customer_cancelled';

        DB::transaction(function () use ($order, $previousStatus, $cancelReason): void {
            $moved = $this->orders->transitionStatusFromAny(
                (int) $order->id,
                self::CANCELLABLE_STATUSES,
                OrderStatus::Cancelled->value,
            );

            if (! $moved) {
                throw new UnprocessableEntityHttpException('This order cannot be cancelled.');
            }

            $this->kafka->publish('order.cancelled', [
                'order_id' => $order->id,
                'reason' => $cancelReason,
            ]);

            if (in_array($previousStatus, self::STOCK_RELEASE_STATUSES, true)) {
                $this->kafka->publish('stock.release_requested', $this->releasePayload($order));
            }
        });

        $fresh = $this->orders->findById((int) $order->id);

        return $fresh ?? $order;
    }

    /**
     * @return array<string, mixed>
     */
    private function releasePayload(Order $order): array
    {
        $order->loadMissing('items');

        return [
            'order_id' => $order->id,
            'items' => $order->items->map(fn ($item): array => [
                'product_id' => $item->product_id,
                'store_id' => $item->store_id,
                'quantity' => $item->quantity,
            ])->values()->all(),
        ];
    }
}
