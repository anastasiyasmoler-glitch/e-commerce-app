<?php

namespace App\Dto;

readonly class PaymentCheckoutResult
{
    public function __construct(
        public string $provider,
        public string $providerPaymentId,
        public int $amountCents,
        public string $currency,
    ) {}
}
