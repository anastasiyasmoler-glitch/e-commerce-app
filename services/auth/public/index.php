<?php

declare(strict_types=1);

require dirname(__DIR__) . '/vendor/autoload.php';

use Auth\Health;
use Auth\Http\Inertia;

$uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

if ($uri === '/api' || $uri === '/api/' || $uri === '/api/health') {
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode((new Health())->payload(), JSON_THROW_ON_ERROR);
    exit;
}

Inertia::render('Welcome', [
    'appName' => 'Auth Service',
]);
