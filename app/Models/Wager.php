<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Wager extends Model
{
    use HasFactory;

    protected $table = 'wagers';

    protected $fillable = [
        'id',
        'code',
        'member_account',
        'round_id',
        'currency',
        'provider_id',
        'provider_line_id',
        'provider_product_id',
        'provider_product_oid',
        'game_type',
        'game_code',
        'valid_bet_amount',
        'bet_amount',
        'prize_amount',
        'status',
        'payload',
        'settled_at',
        'created_at_api',
        'updated_at_api',
    ];

    // Custom attribute casting
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'payload' => 'array',
    ];
}
