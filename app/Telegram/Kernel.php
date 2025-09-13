<?php

namespace App\Telegram;

use App\Telegram\Handlers\AboutCommandHandler;
use App\Telegram\Handlers\ContactCommandHandler;
use App\Telegram\Handlers\HelpCommandHandler;
use App\Telegram\Handlers\MenuCallbackHandler;
use App\Telegram\Handlers\MenuCommandHandler;
use App\Telegram\Handlers\StartCommandHandler;
use WeStacks\TeleBot\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
    /**
     * Register command handlers
     */
    protected function commands(): array
    {
        return [
            'start' => StartCommandHandler::class,
            'help' => HelpCommandHandler::class,
            'about' => AboutCommandHandler::class,
            'contact' => ContactCommandHandler::class,
            'menu' => MenuCommandHandler::class,
        ];
    }

    /**
     * Register update handlers
     */
    protected function updates(): array
    {
        return [
            'callback_query' => MenuCallbackHandler::class,
        ];
    }
}
