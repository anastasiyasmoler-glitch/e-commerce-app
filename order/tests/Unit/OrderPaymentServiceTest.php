<?php

namespace Tests\Unit;

use App\Dto\PaymentWebhookNotification;
use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Enums\PaymentWebhookOutcome;
use App\Models\Order;
use App\Models\OutboxMessage;
use App\Models\Payment;
use App\Services\OrderPaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPaymentServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_succeeded_marks_order_confirmed_and_enqueues_outbox_events(): void
    {
        $order = $this->seedPaymentPendingOrder('pi_test_1');

        $this->app->make(OrderPaymentService::class)->handleWebhookNotification(
            new PaymentWebhookNotification(
                provider: 'stripe',
                providerPaymentId: 'pi_test_1',
                outcome: PaymentWebhookOutcome::Succeeded,
            ),
        );

        $this->assertSame(OrderStatus::Confirmed->value, $order->fresh()->status);
        $this->assertSame(PaymentStatus::Succeeded->value, Payment::query()->first()->status);

        $topics = OutboxMessage::query()->orderBy('id')->pluck('topic')->all();
        $this->assertSame(['order.paid', 'order.confirmed'], $topics);
    }

    public function test_payment_webhook_is_idempotent_when_order_already_confirmed(): void
    {
        $order = $this->seedPaymentPendingOrder('pi_test_3');
        $order->update(['status' => OrderStatus::Confirmed->value]);
        Payment::query()->where('order_id', $order->id)->update(['status' => PaymentStatus::Succeeded->value]);

        $this->app->make(OrderPaymentService::class)->handleWebhookNotification(
            new PaymentWebhookNotification(
                provider: 'stripe',
                providerPaymentId: 'pi_test_3',
                outcome: PaymentWebhookOutcome::Succeeded,
            ),
        );

        $this->assertDatabaseCount('outbox_messages', 0);
    }

    public function test_payment_failed_releases_stock_via_outbox(): void
    {
        $order = $this->seedPaymentPendingOrder('pi_test_2');

        $this->app->make(OrderPaymentService::class)->handleWebhookNotification(
            new PaymentWebhookNotification(
                provider: 'stripe',
                providerPaymentId: 'pi_test_2',
                outcome: PaymentWebhookOutcome::Failed,
                failureReason: 'card_declined',
            ),
        );

        $this->assertSame(OrderStatus::PaymentFailed->value, $order->fresh()->status);

        $topics = OutboxMessage::query()->orderBy('id')->pluck('topic')->all();
        $this->assertContains('order.payment_failed', $topics);
        $this->assertContains('stock.release_requested', $topics);
    }

    private function seedPaymentPendingOrder(string $providerPaymentId): Order
    {
        $order = Order::query()->create([
            'user_id' => 1,
            'user_email' => 'u@example.com',
            'status' => OrderStatus::PaymentPending->value,
            'total_cents' => 900,
            'currency' => 'USD',
        ]);

        Payment::query()->create([
            'order_id' => $order->id,
            'provider' => 'stripe',
            'provider_payment_id' => $providerPaymentId,
            'status' => PaymentStatus::Pending->value,
            'amount_cents' => 900,
            'currency' => 'USD',
        ]);

        return $order;
    }
}
