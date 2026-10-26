<?php

namespace App\Providers;

use App\Contracts\LlmClient;
use App\Infrastructure\GeminiLlmClient;
use App\Infrastructure\QdrantProductVectorRepository;
use App\Repositories\Contracts\CatalogProductProjectionRepositoryInterface;
use App\Repositories\Contracts\ConversationRepositoryInterface;
use App\Repositories\Contracts\ProductVectorIndexInterface;
use App\Repositories\PgsqlCatalogProductProjectionRepository;
use App\Repositories\PgsqlConversationRepository;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
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
        $this->app->bind(ConversationRepositoryInterface::class, PgsqlConversationRepository::class);
        $this->app->bind(LlmClient::class, GeminiLlmClient::class);
        $this->app->bind(ProductVectorIndexInterface::class, QdrantProductVectorRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        RateLimiter::for('recommendation-chat', function (Request $request): Limit {
            $perMinute = (int) config('recommendation.chat.rate_limit_per_minute', 20);
            $userId = $request->attributes->get('auth_user_id');

            $key = is_numeric($userId)
                ? 'recommendation-chat:user:'.(int) $userId
                : 'recommendation-chat:ip:'.$request->ip();

            return Limit::perMinute(max(1, $perMinute))->by($key);
        });
    }
}
