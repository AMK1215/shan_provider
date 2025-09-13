<?php

namespace App\Telegram\Handlers;

use WeStacks\TeleBot\Handlers\CommandHandler;

class HelpCommandHandler extends CommandHandler
{
    /**
     * Handle the /help command
     */
    public function handle()
    {
        $this->sendMessage([
            'text' => "🤖 *Available Commands*\n\n".
                     "/start - Start the bot\n".
                     "/help - Show this help message\n".
                     "/about - About this bot\n".
                     "/contact - Contact information\n".
                     '/menu - Show main menu',
            'parse_mode' => 'Markdown',
        ]);
    }
}
