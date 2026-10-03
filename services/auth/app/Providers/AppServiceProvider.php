<?php

namespace App\Providers;

use App\Contracts\RefreshTokenStore;
use App\Contracts\UserRepository;
use App\Infrastructure\ArrayRefreshTokenStore;
use App\Infrastructure\RedisRefreshTokenStore;
use App\Models\User;
use App\Policies\UserPolicy;
use App\Repositories\Contracts\AdminUserRepositoryInterface;
use App\Repositories\Contracts\SocialUserRepositoryInterface;
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
        $this->app->bind(RefreshTokenStore::class, function ($app) {
            if ($app->environment('testing')) {
                return $app->make(ArrayRefreshTokenStore::class);
            }

            return $app->make(RedisRefreshTokenStore::class);
        });
        $this->app->bind(AdminUserRepositoryInterface::class, EloquentAdminUserRepository::class);
        $this->app->bind(SocialUserRepositoryInterface::class, EloquentSocialUserRepository::class);
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
