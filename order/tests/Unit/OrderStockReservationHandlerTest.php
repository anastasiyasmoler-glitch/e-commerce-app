<?php

namespace Tests\Unit;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\OutboxMessage;
use App\Models\Payment;
use App\Services\OrderStockReservationHandler;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderStockReservationHandlerTest extends TestCase
{
    use RefreshDatabase;

    public function test_stock_reserved_moves_created_order_to_stock_reserved(): void
    {
        $order = $this->seedCreatedOrder();

        $this->app->make(OrderStockReservationHandler::class)->handle('stock.reserved', [
            'order_id' => $order->id,
        ]);

        $this->assertSame(OrderStatus::PaymentPending->value, $order->fresh()->status);

        $payment = Payment::query()->where('order_id', $order->id)->first();
        $this->assertNotNull($payment);
        $this->assertSame('pi_fake_'.$order->id, $payment->provider_payment_id);
        $this->assertDatabaseCount('outbox_messages', 0);
    }

    public function test_stock_reserved_is_idempotent_when_already_processed(): void
    {
        $order = $this->seedCreatedOrder();
        $order->update(['status' => OrderStatus::StockReserved->value]);

        $this->app->make(OrderStockReservationHandler::class)->handle('stock.reserved', [
            'order_id' => $order->id,
        ]);

        $this->assertSame(OrderStatus::StockReserved->value, $order->fresh()->status);
    }

    public function test_reservation_failed_cancels_order_and_writes_order_cancelled_outbox(): void
    {
        $order = $this->seedCreatedOrder();

        $this->app->make(OrderStockReservationHandler::class)->handle('stock.reservation_failed', [
            'order_id' => $order->id,
            'reason' => 'insufficient_stock',
        ]);

        $this->assertSame(OrderStatus::Cancelled->value, $order->fresh()->status);

        $outbox = OutboxMessage::query()->first();
        $this->assertNotNull($outbox);
        $this->assertSame('order.cancelled', $outbox->topic);
        $this->assertSame($order->id, $outbox->payload['order_id']);
    }

    private function seedCreatedOrder(): Order
    {
        return Order::query()->create([
            'user_id' => 1,
            'user_email' => 'u@example.com',
            'status' => OrderStatus::Created->value,
            'total_cents' => 500,
            'currency' => 'USD',
        ]);
    }
}
