<?php

namespace App\Services;

use App\Contracts\KafkaPublisherInterface;
use App\Enums\OrderStatus;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;

/**
 * Reacts to Catalog inventory outcomes after order.created (choreography saga).
 *
 * Catalog publishes stock.reserved or stock.reservation_failed; this service updates
 * orders.status and, on failure, enqueues order.cancelled via the outbox in the same DB transaction.
 */
class OrderStockReservationHandler
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly KafkaPublisherInterface $kafka,
        private readonly OrderPaymentService $payments,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(string $topic, array $payload): void
    {
        if ($topic === 'stock.reserved') {
            $this->onStockReserved($payload);

            return;
        }

        if ($topic === 'stock.reservation_failed') {
            $this->onStockReservationFailed($payload);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function onStockReserved(array $payload): void
    {
        $orderId = $this->orderIdFromPayload($payload);

        if ($orderId === null) {
            return;
        }

        $moved = $this->orders->transitionStatus(
            $orderId,
            OrderStatus::Created->value,
            OrderStatus::StockReserved->value,
        );

        if ($moved) {
            $this->payments->beginAfterStockReserved($orderId);
        }
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function onStockReservationFailed(array $payload): void
    {
        $orderId = $this->orderIdFromPayload($payload);

        if ($orderId === null) {
            return;
        }

        DB::transaction(function () use ($orderId, $payload): void {
            $moved = $this->orders->transitionStatus(
                $orderId,
                OrderStatus::Created->value,
                OrderStatus::Cancelled->value,
            );

            if (! $moved) {
                return;
            }

            $this->kafka->publish('order.cancelled', [
                'order_id' => $orderId,
                'reason' => (string) ($payload['reason'] ?? 'stock_reservation_failed'),
            ]);
        });
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private function orderIdFromPayload(array $payload): ?int
    {
        $orderId = $payload['order_id'] ?? null;

        if (! is_numeric($orderId)) {
            return null;
        }

        $id = (int) $orderId;

        return $id > 0 ? $id : null;
    }
}
