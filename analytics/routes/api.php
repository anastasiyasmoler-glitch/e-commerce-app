<?php

use App\Http\Controllers\HealthController;
use App\Http\Controllers\OrderMetricsController;
use App\Http\Controllers\SearchMetricsController;
use App\Http\Controllers\UserMetricsController;
use Illuminate\Support\Facades\Route;

Route::get('/healthz', [HealthController::class, 'healthz']);
Route::get('/readyz', [HealthController::class, 'readyz']);

Route::middleware('staff.analytics')->group(function (): void {
    Route::get('/users', UserMetricsController::class);
    Route::get('/orders', OrderMetricsController::class);
    Route::get('/search', SearchMetricsController::class);
});
