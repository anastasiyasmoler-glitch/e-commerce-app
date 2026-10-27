<?php

namespace App\Http\Controllers;

use App\Http\Requests\ReindexRequest;
use App\Services\CatalogIndexIngestService;
use Illuminate\Http\JsonResponse;

class ReindexController extends Controller
{
    public function store(ReindexRequest $request, CatalogIndexIngestService $ingest): JsonResponse
    {
        $stats = $ingest->reindexFromProjections($request->storeId());

        return response()->json(['data' => $stats]);
    }
}
