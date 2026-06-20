<?php

return [
    'base_url' => env('SEVIMA_BASE_URL', 'https://api.sevimaplatform.com'),
    'app_key' => env('SEVIMA_APP_KEY'),
    'secret_key' => env('SEVIMA_SECRET_KEY'),
    'bearer_token' => env('SEVIMA_BEARER_TOKEN'),
    'periode_akademik' => env('SEVIMA_PERIODE_AKADEMIK', '20261'),
    'request_delay_seconds' => (int) env('SEVIMA_REQUEST_DELAY_SECONDS', 2),
    'rate_limit_retry_seconds' => (int) env('SEVIMA_RATE_LIMIT_RETRY_SECONDS', 15),
    'max_attempts' => (int) env('SEVIMA_MAX_ATTEMPTS', 3),
];
