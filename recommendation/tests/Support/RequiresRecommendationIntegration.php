<?php

namespace Tests\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

trait RequiresRecommendationIntegration
{
    protected function prepareIntegrationHttpClient(): void
    {
        putenv('HTTP_PROXY=');
        putenv('HTTPS_PROXY=');
        putenv('ALL_PROXY=');
        putenv('NO_PROXY=127.0.0.1,localhost');
        $_ENV['NO_PROXY'] = '127.0.0.1,localhost';
    }

    protected function skipUnlessRecommendationIntegration(): void
    {
        if (! getenv('RECOMMENDATION_INTEGRATION_TESTS')) {
            $this->markTestSkipped('Set RECOMMENDATION_INTEGRATION_TESTS=1 to run Recommendation integration tests.');
        }
    }

    protected function configureIntegrationDatabase(): void
    {
        config([
            'database.default' => 'pgsql',
            'database.connections.pgsql.host' => getenv('RECOMMENDATION_TEST_DB_HOST') ?: '127.0.0.1',
            'database.connections.pgsql.port' => (int) (getenv('RECOMMENDATION_TEST_DB_PORT') ?: 5435),
            'database.connections.pgsql.database' => getenv('RECOMMENDATION_TEST_DB_DATABASE') ?: 'recommendation',
            'database.connections.pgsql.username' => getenv('RECOMMENDATION_TEST_DB_USERNAME') ?: 'recommendation',
            'database.connections.pgsql.password' => getenv('RECOMMENDATION_TEST_DB_PASSWORD') ?: 'recommendation',
        ]);

        DB::purge('pgsql');
        DB::reconnect('pgsql');
    }

    protected function skipUnlessQdrantReachable(): void
    {
        $host = (string) config('recommendation.qdrant.host');
        $port = (int) config('recommendation.qdrant.port');
        $scheme = config('recommendation.qdrant.use_https') ? 'https' : 'http';

        try {
            $response = Http::withOptions(['proxy' => ''])
                ->timeout(3)
                ->get(sprintf('%s://%s:%d/healthz', $scheme, $host, $port));
        } catch (\Throwable $exception) {
            $this->markTestSkipped('Qdrant is not reachable at '.$host.':'.$port.' ('.$exception->getMessage().')');
        }

        if (! $response->successful()) {
            $this->markTestSkipped('Qdrant is not reachable at '.$host.':'.$port);
        }
    }

    protected function useIsolatedQdrantCollection(string $suffix): void
    {
        $name = 'catalog_products_test_'.$suffix;
        config([
            'recommendation.qdrant.collection' => $name,
            'recommendation.embedding_dimensions' => 8,
            'recommendation.chat.similarity_threshold' => 0.0,
        ]);
    }
}
