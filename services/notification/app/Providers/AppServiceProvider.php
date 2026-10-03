<?php

namespace App\Providers;

use App\Repositories\Contracts\NotificationLogRepositoryInterface;
use App\Repositories\MongoNotificationLogRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(NotificationLogRepositoryInterface::class, MongoNotificationLogRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
