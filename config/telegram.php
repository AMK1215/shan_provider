<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure your Telegram bot settings
    |
    */

    'bot_token' => env('TELEGRAM_BOT_TOKEN'),
    'api_url' => env('TELEGRAM_API_URL', 'https://api.telegram.org/bot'),
    'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Telegram API Endpoints
    |--------------------------------------------------------------------------
    |
    | These are the main API endpoints for Telegram bot operations
    |
    */

    'endpoints' => [
        'get_updates' => '/getUpdates',
        'set_webhook' => '/setWebhook',
        'get_webhook_info' => '/getWebhookInfo',
        'delete_webhook' => '/deleteWebhook',
    ],

    /*
    |--------------------------------------------------------------------------
    | Webhook Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for the webhook endpoint
    |
    */

    'webhook' => [
        'route' => 'telegram-message-webhook',
        'middleware' => ['api'],
    ],
];
