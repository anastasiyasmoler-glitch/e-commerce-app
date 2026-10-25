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

    public function test_unknown_topic_is_ignored(): void
    {
        $projections = Mockery::mock(CatalogProductProjectionRepositoryInterface::class);
        $projections->shouldNotReceive('upsertFromCatalogEvent');
        $projections->shouldNotReceive('deleteByProductAndStore');

        $vectors = Mockery::mock(ProductVectorIndexInterface::class);
        $vectors->shouldNotReceive('upsertProductVector');
        $vectors->shouldNotReceive('deleteProductVector');

        $llm = Mockery::mock(LlmClient::class);
        $llm->shouldNotReceive('embed');

        $service = new CatalogIndexIngestService(
            $projections,
            $vectors,
            new ProductEmbeddingTextBuilder,
            $llm,
        );
        $service->handle('stock.changed', [
            'id' => 42,
            'store_id' => 1,
        ]);
    }
}
