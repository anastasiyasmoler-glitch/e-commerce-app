<?php

namespace Tests\Integration;

use App\Infrastructure\QdrantProductVectorRepository;
use Illuminate\Support\Facades\Http;
use Tests\Support\FixedVectorLlmClient;
use Tests\Support\RequiresRecommendationIntegration;
use Tests\TestCase;

class QdrantProductVectorIntegrationTest extends TestCase
{
    use RequiresRecommendationIntegration;

    private QdrantProductVectorRepository $vectors;

    private string $collectionName;

    protected function setUp(): void
    {
        $this->skipUnlessRecommendationIntegration();

        parent::setUp();

        $this->prepareIntegrationHttpClient();

        config([
            'recommendation.qdrant.host' => getenv('QDRANT_HOST') ?: '127.0.0.1',
            'recommendation.qdrant.port' => (int) (getenv('QDRANT_PORT') ?: 6333),
        ]);

        $suffix = substr(md5(static::class.(string) microtime(true)), 0, 12);
        $this->useIsolatedQdrantCollection($suffix);
        $this->collectionName = (string) config('recommendation.qdrant.collection');

        $this->skipUnlessQdrantReachable();

        $this->vectors = $this->app->make(QdrantProductVectorRepository::class);
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

    public function test_upsert_search_and_delete_round_trip(): void
    {
        $llm = new FixedVectorLlmClient;
        $embedding = $llm->embed('Rose sofa pink velvet');

        $this->vectors->upsertProductVector(101, 1, $embedding, true, true);

        $hits = $this->vectors->searchSimilar(1, $embedding, 5);
        $this->assertNotEmpty($hits);
        $this->assertSame(101, $hits[0]['product_id']);

        $this->vectors->deleteProductVector(101, 1);

        $afterDelete = $this->vectors->searchSimilar(1, $embedding, 5);
        $this->assertSame([], $afterDelete);
    }

    public function test_search_filters_by_store_and_publication_flags(): void
    {
        $llm = new FixedVectorLlmClient;

        $storeOneVector = $llm->embed('Store one product');
        $storeTwoVector = $llm->embed('Store two product');

        $this->vectors->upsertProductVector(201, 1, $storeOneVector, true, true);
        $this->vectors->upsertProductVector(202, 2, $storeTwoVector, true, true);
        $this->vectors->upsertProductVector(203, 1, $storeOneVector, false, true);

        $storeOneHits = $this->vectors->searchSimilar(1, $storeOneVector, 10);
        $productIds = array_column($storeOneHits, 'product_id');

        $this->assertContains(201, $productIds);
        $this->assertNotContains(202, $productIds);
        $this->assertNotContains(203, $productIds);
    }
}
