<?php

namespace App\Providers;

use App\Contracts\KafkaPublisherInterface;
use App\Kafka\ArrayKafkaPublisher;
use App\Kafka\OutboxKafkaPublisher;
use App\Repositories\Contracts\OutboxMessageRepositoryInterface;
use App\Repositories\EloquentOutboxMessageRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(
            OutboxMessageRepositoryInterface::class,
            EloquentOutboxMessageRepository::class,
        );

        $this->app->singleton(ArrayKafkaPublisher::class);

        $this->app->bind(KafkaPublisherInterface::class, function ($app) {
            if ($app->environment('testing')) {
                return $app->make(ArrayKafkaPublisher::class);
            }

            return $app->make(OutboxKafkaPublisher::class);
        });
    }

    public function boot(): void
    {
        //
    }
}
