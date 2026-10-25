<?php

namespace App\Http\Controllers;

use App\Infrastructure\QdrantProductVectorRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    public function health(QdrantProductVectorRepository $qdrant): JsonResponse
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
            $checks['catalog_projection_table'] = $this->projectionTableReady() ? 'ok' : 'pending';
        }

        $checks['qdrant'] = app()->environment('testing')
            ? 'skipped'
            : ($qdrant->ping() ? 'ok' : 'unavailable');

        $status = collect($checks)->contains(
            fn (string $value): bool => ! in_array($value, ['ok', 'skipped'], true),
        ) ? 'degraded' : 'ok';

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
