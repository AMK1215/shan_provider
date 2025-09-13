<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PoneWineBet extends Model
{
    use HasFactory;

    protected $fillable = ['room_id', 'match_id', 'status', 'win_number'];
}
