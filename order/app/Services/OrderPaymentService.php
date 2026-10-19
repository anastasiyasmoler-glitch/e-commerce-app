<?php

namespace App\Services;

use App\Contracts\KafkaPublisherInterface;
use App\Contracts\PaymentGatewayInterface;
use App\Dto\PaymentWebhookNotification;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentWebhookOutcome;
use App\Models\Order;
use App\Models\Payment;
use App\Repositories\Contracts\OrderRepositoryInterface;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Payment saga step after stock.reserved: start checkout with the configured provider, finish on webhook.
 */
class OrderPaymentService
{
    public function __construct(
        private readonly OrderRepositoryInterface $orders,
        private readonly PaymentGatewayInterface $paymentGateway,
        private readonly KafkaPublisherInterface $kafka,
    ) {}

    public function beginAfterStockReserved(int $orderId): void
    {
        $order = $this->orders->findById($orderId);

        if ($order === null) {
            return;
        }

        $moved = $this->orders->transitionStatus(
            $orderId,
            OrderStatus::StockReserved->value,
            OrderStatus::PaymentPending->value,
        );

        if (! $moved) {
            return;
        }

        try {
            $checkout = $this->paymentGateway->startPayment(
                (int) $order->total_cents,
                (string) $order->currency,
                ['order_id' => (string) $order->id],
            );

            Payment::query()->create([
                'order_id' => $order->id,
                'provider' => $checkout->provider,
                'provider_payment_id' => $checkout->providerPaymentId,
                'status' => PaymentStatus::Pending->value,
                'amount_cents' => $checkout->amountCents,
                'currency' => $checkout->currency,
            ]);
        } catch (Throwable $exception) {
            $this->markPaymentFailed($order, $exception->getMessage());
        }
    }

    public function handleWebhookNotification(PaymentWebhookNotification $notification): void
    {
        match ($notification->outcome) {
            PaymentWebhookOutcome::Succeeded => $this->onPaymentSucceeded($notification),
            PaymentWebhookOutcome::Failed => $this->onPaymentFailed($notification),
        };
    }

    private function onPaymentSucceeded(PaymentWebhookNotification $notification): void
    {
        $payment = $this->findPayment($notification);

        if ($payment === null) {
            return;
        }

        DB::transaction(function () use ($payment): void {
            $payment->status = PaymentStatus::Succeeded->value;
            $payment->save();

            $moved = $this->orders->transitionStatus(
                (int) $payment->order_id,
                OrderStatus::PaymentPending->value,
                OrderStatus::Paid->value,
            );

            if (! $moved) {
                return;
            }

            $orderId = (int) $payment->order_id;

            $this->kafka->publish('order.paid', ['order_id' => $orderId]);
            $this->kafka->publish('order.confirmed', ['order_id' => $orderId]);
        });
    }

    private function onPaymentFailed(PaymentWebhookNotification $notification): void
    {
        $payment = $this->findPayment($notification);

        if ($payment === null) {
            return;
        }

        $order = $this->orders->findById((int) $payment->order_id);

        if ($order === null) {
            return;
        }

        $message = $notification->failureReason ?? 'payment_failed';

        $payment->status = PaymentStatus::Failed->value;
        $payment->error_message = $message;
        $payment->save();

        $this->markPaymentFailed($order, $message);
    }

    private function findPayment(PaymentWebhookNotification $notification): ?Payment
    {
        return Payment::query()
            ->where('provider', $notification->provider)
            ->where('provider_payment_id', $notification->providerPaymentId)
            ->first();
    }

    private function markPaymentFailed(Order $order, string $message): void
    {
        DB::transaction(function () use ($order, $message): void {
            $from = $order->fresh()?->status;

            if ($from === OrderStatus::PaymentPending->value) {
                $this->orders->transitionStatus(
                    (int) $order->id,
                    OrderStatus::PaymentPending->value,
                    OrderStatus::PaymentFailed->value,
                );
            } elseif ($from === OrderStatus::StockReserved->value) {
                $this->orders->transitionStatus(
                    (int) $order->id,
                    OrderStatus::StockReserved->value,
                    OrderStatus::PaymentFailed->value,
                );
            } else {
                return;
            }

            $this->kafka->publish('order.payment_failed', [
                'order_id' => $order->id,
                'reason' => $message,
            ]);

            $this->kafka->publish('stock.release_requested', $this->releasePayload($order));
        });
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
