<?php

namespace Tests\Integration;

use App\Contracts\LlmClient;
use App\Infrastructure\QdrantProductVectorRepository;
use App\Services\CatalogIndexIngestService;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Tests\Support\FixedVectorLlmClient;
use Tests\Support\RequiresRecommendationIntegration;
use Tests\TestCase;

class CatalogIndexPipelineIntegrationTest extends TestCase
{
    use RequiresRecommendationIntegration;

    private string $collectionName;

    protected function setUp(): void
    {
        $this->skipUnlessRecommendationIntegration();

        parent::setUp();

        $this->prepareIntegrationHttpClient();
        $this->configureIntegrationDatabase();

        config([
            'recommendation.qdrant.host' => getenv('QDRANT_HOST') ?: '127.0.0.1',
            'recommendation.qdrant.port' => (int) (getenv('QDRANT_PORT') ?: 6333),
        ]);

        $suffix = 'pipe_'.substr(md5((string) microtime(true)), 0, 12);
        $this->useIsolatedQdrantCollection($suffix);
        $this->collectionName = (string) config('recommendation.qdrant.collection');

        $this->skipUnlessQdrantReachable();
        $this->skipUnlessPostgresReachable();

        Artisan::call('migrate:fresh', ['--force' => true]);

        $this->app->instance(LlmClient::class, new FixedVectorLlmClient);
    }

    protected function tearDown(): void
    {
        if (isset($this->collectionName)) {
            $host = (string) config('recommendation.qdrant.host');
            $port = (int) config('recommendation.qdrant.port');
            $scheme = config('recommendation.qdrant.use_https') ? 'https' : 'http';
            Http::withOptions(['proxy' => ''])->timeout(5)
                ->delete(sprintf('%s://%s:%d/collections/%s', $scheme, $host, $port, $this->collectionName));
        }

        parent::tearDown();
    }

    public function test_decoded_kafka_payload_indexes_postgres_and_qdrant(): void
    {
        $raw = file_get_contents(base_path('tests/Fixtures/CatalogKafka/product.created.json'));
        $this->assertNotFalse($raw);

        $payload = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        $ingest = $this->app->make(CatalogIndexIngestService::class);
        $ingest->handle('product.created', $payload);

        $row = DB::table('catalog_product_projections')
            ->where('product_id', 101)
            ->where('store_id', 1)
            ->first();

        $this->assertNotNull($row);
        $this->assertSame('Rose Sofa', json_decode((string) $row->payload, true)['name']);

        $vectors = $this->app->make(QdrantProductVectorRepository::class);
        $query = (new FixedVectorLlmClient)->embed("Rose Sofa\nPink velvet three-seater");
        $hits = $vectors->searchSimilar(1, $query, 5);

        $this->assertNotEmpty($hits);
        $this->assertSame(101, $hits[0]['product_id']);
    }

    public function test_stock_changed_event_updates_projection_and_vector_metadata(): void
    {
        $ingest = $this->app->make(CatalogIndexIngestService::class);

        $createdRaw = file_get_contents(base_path('tests/Fixtures/CatalogKafka/product.created.json'));
        $ingest->handle('product.created', json_decode((string) $createdRaw, true, 512, JSON_THROW_ON_ERROR));

        $stockRaw = file_get_contents(base_path('tests/Fixtures/CatalogKafka/stock.changed.json'));
        $ingest->handle('stock.changed', json_decode((string) $stockRaw, true, 512, JSON_THROW_ON_ERROR));

        $row = DB::table('catalog_product_projections')
            ->where('product_id', 101)
            ->where('store_id', 1)
            ->first();

        $this->assertNotNull($row);
        $this->assertFalse((bool) $row->in_stock);

        $vectors = $this->app->make(QdrantProductVectorRepository::class);
        $query = (new FixedVectorLlmClient)->embed("Rose Sofa\nPink velvet three-seater");

        $publishedOnly = $vectors->searchSimilar(1, $query, 5, inStockOnly: true);
        $this->assertSame([], $publishedOnly);

        $anyStock = $vectors->searchSimilar(1, $query, 5, inStockOnly: false);
        $this->assertNotEmpty($anyStock);
    }

    public function test_product_deleted_event_removes_projection_and_vector(): void
    {
        $ingest = $this->app->make(CatalogIndexIngestService::class);

        $createdRaw = file_get_contents(base_path('tests/Fixtures/CatalogKafka/product.created.json'));
        $ingest->handle('product.created', json_decode((string) $createdRaw, true, 512, JSON_THROW_ON_ERROR));

        $deletedRaw = file_get_contents(base_path('tests/Fixtures/CatalogKafka/product.deleted.json'));
        $ingest->handle('product.deleted', json_decode((string) $deletedRaw, true, 512, JSON_THROW_ON_ERROR));

        $this->assertNull(
            DB::table('catalog_product_projections')
                ->where('product_id', 101)
                ->where('store_id', 1)
                ->first()
        );

        $vectors = $this->app->make(QdrantProductVectorRepository::class);
        $query = (new FixedVectorLlmClient)->embed("Rose Sofa\nPink velvet three-seater");
        $this->assertSame([], $vectors->searchSimilar(1, $query, 5));
    }

    private function skipUnlessPostgresReachable(): void
    {
        try {
            DB::connection()->getPdo();
        } catch (\Throwable $exception) {
            $this->markTestSkipped('PostgreSQL is not reachable: '.$exception->getMessage());
        }
    }
}
