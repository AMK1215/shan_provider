<?php

namespace App\Models\DigitGame;

use App\Models\GameType;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DigitBet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'member_account',
        'bet_type',
        'digit',
        'bet_amount',
        'multiplier',
        'rolled_number',
        'win_amount',
        'profit',
        'status',
        'bet_time',
        'wager_code',
        'outcome',
        'game_type_id',
        'before_balance',
        'after_balance',

    ];

    protected $casts = [
        'bet_time' => 'datetime', // <-- Add this line
        'bet_amount' => 'decimal:2', // Good practice to cast decimals
        'win_amount' => 'decimal:2',
        'profit' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function gameType()
    {
        return $this->belongsTo(GameType::class);
    }
}
