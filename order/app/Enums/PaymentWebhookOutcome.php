<?php

namespace App\Enums;

enum PaymentWebhookOutcome: string
{
    case Succeeded = 'succeeded';

    case Failed = 'failed';
}
