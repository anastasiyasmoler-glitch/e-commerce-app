<?php

namespace App\Contracts;

use App\Dto\PaymentCheckoutResult;
use App\Dto\PaymentWebhookNotification;

interface PaymentGatewayInterface
{
    public function providerName(): string;

    /**
     * @param  array<string, string>  $metadata
     */
    public function startPayment(int $amountCents, string $currency, array $metadata): PaymentCheckoutResult;

    /**
     * @param  array<string, string>  $headers
     */
    public function parseWebhook(string $payload, array $headers): ?PaymentWebhookNotification;
}
