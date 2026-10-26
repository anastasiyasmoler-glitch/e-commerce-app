<?php

namespace Tests\Unit;

use App\Contracts\LlmClient;
use App\Repositories\Contracts\CatalogProductProjectionRepositoryInterface;
use App\Repositories\Contracts\ProductVectorIndexInterface;
use App\Services\CatalogIndexIngestService;
use App\Services\ProductEmbeddingTextBuilder;
use Mockery;
use Tests\TestCase;

class CatalogIndexIngestServiceTest extends TestCase
{
    public function test_product_created_upserts_projection_and_vector(): void
    {
        $payload = [
            'id' => 42,
            'store_id' => 1,
            'name' => 'Pink sofa',
            'description' => 'Soft fabric',
        ];

        $projections = Mockery::mock(CatalogProductProjectionRepositoryInterface::class);
        $projections->shouldReceive('upsertFromCatalogEvent')->once()->with($payload);

        $vectors = Mockery::mock(ProductVectorIndexInterface::class);
        $vectors->shouldReceive('upsertProductVector')
            ->once()
            ->with(42, 1, [0.1, 0.2], false, true);

        $llm = Mockery::mock(LlmClient::class);
        $llm->shouldReceive('embed')->once()->with("Pink sofa\nSoft fabric")->andReturn([0.1, 0.2]);

        $service = new CatalogIndexIngestService(
            $projections,
            $vectors,
            new ProductEmbeddingTextBuilder,
            $llm,
        );
        $service->handle('product.created', $payload);
    }

    public function test_product_deleted_removes_projection_and_vector(): void
    {
        $projections = Mockery::mock(CatalogProductProjectionRepositoryInterface::class);
        $projections->shouldReceive('deleteByProductAndStore')->once()->with(42, 1);

        $vectors = Mockery::mock(ProductVectorIndexInterface::class);
        $vectors->shouldReceive('deleteProductVector')->once()->with(42, 1);

        $llm = Mockery::mock(LlmClient::class);
        $llm->shouldNotReceive('embed');

        $service = new CatalogIndexIngestService(
            $projections,
            $vectors,
            new ProductEmbeddingTextBuilder,
            $llm,
        );
        $service->handle('product.deleted', [
            'id' => 42,
            'store_id' => 1,
        ]);
    }

    public function test_stock_changed_updates_metadata_without_embed(): void
    {
        $projections = Mockery::mock(CatalogProductProjectionRepositoryInterface::class);
        $projections->shouldReceive('syncStockAvailability')
            ->once()
            ->with(42, 1, false)
            ->andReturn(['is_published' => true, 'in_stock' => false]);

        $vectors = Mockery::mock(ProductVectorIndexInterface::class);
        $vectors->shouldReceive('updateProductVectorMetadata')
            ->once()
            ->with(42, 1, true, false);
        $vectors->shouldNotReceive('upsertProductVector');

        $llm = Mockery::mock(LlmClient::class);
        $llm->shouldNotReceive('embed');

        $service = new CatalogIndexIngestService(
            $projections,
            $vectors,
            new ProductEmbeddingTextBuilder,
            $llm,
        );
        $service->handle('stock.changed', [
            'product_id' => 42,
            'store_id' => 1,
            'quantity' => 0,
            'reserved' => 0,
        ]);
    }

    public function test_product_unpublished_updates_metadata_only(): void
    {
        $projections = Mockery::mock(CatalogProductProjectionRepositoryInterface::class);
        $projections->shouldReceive('syncPublicationFlag')
            ->once()
            ->with(42, 1, false)
            ->andReturn(['is_published' => false, 'in_stock' => true]);

        $vectors = Mockery::mock(ProductVectorIndexInterface::class);
        $vectors->shouldReceive('updateProductVectorMetadata')
            ->once()
            ->with(42, 1, false, true);

        $llm = Mockery::mock(LlmClient::class);
        $llm->shouldNotReceive('embed');

        $service = new CatalogIndexIngestService(
            $projections,
            $vectors,
            new ProductEmbeddingTextBuilder,
            $llm,
        );
        $service->handle('product.unpublished', [
            'id' => 42,
            'store_id' => 1,
            'is_published' => false,
        ]);
    }

    public function test_product_published_with_full_payload_reindexes(): void
    {
        $payload = [
            'id' => 42,
            'store_id' => 1,
            'name' => 'Pink sofa',
            'is_published' => true,
            'in_stock' => true,
        ];

        $projections = Mockery::mock(CatalogProductProjectionRepositoryInterface::class);
        $projections->shouldReceive('upsertFromCatalogEvent')->once()->with($payload);

        $vectors = Mockery::mock(ProductVectorIndexInterface::class);
        $vectors->shouldReceive('upsertProductVector')->once();

        $llm = Mockery::mock(LlmClient::class);
        $llm->shouldReceive('embed')->once()->andReturn([0.1]);

        $service = new CatalogIndexIngestService(
            $projections,
            $vectors,
            new ProductEmbeddingTextBuilder,
            $llm,
        );
        $service->handle('product.published', $payload);
    }

    public function test_unknown_topic_is_ignored(): void
    {
        $projections = Mockery::mock(CatalogProductProjectionRepositoryInterface::class);
        $projections->shouldNotReceive('upsertFromCatalogEvent');
        $projections->shouldNotReceive('deleteByProductAndStore');
        $projections->shouldNotReceive('syncStockAvailability');
        $projections->shouldNotReceive('syncPublicationFlag');

        $vectors = Mockery::mock(ProductVectorIndexInterface::class);
        $vectors->shouldNotReceive('upsertProductVector');
        $vectors->shouldNotReceive('deleteProductVector');
        $vectors->shouldNotReceive('updateProductVectorMetadata');

        $llm = Mockery::mock(LlmClient::class);
        $llm->shouldNotReceive('embed');

        $service = new CatalogIndexIngestService(
            $projections,
            $vectors,
            new ProductEmbeddingTextBuilder,
            $llm,
        );
        $service->handle('category.updated', [
            'id' => 42,
            'store_id' => 1,
        ]);
    }
}
