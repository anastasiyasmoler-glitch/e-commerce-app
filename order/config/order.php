<?php

return [
    'idempotency_ttl_seconds' => (int) env('ORDER_IDEMPOTENCY_TTL_SECONDS', 86400),
];
