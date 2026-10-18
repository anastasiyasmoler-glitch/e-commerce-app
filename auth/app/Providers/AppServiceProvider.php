<?php

namespace App\Providers;

use App\Contracts\Auth\RefreshTokenStore;
use App\Contracts\Auth\TokenIssuer;
use App\Contracts\Kafka\KafkaPublisherInterface;
use App\Contracts\Repositories\AdminUserRepositoryInterface;
use App\Contracts\Repositories\SocialUserRepositoryInterface;
use App\Contracts\Repositories\UserRepository;
use App\Infrastructure\ArrayRefreshTokenStore;
use App\Infrastructure\Auth\TymonTokenIssuer;
use App\Infrastructure\RedisRefreshTokenStore;
use App\Kafka\ArrayKafkaPublisher;
use App\Kafka\LonglangKafkaPublisher;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Repositories\EloquentAdminUserRepository;
use App\Repositories\EloquentSocialUserRepository;
use App\Repositories\EloquentUserRepository;
use App\Socialite\GoogleOidcProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(ArrayRefreshTokenStore::class);
        $this->app->bind(UserRepository::class, EloquentUserRepository::class);
        $this->app->bind(TokenIssuer::class, TymonTokenIssuer::class);
        $this->app->singleton(RefreshTokenStore::class, function ($app) {
            if ($app->runningUnitTests()) {
                return $app->make(ArrayRefreshTokenStore::class);
            }

            return $app->make(RedisRefreshTokenStore::class);
        });
        $this->app->bind(AdminUserRepositoryInterface::class, EloquentAdminUserRepository::class);
        $this->app->bind(SocialUserRepositoryInterface::class, EloquentSocialUserRepository::class);
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
        Gate::policy(User::class, UserPolicy::class);

        Socialite::extend('google', function ($app) {
            $config = $app['config']['services.google'];

            return Socialite::buildProvider(GoogleOidcProvider::class, $config);
        });
    }
}
