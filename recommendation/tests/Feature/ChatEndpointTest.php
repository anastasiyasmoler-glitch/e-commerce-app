<?php

namespace Tests\Feature;

use App\Contracts\LlmClient;
use App\Repositories\Contracts\CatalogProductProjectionRepositoryInterface;
use App\Repositories\Contracts\ProductVectorIndexInterface;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChatEndpointTest extends TestCase
{
    use RefreshDatabase;

    public function test_chat_requires_gateway_identity(): void
    {
        $this->postJson('/recommendations/chat', [
            'message' => 'pink sofa',
            'store_id' => 1,
        ])->assertUnauthorized();
    }

    public function test_chat_returns_answer_and_products(): void
    {
        $this->mock(ProductVectorIndexInterface::class, function ($mock): void {
            $mock->shouldReceive('searchSimilar')
                ->once()
                ->with(1, [0.1, 0.2], 5, false)
                ->andReturn([
                    ['product_id' => 9, 'store_id' => 1, 'score' => 0.91],
                ]);
        });

        $this->mock(CatalogProductProjectionRepositoryInterface::class, function ($mock): void {
            $mock->shouldReceive('findPayloadsByStore')
                ->once()
                ->with(1, [9])
                ->andReturn([
                    [
                        'product_id' => 9,
                        'store_id' => 1,
                        'is_published' => true,
                        'in_stock' => true,
                        'payload' => [
                            'id' => 9,
                            'store_id' => 1,
                            'name' => 'Rose Sofa',
                            'slug' => 'rose-sofa',
                            'price' => 45000,
                        ],
                    ],
                ]);
        });

        $this->mock(LlmClient::class, function ($mock): void {
            $mock->shouldReceive('generate')
                ->twice()
                ->andReturn(
                    json_encode([
                        'needs_clarification' => false,
                        'search_query' => 'pink sofa',
                        'in_stock_only' => false,
                    ], JSON_THROW_ON_ERROR),
                    'The Rose Sofa is a close match.',
                );
            $mock->shouldReceive('embed')->once()->with('pink sofa')->andReturn([0.1, 0.2]);
        });

        $response = $this->postJson('/recommendations/chat', [
            'message' => 'pink sofa',
            'store_id' => 1,
        ], [
            'X-Auth-User-Id' => '42',
            'X-Auth-User-Email' => 'buyer@example.com',
            'X-Auth-User-Roles' => 'customer',
        ]);

        $response->assertOk()
            ->assertJsonPath('products.0.name', 'Rose Sofa')
            ->assertJsonPath('extracted_filters.search_query', 'pink sofa');
    }

    public function test_chat_skips_search_when_clarification_needed(): void
    {
        $this->mock(ProductVectorIndexInterface::class, function ($mock): void {
            $mock->shouldNotReceive('searchSimilar');
        });

        $this->mock(CatalogProductProjectionRepositoryInterface::class, function ($mock): void {
            $mock->shouldNotReceive('findPayloadsByStore');
        });

        $this->mock(LlmClient::class, function ($mock): void {
            $mock->shouldReceive('generate')->once()->andReturn(json_encode([
                'needs_clarification' => true,
                'clarification_question' => 'What product type do you need?',
            ], JSON_THROW_ON_ERROR));
            $mock->shouldNotReceive('embed');
        });

        $response = $this->postJson('/recommendations/chat', [
            'message' => 'help me',
            'store_id' => 1,
        ], [
            'X-Auth-User-Id' => '42',
            'X-Auth-User-Email' => 'buyer@example.com',
            'X-Auth-User-Roles' => 'customer',
        ]);

        $response->assertOk()
            ->assertJsonPath('answer', 'What product type do you need?')
            ->assertJsonPath('products', []);
    }
}
