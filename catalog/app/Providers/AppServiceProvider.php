<?php

namespace App\Providers;

use App\Contracts\KafkaPublisherInterface;
use App\Kafka\ArrayKafkaPublisher;
use App\Kafka\LonglangKafkaPublisher;
use App\Models\Category;
use App\Models\Product;
use App\Models\Stock;
use App\Observers\CategoryObserver;
use App\Observers\ProductObserver;
use App\Observers\StockObserver;
use App\Repositories\Contracts\CatalogReadRepositoryInterface;
use App\Repositories\EloquentCatalogReadRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            CatalogReadRepositoryInterface::class,
            EloquentCatalogReadRepository::class,
        );
        $this->app->singleton(ArrayKafkaPublisher::class);
        $this->app->bind(KafkaPublisherInterface::class, function ($app) {
            if ($app->environment('testing')) {
                return $app->make(ArrayKafkaPublisher::class);
            }

            return $app->make(LonglangKafkaPublisher::class);
        });
    }

    public function boot(): void
    {
        Product::observe(ProductObserver::class);
        Category::observe(CategoryObserver::class);
        Stock::observe(StockObserver::class);
    }
}
