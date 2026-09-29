<?php

namespace App\Providers;

use App\Repositories\Contracts\AdminUserRepositoryInterface;
use App\Repositories\EloquentAdminUserRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->bind(AdminUserRepositoryInterface::class, EloquentAdminUserRepository::class);
    }

    public function boot(): void
    {
        //
    }
}
