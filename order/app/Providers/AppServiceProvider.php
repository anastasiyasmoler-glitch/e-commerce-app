<?php

namespace App\Providers;

use App\Contracts\KafkaPublisherInterface;
use App\Contracts\OrderIdempotencyStore;
use App\Infrastructure\ArrayOrderIdempotencyStore;
use App\Infrastructure\RedisOrderIdempotencyStore;
use App\Kafka\ArrayKafkaPublisher;
use App\Kafka\OutboxKafkaPublisher;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\OutboxMessageRepositoryInterface;
use App\Repositories\Contracts\ProductSnapshotRepositoryInterface;
use App\Repositories\EloquentOrderRepository;
use App\Repositories\EloquentOutboxMessageRepository;
use App\Repositories\EloquentProductSnapshotRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            OutboxMessageRepositoryInterface::class,
            EloquentOutboxMessageRepository::class,
        );

        $this->app->bind(OrderRepositoryInterface::class, EloquentOrderRepository::class);
        $this->app->bind(ProductSnapshotRepositoryInterface::class, EloquentProductSnapshotRepository::class);
        $this->app->bind(OrderIdempotencyStore::class, function ($app) {
            if ($app->environment('testing')) {
                return $app->make(ArrayOrderIdempotencyStore::class);
            }

            return $app->make(RedisOrderIdempotencyStore::class);
        });

        $this->app->singleton(ArrayKafkaPublisher::class);

        $this->app->bind(KafkaPublisherInterface::class, OutboxKafkaPublisher::class);
    }

    public function boot(): void
    {
        //
    }
}
