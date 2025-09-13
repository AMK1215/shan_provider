<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
class PushBet extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_account', 'player_id', 'player_agent_id', 'product_code', 'provider_name', 'game_type', 'operator_code', 'request_time',
        'sign', 'currency', 'transaction_id', 'action', 'amount', 'valid_bet_amount',
        'bet_amount', 'prize_amount', 'tip_amount', 'wager_code', 'wager_status',
        'round_id', 'payload', 'settle_at', 'game_code', 'game_name', 'channel_code', 'status',
        'before_balance', 'balance',
    ];

    protected $casts = [
        'payload' => 'array',
        'settle_at' => 'datetime',
        'request_time' => 'datetime',
    ];

    // PlaceBet.php
    public function user()
    {
        return $this->belongsTo(User::class, 'member_account', 'user_name');
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->created_at = Carbon::now('Asia/Yangon');
            $model->updated_at = Carbon::now('Asia/Yangon');
        });

        static::updating(function ($model) {
            $model->updated_at = Carbon::now('Asia/Yangon');
        });
    }
}
