<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Default credentials
    |--------------------------------------------------------------------------
    |
    | Used when no credentials are supplied via Codeweavers::withCredentials().
    | Multi-tenant applications should pass per-tenant credentials explicitly.
    | The key is sent as the X-CW-ApiKey header exactly as issued.
    |
    */

    'api_key' => env('CODEWEAVERS_API_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Default organisation
    |--------------------------------------------------------------------------
    |
    | The AssociatedDealerKey that requests are run as. Usually set per tenant
    | via Codeweavers::withOrganisation() instead.
    |
    */

    'dealer_key' => env('CODEWEAVERS_DEALER_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Environment
    |--------------------------------------------------------------------------
    |
    | "sandbox" or "production". Each environment's base URL can be overridden.
    |
    */

    'environment' => env('CODEWEAVERS_ENVIRONMENT', 'production'),

    'base_urls' => [
        'sandbox' => env('CODEWEAVERS_SANDBOX_URL', 'https://demoservices.codeweavers.net'),
        'production' => env('CODEWEAVERS_PRODUCTION_URL', 'https://services.codeweavers.net'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Transport
    |--------------------------------------------------------------------------
    |
    | Retries only apply to connection failures and 429/502/503/504 responses.
    |
    */

    'timeout' => (int) env('CODEWEAVERS_TIMEOUT', 15),

    'connect_timeout' => (int) env('CODEWEAVERS_CONNECT_TIMEOUT', 5),

    'retry' => [
        'times' => (int) env('CODEWEAVERS_RETRY_TIMES', 2),
        'sleep_ms' => (int) env('CODEWEAVERS_RETRY_SLEEP_MS', 250),
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | When enabled, each call logs its endpoint, status and duration. Request
    | bodies and API keys are never logged.
    |
    */

    'logging' => [
        'enabled' => (bool) env('CODEWEAVERS_LOG', false),
        'channel' => env('CODEWEAVERS_LOG_CHANNEL'),
    ],

];
