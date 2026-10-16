<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'product_id', 'store_id', 'product_name', 'sku', 'price_cents', 'quantity'])]
class OrderItem extends Model
{
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
