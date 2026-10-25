<?php

namespace App\Providers;

use App\Contracts\LlmClient;
use App\Infrastructure\GeminiLlmClient;
use App\Infrastructure\QdrantProductVectorRepository;
use App\Repositories\Contracts\CatalogProductProjectionRepositoryInterface;
use App\Repositories\Contracts\ProductVectorIndexInterface;
use App\Repositories\PgsqlCatalogProductProjectionRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            CatalogProductProjectionRepositoryInterface::class,
            PgsqlCatalogProductProjectionRepository::class,
        );
        $this->app->bind(LlmClient::class, GeminiLlmClient::class);
        $this->app->bind(ProductVectorIndexInterface::class, QdrantProductVectorRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
