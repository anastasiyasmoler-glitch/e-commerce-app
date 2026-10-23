<?php

return [
    'catalog_base_url' => env('CATALOG_BASE_URL', 'https://catalog-nginx'),
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
    ],
    'embedding_dimensions' => (int) env('RECOMMENDATION_EMBEDDING_DIMENSIONS', 1536),
];
