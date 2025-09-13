<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoneWineBetInfo extends Model
{
    use HasFactory;

    protected $fillable = ['bet_no', 'bet_amount', 'pone_wine_player_bet_id'];

    public function poneWinePlayerBet()
    {
        return $this->belongsTo(PoneWinePlayerBet::class);
    }
}
