<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_stripe_webhook_does_not_require_gateway_identity_headers(): void
    {
        $order = Order::query()->create([
            'user_id' => 1,
            'user_email' => 'u@example.com',
            'status' => OrderStatus::PaymentPending->value,
            'total_cents' => 500,
            'currency' => 'USD',
        ]);

        Payment::query()->create([
            'order_id' => $order->id,
            'provider' => 'stripe',
            'provider_payment_id' => 'pi_fake_'.$order->id,
            'status' => PaymentStatus::Pending->value,
            'amount_cents' => 500,
            'currency' => 'USD',
        ]);

        $this->postJson('/webhooks/stripe', [
            'outcome' => 'succeeded',
            'provider_payment_id' => 'pi_fake_'.$order->id,
        ])->assertNoContent();

        $this->assertSame(OrderStatus::Confirmed->value, $order->fresh()->status);
    }
}
