<?php

namespace App\Telegram\Handlers;

use WeStacks\TeleBot\Handlers\CommandHandler;

class AboutCommandHandler extends CommandHandler
{
    /**
     * Handle the /about command
     */
    public function handle()
    {
        $this->sendMessage([
            'text' => "📱 *About This Bot*\n\n".
                     "This is a powerful Telegram bot built with Laravel.\n\n".
                     "Version: 1.0.0\n".
                     "Framework: Laravel\n".
                     "Package: westacks/telebot-laravel\n\n".
                     'For more information, visit our website or contact support.',
            'parse_mode' => 'Markdown',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => '🌐 Visit Website', 'url' => 'https://your-website.com'],
                        ['text' => '📧 Contact Support', 'url' => 'https://t.me/your_support'],
                    ],
                ],
            ],
        ]);
    }
}
