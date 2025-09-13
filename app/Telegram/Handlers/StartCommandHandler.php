<?php

namespace App\Telegram\Handlers;

use WeStacks\TeleBot\Handlers\CommandHandler;

class StartCommandHandler extends CommandHandler
{
    /**
     * Handle the /start command
     */
    public function handle()
    {
        // Send welcome photo
        $this->sendPhoto([
            'photo' => 'https://picsum.photos/400/300', // Replace with your welcome image
            'caption' => "Welcome to Our Bot! 👋\n\nI'm here to help you with everything you need.",
            'parse_mode' => 'HTML',
        ]);

        // Send welcome message with custom keyboard
        $this->sendMessage([
            'text' => "🎯 *What would you like to do?*\n\n".
                     'Choose an option from the menu below:',
            'parse_mode' => 'Markdown',
            'reply_markup' => [
                'keyboard' => [
                    [
                        ['text' => '📱 About Us'],
                        ['text' => '📞 Contact'],
                    ],
                    [
                        ['text' => '❓ Help'],
                        ['text' => '⚙️ Settings'],
                    ],
                    [
                        ['text' => '🎯 Main Menu'],
                    ],
                ],
                'resize_keyboard' => true,
                'one_time_keyboard' => false,
            ],
        ]);

        // Send a follow-up message with inline buttons
        $this->sendMessage([
            'text' => "🔍 *Quick Actions*\n\n".
                     'Select a quick action:',
            'parse_mode' => 'Markdown',
            'reply_markup' => [
                'inline_keyboard' => [
                    [
                        ['text' => '🌐 Visit Website', 'url' => 'https://your-website.com'],
                        ['text' => '📧 Support', 'url' => 'https://t.me/your_support'],
                    ],
                    [
                        ['text' => '📱 Download App', 'url' => 'https://your-app-store-link.com'],
                        ['text' => '📚 Documentation', 'url' => 'https://your-docs.com'],
                    ],
                ],
            ],
        ]);
    }
}
