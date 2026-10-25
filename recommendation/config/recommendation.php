<?php

return [
    'catalog_base_url' => env('CATALOG_BASE_URL', 'https://catalog-nginx'),
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        'embedding_model' => env('GEMINI_EMBEDDING_MODEL', 'gemini-embedding-001'),
    ],
    'embedding_dimensions' => (int) env('RECOMMENDATION_EMBEDDING_DIMENSIONS', 1536),
    'qdrant' => [
        'host' => env('QDRANT_HOST', 'qdrant'),
        'port' => (int) env('QDRANT_PORT', 6333),
        'collection' => env('QDRANT_COLLECTION', 'catalog_products'),
        'use_https' => (bool) env('QDRANT_USE_HTTPS', false),
    ],
];
