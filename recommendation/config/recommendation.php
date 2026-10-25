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
    'chat' => [
        'message_max_length' => (int) env('RECOMMENDATION_MESSAGE_MAX_LENGTH', 2000),
        'default_result_limit' => (int) env('RECOMMENDATION_DEFAULT_RESULT_LIMIT', 5),
        'max_result_limit' => (int) env('RECOMMENDATION_MAX_RESULT_LIMIT', 20),
        'similarity_threshold' => (float) env('RECOMMENDATION_SIMILARITY_THRESHOLD', 0.55),
        'conversation_idle_minutes' => (int) env('RECOMMENDATION_CONVERSATION_IDLE_MINUTES', 30),
        'conversation_max_hours' => (int) env('RECOMMENDATION_CONVERSATION_MAX_HOURS', 24),
    ],
];
