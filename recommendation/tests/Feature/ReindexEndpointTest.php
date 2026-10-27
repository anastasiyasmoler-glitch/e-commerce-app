<?php

namespace Tests\Feature;

use App\Contracts\LlmClient;
use App\Repositories\Contracts\CatalogProductProjectionRepositoryInterface;
use App\Repositories\Contracts\ProductVectorIndexInterface;
use Mockery\MockInterface;
use Tests\TestCase;

class ReindexEndpointTest extends TestCase
{
    public function test_reindex_requires_gateway_identity_headers(): void
    {
        $this->postJson('/recommendations/reindex')->assertUnauthorized();
    }

    public function test_customer_cannot_reindex(): void
    {
        $this->postJson('/recommendations/reindex', [], $this->customerHeaders())
            ->assertForbidden();
    }

    public function test_analyst_cannot_reindex(): void
    {
        $this->postJson('/recommendations/reindex', [], [
            'X-Auth-User-Id' => '2',
            'X-Auth-User-Email' => 'analyst@example.com',
            'X-Auth-User-Roles' => 'analyst',
        ])->assertForbidden();
    }

    public function test_admin_can_trigger_reindex(): void
    {
        $this->mock(CatalogProductProjectionRepositoryInterface::class, function (MockInterface $mock): void {
            $mock->shouldReceive('allForReindex')
                ->once()
                ->with(null)
                ->andReturn([
                    [
                        'product_id' => 10,
                        'store_id' => 2,
                        'is_published' => true,
                        'in_stock' => true,
                        'payload' => [
                            'id' => 10,
                            'store_id' => 2,
                            'name' => 'Oslo sofa',
                            'description' => 'Grey fabric',
                            'is_published' => true,
                            'in_stock' => true,
                        ],
                    ],
                ]);
            $mock->shouldReceive('upsertFromCatalogEvent')->once();
        });

        $this->mock(LlmClient::class, function (MockInterface $mock): void {
            $mock->shouldReceive('embed')->once()->andReturn([0.1, 0.2]);
        });

        $this->mock(ProductVectorIndexInterface::class, function (MockInterface $mock): void {
            $mock->shouldReceive('upsertProductVector')->once();
        });

        $this->postJson('/recommendations/reindex', [], $this->adminHeaders())
            ->assertOk()
            ->assertJsonPath('data.processed', 1)
            ->assertJsonPath('data.vectors_indexed', 1)
            ->assertJsonPath('data.vector_failures', 0);
    }

    public function test_admin_can_pass_store_id_filter_to_reindex(): void
    {
        $this->mock(CatalogProductProjectionRepositoryInterface::class, function (MockInterface $mock): void {
            $mock->shouldReceive('allForReindex')
                ->once()
                ->with(3)
                ->andReturn([]);
            $mock->shouldNotReceive('upsertFromCatalogEvent');
        });

        $this->mock(LlmClient::class, function (MockInterface $mock): void {
            $mock->shouldNotReceive('embed');
        });

        $this->postJson('/recommendations/reindex', ['store_id' => 3], $this->adminHeaders())
            ->assertOk()
            ->assertJsonPath('data.processed', 0);
    }

    /**
     * @return array<string, string>
     */
    private function customerHeaders(): array
    {
        return [
            'X-Auth-User-Id' => '5',
            'X-Auth-User-Email' => 'buyer@example.com',
            'X-Auth-User-Roles' => 'customer',
        ];
    }

    /**
     * @return array<string, string>
     */
    private function adminHeaders(): array
    {
        return [
            'X-Auth-User-Id' => '1',
            'X-Auth-User-Email' => 'admin@example.com',
            'X-Auth-User-Roles' => 'admin',
        ];
    }
}
