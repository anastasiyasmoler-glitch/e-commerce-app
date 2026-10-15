<?php

namespace App\Http\Controllers;

use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class StoreController extends Controller
{
    public function index(): JsonResponse
    {
        $rows = Cache::remember('catalog:stores', 300, fn () => Store::query()
            ->orderBy('name')
            ->get()
            ->map(fn (Store $store) => [
                'id' => $store->id,
                'name' => $store->name,
                'slug' => $store->slug,
            ])
            ->values()
            ->all());

        return response()->json(['data' => $rows]);
    }
}
