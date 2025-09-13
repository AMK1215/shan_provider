<?php

namespace App\Telegram\Handlers;

use WeStacks\TeleBot\Handlers\CallbackHandler;

class MenuCallbackHandler extends CallbackHandler
{
    public function handle()
    {
        $callback = $this->update->callback_query;
        $data = $callback->data;

        switch ($data) {
            case 'about':
                $this->answerCallbackQuery([
                    'text' => 'Loading about information...',
                ]);
                $this->sendMessage([
                    'text' => "📱 *About This Bot*\n\n".
                             "This is a powerful Telegram bot built with Laravel.\n\n".
                             "Version: 1.0.0\n".
                             "Framework: Laravel\n".
                             'Package: westacks/telebot-laravel',
                    'parse_mode' => 'Markdown',
                ]);
                break;

            case 'contact':
                $this->answerCallbackQuery([
                    'text' => 'Loading contact information...',
                ]);
                $this->sendContact([
                    'phone_number' => '+1234567890',
                    'first_name' => 'Support',
                    'last_name' => 'Team',
                ]);
                break;

            case 'help':
                $this->answerCallbackQuery([
                    'text' => 'Loading help information...',
                ]);
                $this->sendMessage([
                    'text' => "🤖 *Help Center*\n\n".
                             "Need assistance? Here's how we can help:\n\n".
                             "1️⃣ Check our FAQ\n".
                             "2️⃣ Contact support\n".
                             '3️⃣ Watch tutorial videos',
                    'parse_mode' => 'Markdown',
                    'reply_markup' => [
                        'inline_keyboard' => [
                            [
                                ['text' => '📚 FAQ', 'callback_data' => 'faq'],
                                ['text' => '🎥 Tutorials', 'callback_data' => 'tutorials'],
                            ],
                        ],
                    ],
                ]);
                break;
        }
    }
}
