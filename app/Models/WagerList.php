<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WagerList extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_account',
        'round_id',
        'currency',
        'provider_id',
        'provider_line_id',
        'game_type',
        'game_code',
        'valid_bet_amount',
        'bet_amount',
        'prize_amount',
        'status',
        'settled_at',
    ];

    protected $casts = [
        'settled_at' => 'datetime',
    ];
}
