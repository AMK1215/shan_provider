<?php

namespace App\Telegram\Handlers;

use WeStacks\TeleBot\Handlers\CommandHandler;

class ContactCommandHandler extends CommandHandler
{
    /**
     * Handle the /contact command
     */
    public function handle()
    {
        $this->sendContact([
            'phone_number' => '+1234567890',
            'first_name' => 'Support',
            'last_name' => 'Team',
            'vcard' => "BEGIN:VCARD\nVERSION:3.0\nFN:Support Team\nTEL:+1234567890\nEMAIL:support@example.com\nEND:VCARD",
        ]);

        $this->sendMessage([
            'text' => "📞 *Contact Information*\n\n".
                     "You can reach us through:\n\n".
                     "📱 Phone: +1234567890\n".
                     "📧 Email: support@example.com\n".
                     "🌐 Website: https://your-website.com\n".
                     '💬 Telegram: @your_support',
            'parse_mode' => 'Markdown',
        ]);
    }
}
