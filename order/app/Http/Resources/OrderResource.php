<?php

namespace App\Http\Resources;

use App\Models\Order;
use App\Models\OrderItem;

class OrderResource
{
    /**
     * @return array<string, mixed>
     */
    public static function toArray(Order $order): array
    {
        return [
            'id' => $order->id,
            'user_id' => $order->user_id,
            'status' => $order->status,
            'total_cents' => $order->total_cents,
            'currency' => $order->currency,
            'shipping_address' => $order->shipping_address,
            'items' => $order->items->map(fn (OrderItem $item): array => [
                'product_id' => $item->product_id,
                'store_id' => $item->store_id,
                'product_name' => $item->product_name,
                'sku' => $item->sku,
                'price_cents' => $item->price_cents,
                'quantity' => $item->quantity,
            ])->values()->all(),
            'created_at' => $order->created_at?->toIso8601String(),
        ];
    }
}
