<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\BroadcastMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class PlayerDepositNotification extends Notification implements ShouldQueue
{
    use Queueable;

    protected $deposit;

    public function __construct($deposit)
    {
        $this->deposit = $deposit;
    }

    public function via($notifiable)
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable)
    {
        Log::info('Storing notification in database:', [
            'player_name' => $this->deposit->user->user_name,
            'amount' => $this->deposit->amount,
            'refrence_no' => $this->deposit->refrence_no,
        ]);

        return [
            'player_name' => $this->deposit->user->user_name,
            'amount' => $this->deposit->amount,
            'refrence_no' => $this->deposit->refrence_no,
            'message' => "Player {$this->deposit->user->user_name} has deposited {$this->deposit->amount}.",
        ];
    }

    public function toBroadcast($notifiable)
    {
        return new BroadcastMessage([
            'player_name' => $this->deposit->user->user_name,
            'amount' => $this->deposit->amount,
            'refrence_no' => $this->deposit->refrence_no,
            'message' => "Player {$this->deposit->user->user_name} has deposited {$this->deposit->amount}.",
        ]);
    }
}
