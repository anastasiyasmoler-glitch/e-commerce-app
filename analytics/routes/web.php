<?php

use App\Http\Controllers\MetricsController;
use Illuminate\Support\Facades\Route;

Route::get('/metrics', MetricsController::class);

Route::get('/', function () {
    return view('welcome');
});

Route::get('/docs', function () {
    return response()->file(public_path('docs/index.html'));
});

Route::get('/docs/openapi.yaml', function () {
    return response()->file(base_path('docs/openapi.yaml'), [
        'Content-Type' => 'application/yaml',
    ]);
});
