<?php

namespace Tests\Contract;

use App\Contracts\LlmClient;
use App\Repositories\Contracts\CatalogProductProjectionRepositoryInterface;
use App\Repositories\Contracts\ProductVectorIndexInterface;
use App\Services\CatalogIndexIngestService;
use App\Services\ProductEmbeddingTextBuilder;
use Mockery;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Golden fixtures under tests/Fixtures/CatalogKafka mirror Catalog Kafka payloads
 * (Product::toEventPayload(), ProductObserver, StockObserver).
 */
class CatalogKafkaEventContractTest extends TestCase
{
    /**
     * Keys emitted by catalog/app/Models/Product.php::toEventPayload().
     *
     * @var list<string>
     */
    private const CATALOG_FULL_PRODUCT_KEYS = [
        'id',
        'store_id',
        'category_id',
        'name',
        'slug',
        'description',
        'sku',
        'price',
        'attributes',
        'image',
        'is_published',
        'in_stock',
    ];

    #[DataProvider('fullProductFixtureProvider')]
    public function test_full_product_fixtures_include_catalog_event_keys(string $fixtureFile): void
    {
        $payload = $this->loadFixture($fixtureFile);

        foreach (self::CATALOG_FULL_PRODUCT_KEYS as $key) {
            $this->assertArrayHasKey($key, $payload, $fixtureFile.' missing '.$key);
        }
    }

    /**
     * @return list<array{0: string}>
     */
    public static function fullProductFixtureProvider(): array
    {
        return [
            ['product.created.json'],
            ['product.updated.json'],
            ['product.published.full.json'],
        ];
    }

    public function test_product_deleted_fixture_matches_catalog_observer(): void
    {
        $payload = $this->loadFixture('product.deleted.json');

        $this->assertSame([101, 1], [(int) $payload['id'], (int) $payload['store_id']]);
        $this->assertSame(array_keys($payload), ['id', 'store_id']);
    }

    public function test_product_unpublished_fixture_matches_catalog_observer(): void
    {
        $payload = $this->loadFixture('product.unpublished.json');

        $this->assertFalse($payload['is_published']);
        $this->assertSame(['id', 'store_id', 'is_published'], array_keys($payload));
    }

    public function test_stock_changed_fixture_matches_catalog_observer(): void
    {
        $payload = $this->loadFixture('stock.changed.json');

        $this->assertSame(['product_id', 'store_id', 'quantity', 'reserved'], array_keys($payload));
    }

    public function test_ingest_accepts_product_created_fixture(): void
    {
        $payload = $this->loadFixture('product.created.json');

        $projections = Mockery::mock(CatalogProductProjectionRepositoryInterface::class);
        $projections->shouldReceive('upsertFromCatalogEvent')->once()->with($payload);

        $vectors = Mockery::mock(ProductVectorIndexInterface::class);
        $vectors->shouldReceive('upsertProductVector')->once();

        $llm = Mockery::mock(LlmClient::class);
        $llm->shouldReceive('embed')->once()->andReturn([0.1, 0.2, 0.3]);

        $service = new CatalogIndexIngestService(
            $projections,
            $vectors,
            new ProductEmbeddingTextBuilder,
            $llm,
        );

        $service->handle('product.created', $payload);
    }

    public function test_kafka_consumer_json_decode_then_ingest_product_updated_fixture(): void
    {
        $raw = file_get_contents($this->fixturePath('product.updated.json'));
        $this->assertNotFalse($raw);

        $payload = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);

        $projections = Mockery::mock(CatalogProductProjectionRepositoryInterface::class);
        $projections->shouldReceive('upsertFromCatalogEvent')->once()->with($payload);

        $vectors = Mockery::mock(ProductVectorIndexInterface::class);
        $vectors->shouldReceive('upsertProductVector')->once();

        $llm = Mockery::mock(LlmClient::class);
        $llm->shouldReceive('embed')->once()->andReturn([0.4, 0.5]);

        $service = new CatalogIndexIngestService(
            $projections,
            $vectors,
            new ProductEmbeddingTextBuilder,
            $llm,
        );

        $service->handle('product.updated', $payload);
    }

    /**
     * @return array<string, mixed>
     */
    private function loadFixture(string $filename): array
    {
        $raw = file_get_contents($this->fixturePath($filename));
        $this->assertNotFalse($raw, 'Missing fixture '.$filename);

        $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        $this->assertIsArray($decoded);

        return $decoded;
    }

    private function fixturePath(string $filename): string
    {
        return base_path('tests/Fixtures/CatalogKafka/'.$filename);
    }
}
