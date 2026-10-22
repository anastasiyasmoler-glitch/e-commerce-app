<?php

use App\Http\Controllers\HealthController;
use Illuminate\Support\Facades\Route;

Route::get('/healthz', [HealthController::class, 'healthz']);
Route::get('/readyz', [HealthController::class, 'readyz']);

Route::middleware('staff.analytics')->group(function (): void {
    Route::get('/users', function () {
        return response()->json(['message' => 'User metrics are not implemented yet.']);
    });

    Route::get('/orders', function () {
        return response()->json(['message' => 'Order metrics are not implemented yet.']);
    });

    Route::get('/search', function () {
        return response()->json(['message' => 'Search metrics are not implemented yet.']);
    });
});
