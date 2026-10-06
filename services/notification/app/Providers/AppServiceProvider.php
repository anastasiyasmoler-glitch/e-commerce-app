<?php

namespace App\Providers;

use App\Auth\JwtClaimUserProvider;
use App\Contracts\KafkaPublisherInterface;
use App\Kafka\LonglangKafkaPublisher;
use App\Repositories\Contracts\NotificationLogRepositoryInterface;
use App\Repositories\MongoNotificationLogRepository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Auth;
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
        Auth::provider('jwt-claim', function (Application $app, array $config): JwtClaimUserProvider {
            return new JwtClaimUserProvider;
        });
    }
}
