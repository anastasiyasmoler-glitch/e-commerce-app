<?php

namespace App\Providers;

use App\Contracts\RefreshTokenStore;
use App\Contracts\UserRepository;
use App\Infrastructure\ArrayRefreshTokenStore;
use App\Infrastructure\RedisRefreshTokenStore;
use App\Repositories\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ArrayRefreshTokenStore::class);
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(RefreshTokenStore::class, function ($app) {
            if ($app->environment('testing')) {
                return $app->make(ArrayRefreshTokenStore::class);
            }

            return $app->make(RedisRefreshTokenStore::class);
        });
    }

    public function boot(): void
    {
        //
    }
}
