<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'order_id',
    'provider',
    'stripe_payment_intent_id',
    'status',
    'amount_cents',
    'currency',
    'error_message',
])]
class Payment extends Model
{
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
