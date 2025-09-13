<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessedWagerCallback extends Model
{
    use HasFactory;
    protected $fillable = ['wager_code', 'game_type_id', 'players', 'banker_balance', 'timestamp', 'total_player_net', 'banker_amount_change'];
    protected $casts = [
        'players' => 'array', // 👈 This tells Laravel to automatically JSON encode/decode
    ];
}
