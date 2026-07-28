<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'shark' => [
        'sync_public_ip' => env('SHARK_SYNC_PUBLIC_IP'),
        'fee_tax_rate' => (float) env('SHARK_FEE_TAX_RATE', 0.18),
    ],

    'broker_sync' => [
        'ipv4' => env('BROKER_SYNC_IPV4', env('SHARK_SYNC_PUBLIC_IP')),
        'ipv6' => env('BROKER_SYNC_IPV6'),
    ],

    'news' => [
        'locale' => env('MARKET_NEWS_LOCALE', 'en-IN'),
        'country' => env('MARKET_NEWS_COUNTRY', 'IN'),
        'cache_minutes' => (int) env('MARKET_NEWS_CACHE_MINUTES', 10),
        'alpha_vantage_key' => env('ALPHA_VANTAGE_API_KEY'),
        'financial_juice_key' => env('FINANCIAL_JUICE_API_KEY'),
        'financial_juice_url' => env('FINANCIAL_JUICE_STREAM_URL', 'wss://stream.financialjuice.com/v1/stream'),
    ],

];
