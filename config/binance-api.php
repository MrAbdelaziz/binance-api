<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Binance API Credentials
    |--------------------------------------------------------------------------
    |
    | Your Binance API credentials. You can get these from your Binance
    | account API management page.
    |
    */
    'api_key' => env('BINANCE_API_KEY'),
    'api_secret' => env('BINANCE_API_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Base URL
    |--------------------------------------------------------------------------
    |
    | The base URL for Binance API. Use the testnet URL for testing.
    |
    */
    'base_url' => env('BINANCE_BASE_URL', 'https://api.binance.com'),

    /*
    |--------------------------------------------------------------------------
    | Testnet Mode
    |--------------------------------------------------------------------------
    |
    | Whether to use Binance testnet. When enabled, uses testnet.binance.vision
    |
    */
    'testnet' => env('BINANCE_TESTNET', false),

    /*
    |--------------------------------------------------------------------------
    | Request Timeout
    |--------------------------------------------------------------------------
    |
    | The timeout for HTTP requests in seconds.
    |
    */
    'timeout' => env('BINANCE_TIMEOUT', 30),

    /*
    |--------------------------------------------------------------------------
    | Rate Limits
    |--------------------------------------------------------------------------
    |
    | Rate limiting configuration to prevent hitting Binance limits.
    |
    */
    'rate_limits' => [
        'requests_per_minute' => 1200,
        'orders_per_second' => 10,
        'orders_per_day' => 200000,
        'weight_per_minute' => 6000,
    ],

    /*
    |--------------------------------------------------------------------------
    | Retry Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for retrying failed requests.
    |
    */
    'retry' => [
        'max_attempts' => 3,
        'delay_milliseconds' => 1000,
        'multiplier' => 2,
    ],

    /*
    |--------------------------------------------------------------------------
    | Logging
    |--------------------------------------------------------------------------
    |
    | Enable/disable logging of API requests and responses.
    |
    */
    'logging' => [
        'enabled' => env('BINANCE_LOGGING_ENABLED', true),
        'log_requests' => env('BINANCE_LOG_REQUESTS', false),
        'log_responses' => env('BINANCE_LOG_RESPONSES', false),
        'log_errors' => env('BINANCE_LOG_ERRORS', true),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cache Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for caching market data and other non-sensitive information.
    |
    */
    'cache' => [
        'enabled' => env('BINANCE_CACHE_ENABLED', true),
        'ttl' => [
            'market_data' => 60, // seconds
            'exchange_info' => 3600, // seconds
            'account_info' => 30, // seconds
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Default Trading Parameters
    |--------------------------------------------------------------------------
    |
    | Default parameters for trading operations.
    |
    */
    'defaults' => [
        'order_type' => 'LIMIT',
        'time_in_force' => 'GTC',
        'recv_window' => 5000,
    ],
];
