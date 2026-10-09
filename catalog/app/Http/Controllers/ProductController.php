<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListCatalogRequest;
use App\Models\Product;
use App\Services\CatalogReadService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(
        private readonly CatalogReadService $catalog,
    ) {}

    public function index(ListCatalogRequest $request): JsonResponse
    {
        $storeId = (int) $request->validated('store_id');
        $categoryId = $request->validated('category_id');
        $page = (int) ($request->validated('page') ?? 1);
        $perPage = (int) ($request->validated('per_page') ?? 20);

        $rows = $this->catalog->products(
            $storeId,
            $categoryId !== null ? (int) $categoryId : null,
            $page,
            $perPage,
        )->map(fn (Product $product) => $this->present($product))->values();

        return response()->json(['data' => $rows]);
    }

    public function show(Request $request, Product $product): JsonResponse
    {
        $storeId = $request->integer('store_id');
        if ($storeId < 1) {
            abort(422, 'store_id is required.');
        }

        $found = $this->catalog->product($storeId, $product->id);

        return response()->json(['data' => $this->present($found)]);
    }

    /**
     * @return array<string, mixed>
     */
    private function present(Product $product): array
    {
        return [
            'id' => $product->id,
            'store_id' => $product->store_id,
            'category_id' => $product->category_id,
            'name' => $product->name,
            'slug' => $product->slug,
            'description' => $product->description,
            'sku' => $product->sku,
            'stock' => [
                'quantity' => $product->stock?->quantity ?? 0,
                'reserved' => $product->stock?->reserved ?? 0,
            ],
        ];
    }
}
