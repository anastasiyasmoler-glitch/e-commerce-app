<?php

namespace App\Providers;

use App\Contracts\KafkaPublisherInterface;
use App\Contracts\PaymentGatewayInterface;
use App\Infrastructure\Payments\FakePaymentGateway;
use App\Infrastructure\Payments\StripePaymentGateway;
use App\Kafka\ArrayKafkaPublisher;
use App\Kafka\OutboxKafkaPublisher;
use App\Repositories\Contracts\OrderIdempotencyRepositoryInterface;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\Contracts\OutboxMessageRepositoryInterface;
use App\Repositories\Contracts\ProductSnapshotRepositoryInterface;
use App\Repositories\EloquentOrderIdempotencyRepository;
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
        $this->app->bind(OrderIdempotencyRepositoryInterface::class, EloquentOrderIdempotencyRepository::class);

        $this->app->singleton(ArrayKafkaPublisher::class);

        $this->app->bind(KafkaPublisherInterface::class, OutboxKafkaPublisher::class);

        $this->app->bind(
            PaymentGatewayInterface::class,
            $this->app->environment('testing') ? FakePaymentGateway::class : StripePaymentGateway::class,
        );
    }

    public function boot(): void
    {
        //
    }
}
