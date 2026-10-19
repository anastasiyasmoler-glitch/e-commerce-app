<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreOrderRequest;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\CreateOrderService;
use Illuminate\Http\JsonResponse;

class OrderController extends Controller
{
    public function __construct(
        private readonly CreateOrderService $createOrder,
    ) {}

    public function store(StoreOrderRequest $request): JsonResponse
    {
        $order = $this->createOrder->create(
            $request->userId(),
            $request->userEmail(),
            $request->idempotencyKey(),
            $request->lineItems(),
            $request->validated('shipping_address'),
            (string) ($request->validated('currency') ?? 'USD'),
        );

        return response()->json(['data' => $this->present($order)], 201);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Order $order): array
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
