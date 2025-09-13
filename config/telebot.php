<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Telegram Bot Token
    |--------------------------------------------------------------------------
    |
    | Your Telegram bot token you received from @BotFather
    |
    */
    'token' => env('TELEGRAM_BOT_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Bot Username
    |--------------------------------------------------------------------------
    |
    | Your bot username you received from @BotFather
    |
    */
    'username' => env('TELEGRAM_BOT_USERNAME'),

    /*
    |--------------------------------------------------------------------------
    | Bot Kernel
    |--------------------------------------------------------------------------
    |
    | The kernel class that handles all bot commands and updates
    |
    */
    'kernel' => App\Telegram\Kernel::class,

    /*
    |--------------------------------------------------------------------------
    | Webhook URL
    |--------------------------------------------------------------------------
    |
    | The URL where your bot will receive updates
    |
    */
    'webhook_url' => env('TELEGRAM_WEBHOOK_URL'),

    /*
    |--------------------------------------------------------------------------
    | Webhook Secret Token
    |--------------------------------------------------------------------------
    |
    | Secret token to validate webhook requests
    |
    */
    'webhook_secret' => env('TELEGRAM_WEBHOOK_SECRET'),

    /*
    |--------------------------------------------------------------------------
    | Default Parse Mode
    |--------------------------------------------------------------------------
    |
    | Default parse mode for messages (HTML, Markdown, MarkdownV2)
    |
    */
    'parse_mode' => 'HTML',

    /*
    |--------------------------------------------------------------------------
    | Default Action
    |--------------------------------------------------------------------------
    |
    | Default action to show while bot is processing request
    |
    */
    'action' => 'typing',
];
