<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read Collection<int, OrderItem> $items
 */
#[Fillable(['user_id', 'user_email', 'status', 'total_cents', 'currency', 'shipping_address'])]
class Order extends Model
{
    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'shipping_address' => 'array',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class)->latestOfMany();
    }
}
