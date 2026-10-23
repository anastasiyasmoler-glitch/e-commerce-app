<?php

namespace App\Http\Controllers;

use App\Http\Requests\AnalyticsPeriodRequest;
use App\Services\UserMetricsService;
use Illuminate\Http\JsonResponse;

class UserMetricsController extends Controller
{
    public function __invoke(AnalyticsPeriodRequest $request, UserMetricsService $metrics): JsonResponse
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
