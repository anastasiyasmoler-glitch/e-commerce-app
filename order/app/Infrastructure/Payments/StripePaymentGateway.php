<?php

namespace App\Infrastructure\Payments;

use App\Contracts\PaymentGatewayInterface;
use App\Dto\PaymentCheckoutResult;
use App\Dto\PaymentWebhookNotification;
use App\Enums\PaymentWebhookOutcome;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

class StripePaymentGateway implements PaymentGatewayInterface
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
        Stripe::setApiKey((string) config('services.stripe.secret'));

        $intent = PaymentIntent::create([
            'amount' => $amountCents,
            'currency' => strtolower($currency),
            'automatic_payment_methods' => ['enabled' => true],
            'metadata' => $metadata,
        ]);

        return new PaymentCheckoutResult(
            provider: $this->providerName(),
            providerPaymentId: (string) $intent->id,
            amountCents: $amountCents,
            currency: $currency,
        );
    }

    /**
     * @param  array<string, string>  $headers
     */
    public function parseWebhook(string $payload, array $headers): ?PaymentWebhookNotification
    {
        $signature = $headers['Stripe-Signature'] ?? '';

        $secret = (string) config('services.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $signature, $secret);
        } catch (SignatureVerificationException $exception) {
            abort(400, $exception->getMessage());
        }

        $array = $event->toArray();
        $type = (string) ($array['type'] ?? '');
        $intentId = (string) data_get($array, 'data.object.id', '');

        if ($intentId === '') {
            return null;
        }

        if ($type === 'payment_intent.succeeded') {
            return new PaymentWebhookNotification(
                provider: $this->providerName(),
                providerPaymentId: $intentId,
                outcome: PaymentWebhookOutcome::Succeeded,
            );
        }

        if ($type === 'payment_intent.payment_failed') {
            $message = (string) data_get($array, 'data.object.last_payment_error.message', 'payment_failed');

            return new PaymentWebhookNotification(
                provider: $this->providerName(),
                providerPaymentId: $intentId,
                outcome: PaymentWebhookOutcome::Failed,
                failureReason: $message,
            );
        }

        return null;
    }
}
