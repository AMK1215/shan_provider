<?php

namespace App\Telegram\Handlers;

use WeStacks\TeleBot\Handlers\CommandHandler;

class MenuCommandHandler extends CommandHandler
{
    /**
     * Handle the /menu command
     */
    public function handle()
    {
        $this->sendMessage([
            'text' => "🎯 *Main Menu*\n\n".
                     'Please select an option:',
            'parse_mode' => 'Markdown',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => '📱 About Us', 'callback_data' => 'about'],
                        ['text' => '📞 Contact', 'callback_data' => 'contact'],
                    ],
                    [
                        ['text' => '🌐 Visit Website', 'url' => 'https://your-website.com'],
                        ['text' => '📧 Support', 'url' => 'https://t.me/your_support'],
                    ],
                    [
                        ['text' => '❓ Help', 'callback_data' => 'help'],
                    ],
                ],
            ],
        ]);
    }
}
