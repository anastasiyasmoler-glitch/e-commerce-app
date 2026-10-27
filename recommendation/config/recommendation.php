<?php

return [
    'catalog_base_url' => env('CATALOG_BASE_URL', 'https://catalog-nginx'),
    'product_images_public_url' => env('PRODUCT_IMAGES_PUBLIC_URL', 'https://localhost/product-images'),
    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.0-flash'),
        'embedding_model' => env('GEMINI_EMBEDDING_MODEL', 'gemini-embedding-001'),
        'timeout_seconds' => (int) env('GEMINI_TIMEOUT_SECONDS', 25),
        'retry_times' => (int) env('GEMINI_RETRY_TIMES', 2),
        'retry_sleep_ms' => (int) env('GEMINI_RETRY_SLEEP_MS', 300),
        'http_proxy' => env('GEMINI_HTTP_PROXY', env('HTTPS_PROXY')),
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
        'rate_limit_per_minute' => (int) env('RECOMMENDATION_CHAT_RATE_LIMIT_PER_MINUTE', 20),
        'prompt_max_history_messages' => (int) env('RECOMMENDATION_PROMPT_MAX_HISTORY_MESSAGES', 10),
        'prompt_max_history_chars' => (int) env('RECOMMENDATION_PROMPT_MAX_HISTORY_CHARS', 4000),
    ],
];
