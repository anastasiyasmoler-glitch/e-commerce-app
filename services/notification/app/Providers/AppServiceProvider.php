<?php

namespace App\Providers;

use App\Contracts\KafkaPublisherInterface;
use App\Kafka\LonglangKafkaPublisher;
use App\Repositories\Contracts\NotificationLogRepositoryInterface;
use App\Repositories\MongoNotificationLogRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NotificationLogRepositoryInterface::class, MongoNotificationLogRepository::class);
        $this->app->bind(KafkaPublisherInterface::class, LonglangKafkaPublisher::class);
    }

    public function boot(): void
    {
        //
    }
}
