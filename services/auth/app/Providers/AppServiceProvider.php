<?php

namespace App\Providers;

use App\Models\User;
use App\Policies\UserPolicy;
use App\Repositories\Contracts\AdminUserRepositoryInterface;
use App\Repositories\Contracts\SocialUserRepositoryInterface;
use App\Repositories\EloquentAdminUserRepository;
use App\Repositories\EloquentSocialUserRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AdminUserRepositoryInterface::class, EloquentAdminUserRepository::class);
        $this->app->bind(SocialUserRepositoryInterface::class, EloquentSocialUserRepository::class);
    }

    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
    }
}
