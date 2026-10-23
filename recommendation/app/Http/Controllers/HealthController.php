<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function health(): JsonResponse
    {
        $checks = [
            'app' => 'ok',
        ];

        try {
            DB::connection()->getPdo();
            $checks['database'] = 'ok';
        } catch (Throwable $exception) {
            return response()->json([
                'status' => 'degraded',
                'checks' => $checks,
                'message' => 'database not ready',
                'error' => $exception->getMessage(),
            ], 503);
        }

        if (DB::connection()->getDriverName() === 'pgsql') {
            $checks['vector_index_table'] = $this->projectionTableReady() ? 'ok' : 'pending';
        }

        $status = collect($checks)->contains('pending') ? 'degraded' : 'ok';

        return response()->json([
            'status' => $status,
            'checks' => $checks,
        ], $status === 'ok' ? 200 : 503);
    }

    private function projectionTableReady(): bool
    {
        try {
            $row = DB::selectOne("
                SELECT EXISTS (
                    SELECT 1
                    FROM information_schema.tables
                    WHERE table_schema = 'public'
                      AND table_name = 'catalog_product_projections'
                ) AS ready
            ");

            return (bool) ($row->ready ?? false);
        } catch (Throwable) {
            return false;
        }
    }
}
