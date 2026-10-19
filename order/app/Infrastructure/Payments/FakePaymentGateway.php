<?php

namespace App\Infrastructure\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Dto\PaymentCheckoutResult;
use App\Dto\PaymentWebhookNotification;
use App\Enums\PaymentWebhookOutcome;

class FakePaymentGateway implements PaymentGatewayInterface
{
    public function providerName(): string
    {
        return 'stripe';
    }

    /**
     * @param  array<string, string>  $metadata
     */
    public function startPayment(int $amountCents, string $currency, array $metadata): PaymentCheckoutResult
    {
        $orderId = $metadata['order_id'] ?? '0';

        return new PaymentCheckoutResult(
            provider: $this->providerName(),
            providerPaymentId: 'pi_fake_'.$orderId,
            amountCents: $amountCents,
            currency: $currency,
        );
    }

    /**
     * @param  array<string, string>  $headers
     */
    public function parseWebhook(string $payload, array $headers): ?PaymentWebhookNotification
    {
        $decoded = json_decode($payload, true);

        if (! is_array($decoded)) {
            return null;
        }

        $outcome = PaymentWebhookOutcome::tryFrom((string) ($decoded['outcome'] ?? ''));
        $providerPaymentId = (string) ($decoded['provider_payment_id'] ?? '');

        if ($outcome === null || $providerPaymentId === '') {
            return null;
        }

        return new PaymentWebhookNotification(
            provider: $this->providerName(),
            providerPaymentId: $providerPaymentId,
            outcome: $outcome,
            failureReason: isset($decoded['failure_reason']) ? (string) $decoded['failure_reason'] : null,
        );
    }
}
