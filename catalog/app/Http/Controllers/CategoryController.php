<?php

namespace App\Http\Controllers;

use App\Http\Requests\ListCatalogRequest;
use App\Models\Category;
use App\Services\CatalogReadService;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        private readonly CatalogReadService $catalog,
    ) {}

    public function index(ListCatalogRequest $request): JsonResponse
    {
        $storeId = (int) $request->validated('store_id');
        $parentId = $request->validated('parent_id');

        $rows = $this->catalog->categories($storeId, $parentId !== null ? (int) $parentId : null)
            ->map(fn (Category $category) => [
                'id' => $category->id,
                'store_id' => $category->store_id,
                'parent_id' => $category->parent_id,
                'name' => $category->name,
                'slug' => $category->slug,
            ])
            ->values();

        return response()->json(['data' => $rows]);
    }
}
