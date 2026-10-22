<?php

namespace App\Http\Controllers;

use App\Infrastructure\ClickHouseClientFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Throwable;

class HealthController extends Controller
{
    public function healthz(): Response
    {
        return response('ok', 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }

    public function readyz(ClickHouseClientFactory $clickHouse): JsonResponse|Response
    {
        try {
            $clickHouse->make()->ping(true);
        } catch (Throwable $exception) {
            return response()->json([
                'message' => 'clickhouse not ready',
                'error' => $exception->getMessage(),
            ], 503);
        }

        return response('ok', 200)->header('Content-Type', 'text/plain; charset=UTF-8');
    }
}
