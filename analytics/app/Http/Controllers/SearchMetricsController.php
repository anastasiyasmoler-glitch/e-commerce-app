<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyticsPeriodRequest;
use App\Services\SearchMetricsService;
use Illuminate\Http\JsonResponse;

class SearchMetricsController extends Controller
{
    public function __invoke(AnalyticsPeriodRequest $request, SearchMetricsService $metrics): JsonResponse
    {
        [$from, $to] = $request->period();

        return response()->json([
            'data' => $metrics->report($from, $to),
            'meta' => [
                'from' => $from->toDateString(),
                'to' => $to->toDateString(),
            ],
        ]);
    }
}
