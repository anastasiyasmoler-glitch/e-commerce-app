<?php

namespace App\Providers;

use App\Contracts\Repositories\EventMetricsRepositoryInterface;
use App\Repositories\ClickHouseEventMetricsRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(
            EventMetricsRepositoryInterface::class,
            ClickHouseEventMetricsRepository::class,
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
