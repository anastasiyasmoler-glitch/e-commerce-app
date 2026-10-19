<?php

namespace App\Dto;

use App\Enums\PaymentWebhookOutcome;

readonly class PaymentWebhookNotification
{
    public function __construct(
        public string $provider,
        public string $providerPaymentId,
        public PaymentWebhookOutcome $outcome,
        public ?string $failureReason = null,
    ) {}
}
