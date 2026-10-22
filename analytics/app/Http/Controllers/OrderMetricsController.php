<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyticsPeriodRequest;
use App\Services\OrderMetricsService;
use Illuminate\Http\JsonResponse;

class OrderMetricsController extends Controller
{
    public function __invoke(AnalyticsPeriodRequest $request, OrderMetricsService $metrics): JsonResponse
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
