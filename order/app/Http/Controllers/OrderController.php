<?php

namespace App\Http\Controllers;

use App\Http\Concerns\ResolvesGatewayUser;
use App\Http\Requests\ListOrdersRequest;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use App\Services\CancelOrderService;
use App\Services\CreateOrderService;
use App\Services\OrderReadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    use ResolvesGatewayUser;

    public function __construct(
        private readonly CreateOrderService $createOrder,
        private readonly OrderReadService $orderRead,
        private readonly CancelOrderService $cancelOrder,
    ) {}

    public function index(ListOrdersRequest $request): JsonResponse
    {
        $paginator = $this->orderRead->listForViewer(
            $this->gatewayUserId($request),
            $this->gatewayCanViewAllOrders($request),
            $request->page(),
            $request->perPage(),
        );

        return response()->json([
            'data' => $paginator->getCollection()
                ->map(fn (Order $order): array => OrderResource::toArray($order))
                ->values(),
            'meta' => [
                'current_page' => $paginator->currentPage(),
                'per_page' => $paginator->perPage(),
                'total' => $paginator->total(),
                'last_page' => $paginator->lastPage(),
            ],
        ]);
    }

    public function show(Request $request, int $order): JsonResponse
    {
        $model = $this->orderRead->findForViewer(
            $this->gatewayUserId($request),
            $this->gatewayCanViewAllOrders($request),
            $order,
        );

        if ($model === null) {
            abort(404);
        }

        return response()->json(['data' => OrderResource::toArray($model)]);
    }

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

        return response()->json(['data' => OrderResource::toArray($order)], 201);
    }

    public function cancel(Request $request, int $order): JsonResponse
    {
        $model = $this->cancelOrder->cancelForViewer(
            $this->gatewayUserId($request),
            $this->gatewayCanViewAllOrders($request),
            $order,
            $request->input('reason') !== null ? (string) $request->input('reason') : null,
        );

        return response()->json(['data' => OrderResource::toArray($model)]);
    }
}
